<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\Disease;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($credentials['username'] === 'admin' && $credentials['password'] === 'admin') {
            $user = \App\Models\User::query()->firstOrCreate(
                ['email' => 'admin@mujeza.local'],
                [
                    'name' => 'Admin',
                    'password' => bcrypt('admin'),
                ]
            );

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()
            ->withInput(['username' => $credentials['username']])
            ->withErrors([
                'username' => 'بيانات الدخول غير صحيحة.',
            ]);
    }

    public function dashboard(): View
    {
        return view('dashboard.home', [
            'stats' => [
                'products' => Product::query()->count(),
                'available_products' => Product::query()->where('is_available', true)->count(),
                'unavailable_products' => Product::query()->where('is_available', false)->count(),
                'orders' => Order::query()->count(),
                'orders_today' => Order::query()->whereDate('created_at', now()->toDateString())->count(),
                'orders_last7days' => Order::query()->where('created_at', '>=', now()->subDays(7))->count(),
                'branches' => Branch::query()->count(),
                'complaints' => Complaint::query()->count(),
                'complaints_today' => Complaint::query()->whereDate('created_at', now()->toDateString())->count(),
                'complaints_last7days' => Complaint::query()->where('created_at', '>=', now()->subDays(7))->count(),
                'categories' => Category::query()->count(),
                'diseases' => Disease::query()->count(),
            ],
        ]);
    }

    public function categories(): View
    {
        return view('dashboard.categories', [
            'categories' => Category::query()->latest()->get(),
        ]);
    }

    public function createCategory(): View
    {
        return view('dashboard.categories-create');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $imagePath = $request->file('image')?->store('categories', 'public');

        Category::query()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route('categories.create')
            ->with('success', 'تمت إضافة الفئة بنجاح.');
    }

    public function products(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $productsQuery = Product::query();
        if ($q !== '') {
            $productsQuery->where(function ($builder) use ($q) {
                $builder->where('title', 'like', '%'.$q.'%')
                    ->orWhere('description', 'like', '%'.$q.'%');
            });
        }

        $products = $productsQuery->latest()->get();

        $totalProductsCount = Product::query()->count();
        $availableProductsCount = Product::query()
            ->where('is_available', true)
            ->count();
        $unavailableProductsCount = Product::query()
            ->where('is_available', false)
            ->count();

        return view('dashboard.products', [
            'products' => $products,
            'q' => $q,
            'totalProductsCount' => $totalProductsCount,
            'availableProductsCount' => $availableProductsCount,
            'unavailableProductsCount' => $unavailableProductsCount,
            'filteredProductsCount' => $products->count(),
        ]);
    }

    public function branches(): View
    {
        return view('dashboard.branches', [
            'branches' => Branch::query()->latest()->get(),
        ]);
    }

    public function createBranch(): View
    {
        return view('dashboard.branches-create');
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone1' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'map_url' => ['nullable', 'string', 'max:2048'],
        ]);

        // If map_url provided as empty string, normalize to null.
        $validated['map_url'] = isset($validated['map_url']) && trim((string) $validated['map_url']) !== '' ? $validated['map_url'] : null;
        $validated['address'] = isset($validated['address']) && trim((string) $validated['address']) !== '' ? $validated['address'] : null;
        $validated['phone1'] = isset($validated['phone1']) && trim((string) $validated['phone1']) !== '' ? $validated['phone1'] : null;
        $validated['phone2'] = isset($validated['phone2']) && trim((string) $validated['phone2']) !== '' ? $validated['phone2'] : null;

        Branch::query()->create($validated);

        return redirect()
            ->route('branches.index')
            ->with('success', 'تم إضافة الفرع بنجاح.');
    }

    public function editBranch(Branch $branch): View
    {
        return view('dashboard.branches-edit', [
            'branch' => $branch,
        ]);
    }

    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone1' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'map_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $validated['map_url'] = isset($validated['map_url']) && trim((string) $validated['map_url']) !== '' ? $validated['map_url'] : null;
        $validated['address'] = isset($validated['address']) && trim((string) $validated['address']) !== '' ? $validated['address'] : null;
        $validated['phone1'] = isset($validated['phone1']) && trim((string) $validated['phone1']) !== '' ? $validated['phone1'] : null;
        $validated['phone2'] = isset($validated['phone2']) && trim((string) $validated['phone2']) !== '' ? $validated['phone2'] : null;

        $branch->update($validated);

        return redirect()
            ->route('branches.index')
            ->with('success', 'تم تعديل الفرع بنجاح.');
    }

    public function destroyBranch(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', 'تم حذف الفرع بنجاح.');
    }

    public function orders(): View
    {
        $orders = Order::query()
            ->latest()
            ->get();

        return view('dashboard.orders', [
            'orders' => $orders,
        ]);
    }

    public function createOrder(): View
    {
        $products = Product::query()->latest()->get();

        $statuses = [
            'قيد المعالجة',
            'تم الاستلام',
            'قيد التجهيز',
            'تم الشحن',
            'تم التسليم',
            'ملغي',
        ];

        return view('dashboard.orders-create', [
            'products' => $products,
            'statuses' => $statuses,
        ]);
    }

    public function editOrder(Order $order): View
    {
        $order->load('items');

        $products = Product::query()->latest()->get();
        $statuses = [
            'قيد المعالجة',
            'تم الاستلام',
            'قيد التجهيز',
            'تم الشحن',
            'تم التسليم',
            'ملغي',
        ];

        return view('dashboard.orders-edit', [
            'order' => $order,
            'products' => $products,
            'statuses' => $statuses,
        ]);
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:100'],

            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id'],

            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $productIds = array_values($validated['product_ids']);
        $quantities = array_values($validated['quantities']);

        if (count($productIds) !== count($quantities)) {
            return back()->withErrors(['quantities' => 'بيانات المنتجات والكمية غير متطابقة.'])->withInput();
        }

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $total = 0.0;
        $lines = [];
        foreach ($productIds as $idx => $pid) {
            $qty = (int) $quantities[$idx];
            $product = $products->get((int) $pid);
            if (! $product) {
                continue;
            }

            $unitPrice = (float) $product->price;
            $lineTotal = round($unitPrice * $qty, 2);
            $total += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'product_title' => $product->title,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];
        }

        $orderNumber = (int) (Order::query()->max('order_number') ?? 0) + 1;

        $order = Order::query()->create([
            'order_number' => $orderNumber,
            'customer_name' => $validated['customer_name'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'total_amount' => round($total, 2),
        ]);

        foreach ($lines as $line) {
            $order->items()->create([
                'order_id' => $order->id,
                'product_id' => $line['product_id'],
                'product_title' => $line['product_title'],
                'unit_price' => $line['unit_price'],
                'quantity' => $line['quantity'],
                'line_total' => $line['line_total'],
            ]);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'تم إنشاء الطلب بنجاح.');
    }

    public function updateOrder(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:100'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'exists:products,id'],
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $productIds = array_values($validated['product_ids']);
        $quantities = array_values($validated['quantities']);

        if (count($productIds) !== count($quantities)) {
            return back()->withErrors(['quantities' => 'بيانات المنتجات والكمية غير متطابقة.'])->withInput();
        }

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $total = 0.0;
        $lines = [];
        foreach ($productIds as $idx => $pid) {
            $qty = (int) $quantities[$idx];
            $product = $products->get((int) $pid);
            if (! $product) {
                continue;
            }

            $unitPrice = (float) $product->price;
            $lineTotal = round($unitPrice * $qty, 2);
            $total += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'product_title' => $product->title,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];
        }

        $order->update([
            'customer_name' => $validated['customer_name'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'total_amount' => round($total, 2),
        ]);

        OrderItem::query()->where('order_id', $order->id)->delete();
        foreach ($lines as $line) {
            $order->items()->create([
                'order_id' => $order->id,
                'product_id' => $line['product_id'],
                'product_title' => $line['product_title'],
                'unit_price' => $line['unit_price'],
                'quantity' => $line['quantity'],
                'line_total' => $line['line_total'],
            ]);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'تم تعديل الطلب بنجاح.');
    }

    public function destroyOrder(Order $order): RedirectResponse
    {
        OrderItem::query()->where('order_id', $order->id)->delete();
        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('success', 'تم حذف الطلب بنجاح.');
    }

    public function showOrder(Order $order): View
    {
        $order->load('items');

        return view('dashboard.orders-invoice', [
            'order' => $order,
        ]);
    }

    public function invoiceOrder(Order $order): View
    {
        return $this->showOrder($order);
    }

    public function complaints(): View
    {
        $complaints = Complaint::query()->latest()->get();

        return view('dashboard.complaints', [
            'complaints' => $complaints,
            'complaintsStats' => [
                'total' => Complaint::query()->count(),
                'today' => Complaint::query()->whereDate('created_at', now()->toDateString())->count(),
                'last7days' => Complaint::query()->where('created_at', '>=', now()->subDays(7))->count(),
            ],
        ]);
    }

    public function createComplaint(): View
    {
        return view('dashboard.complaints-create');
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        Complaint::query()->create($validated);

        return redirect()
            ->route('complaints.index')
            ->with('success', 'تم حفظ الشكوى/الاستفسار بنجاح.');
    }

    public function editComplaint(Complaint $complaint): View
    {
        return view('dashboard.complaints-edit', [
            'complaint' => $complaint,
        ]);
    }

    public function updateComplaint(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $complaint->update($validated);

        return redirect()
            ->route('complaints.index')
            ->with('success', 'تم تعديل الشكوى/الاستفسار بنجاح.');
    }

    public function destroyComplaint(Complaint $complaint): RedirectResponse
    {
        $complaint->delete();

        return redirect()
            ->route('complaints.index')
            ->with('success', 'تم حذف الشكوى/الاستفسار بنجاح.');
    }

    #[OA\Get(
        path: '/api/products',
        operationId: 'getProducts',
        tags: ['Products'],
        summary: 'Get products list',
        description: 'Returns all products in one response.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Products fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function apiProducts(Request $request): JsonResponse
    {
        $products = Product::query()->latest()->get();

        $items = $products->map(fn (Product $product) => $this->transformProductForApi($product))->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    #[OA\Get(
        path: '/api/products/search',
        operationId: 'searchProductsByName',
        tags: ['Products'],
        summary: 'Search products by name',
        description: 'Searches products using product title or description (partial match).',
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: 'Product name to search for (optional if search is provided)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'سدر')
            ),
            new OA\Parameter(
                name: 'search',
                description: 'Alias for name query parameter',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'سدر')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Products search completed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'query', type: 'string', example: 'سدر'),
                        new OA\Property(property: 'count', type: 'integer', example: 2),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function apiSearchProducts(Request $request): JsonResponse
    {
        $query = trim((string) ($request->query('name') ?? $request->query('search') ?? ''));

        $productsQuery = Product::query();

        if ($query !== '') {
            $productsQuery->where(function ($builder) use ($query) {
                $builder->where('title', 'like', '%'.$query.'%')
                    ->orWhere('description', 'like', '%'.$query.'%');
            });
        }

        $products = $productsQuery->latest()->get();

        $items = $products->map(fn (Product $product) => $this->transformProductForApi($product))->values();

        return response()->json([
            'success' => true,
            'query' => $query,
            'count' => $items->count(),
            'data' => $items,
        ]);
    }

    #[OA\Get(
        path: '/api/branches',
        operationId: 'getBranches',
        tags: ['Branches'],
        summary: 'Get branches list',
        description: 'Returns all branches in one response.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Branches fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function apiBranches(Request $request): JsonResponse
    {
        $branches = Branch::query()->latest()->get();
        $items = $branches->map(fn (Branch $branch) => $this->transformBranchForApi($branch))->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    #[OA\Get(
        path: '/api/complaints',
        operationId: 'getComplaints',
        tags: ['Complaints'],
        summary: 'Get complaints and inquiries list',
        description: 'Returns complaints list with optional text search in title, description, or phone.',
        parameters: [
            new OA\Parameter(
                name: 'q',
                description: 'Search query (optional)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'تأخير الطلب')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaints fetched successfully'
            ),
        ]
    )]
    public function apiComplaints(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        $complaintsQuery = Complaint::query();
        if ($query !== '') {
            $complaintsQuery->where(function ($builder) use ($query) {
                $builder->where('title', 'like', '%'.$query.'%')
                    ->orWhere('description', 'like', '%'.$query.'%')
                    ->orWhere('phone', 'like', '%'.$query.'%');
            });
        }

        $complaints = $complaintsQuery->latest()->get();
        $items = $complaints->map(fn (Complaint $complaint) => $this->transformComplaintForApi($complaint))->values();

        return response()->json([
            'success' => true,
            'query' => $query,
            'count' => $items->count(),
            'data' => $items,
        ]);
    }

    #[OA\Post(
        path: '/api/complaints',
        operationId: 'storeComplaint',
        tags: ['Complaints'],
        summary: 'Create complaint or inquiry',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description', 'phone'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'استفسار عن المنتج'),
                    new OA\Property(property: 'description', type: 'string', example: 'أريد معرفة طريقة الاستخدام المناسبة.'),
                    new OA\Property(property: 'phone', type: 'string', example: '96550000000'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Complaint created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function apiStoreComplaint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $complaint = Complaint::query()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الشكوى/الاستفسار بنجاح.',
            'data' => $this->transformComplaintForApi($complaint),
        ], 201);
    }

    #[OA\Get(
        path: '/api/complaints/{id}',
        operationId: 'showComplaint',
        tags: ['Complaints'],
        summary: 'Get a single complaint/inquiry',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Complaint fetched successfully'),
        ]
    )]
    public function apiShowComplaint(Complaint $complaint): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->transformComplaintForApi($complaint),
        ]);
    }

    #[OA\Put(
        path: '/api/complaints/{id}',
        operationId: 'updateComplaint',
        tags: ['Complaints'],
        summary: 'Update complaint or inquiry',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description', 'phone'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'شكوى عن تأخر الرد'),
                    new OA\Property(property: 'description', type: 'string', example: 'لم يصلني رد حتى الآن.'),
                    new OA\Property(property: 'phone', type: 'string', example: '96550000000'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Complaint updated successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function apiUpdateComplaint(Request $request, Complaint $complaint): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $complaint->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الشكوى/الاستفسار بنجاح.',
            'data' => $this->transformComplaintForApi($complaint->fresh()),
        ]);
    }

    #[OA\Delete(
        path: '/api/complaints/{id}',
        operationId: 'deleteComplaint',
        tags: ['Complaints'],
        summary: 'Delete complaint or inquiry',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Complaint deleted successfully'),
        ]
    )]
    public function apiDestroyComplaint(Complaint $complaint): JsonResponse
    {
        $complaint->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الشكوى/الاستفسار بنجاح.',
        ]);
    }

    #[OA\Post(
        path: '/api/orders',
        operationId: 'storeOrder',
        tags: ['Orders'],
        summary: 'Create order',
        description: 'Creates a new order with one or more items. Prices are calculated automatically from products table.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['customer_name', 'phone', 'items'],
                properties: [
                    new OA\Property(property: 'customer_name', type: 'string', example: 'محمد أحمد'),
                    new OA\Property(property: 'phone', type: 'string', example: '96550000000'),
                    new OA\Property(property: 'status', type: 'string', example: 'قيد المعالجة'),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'product_id', type: 'integer', example: 1),
                                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                            ],
                            type: 'object'
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Order created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function apiStoreOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $itemRows = array_values($validated['items']);
        $productIds = collect($itemRows)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $total = 0.0;
        $lines = [];
        foreach ($itemRows as $row) {
            $productId = (int) $row['product_id'];
            $quantity = (int) $row['quantity'];
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $unitPrice = (float) $product->price;
            $lineTotal = round($unitPrice * $quantity, 2);
            $total += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'product_title' => $product->title,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        if ($lines === []) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إنشاء طلب بدون منتجات صالحة.',
            ], 422);
        }

        $order = DB::transaction(function () use ($validated, $lines, $total) {
            $orderNumber = (int) (Order::query()->max('order_number') ?? 0) + 1;

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'customer_name' => $validated['customer_name'],
                'phone' => $validated['phone'],
                'status' => $validated['status'] ?? 'قيد المعالجة',
                'total_amount' => round($total, 2),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'order_id' => $order->id,
                    'product_id' => $line['product_id'],
                    'product_title' => $line['product_title'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);
            }

            return $order->fresh('items');
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الطلب بنجاح.',
            'data' => $this->transformOrderForApi($order),
        ], 201);
    }

    #[OA\Get(
        path: '/api/orders/status',
        operationId: 'getOrderStatusByPhone',
        tags: ['Orders'],
        summary: 'Get order status by phone number',
        description: 'Returns latest order status and all orders for the provided phone number.',
        parameters: [
            new OA\Parameter(
                name: 'phone',
                description: 'Customer phone number',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: '96550000000')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Order status fetched successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function apiOrderStatusByPhone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $phone = trim((string) $validated['phone']);

        $orders = Order::query()
            ->with('items')
            ->where('phone', $phone)
            ->latest()
            ->get();

        $items = $orders->map(fn (Order $order) => $this->transformOrderForApi($order))->values();
        $latest = $items->first();

        return response()->json([
            'success' => true,
            'phone' => $phone,
            'found' => $latest !== null,
            'latest_status' => $latest['status'] ?? null,
            'count' => $items->count(),
            'latest_order' => $latest,
            'orders' => $items,
        ]);
    }

    public function createProduct(): View
    {
        return view('dashboard.products-create');
    }

    public function editProduct(Product $product): View
    {
        return view('dashboard.products-edit', [
            'product' => $product,
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'is_available' => ['sometimes', 'boolean'],
            'description' => ['required', 'string'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['nullable', 'string', 'max:255'],
            'diseases' => ['nullable', 'array'],
            'diseases.*' => ['nullable', 'string', 'max:255'],
            'usage_methods' => ['nullable', 'array'],
            'usage_methods.*' => ['nullable', 'string', 'max:255'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'string', 'max:100'],
            'promo_videos' => ['nullable', 'array'],
            'promo_videos.*' => ['nullable', 'string', 'max:2048'],
            'product_images' => ['nullable', 'array'],
            'product_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $productImages = [];
        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $imageFile) {
                if ($imageFile !== null) {
                    $productImages[] = $imageFile->store('products/gallery', 'public');
                }
            }
        }

        $primaryGalleryImage = null;
        if ($productImages !== []) {
            $selectedIndex = (int) ($validated['primary_image_index'] ?? 0);
            $selectedIndex = max(0, min($selectedIndex, count($productImages) - 1));
            $primaryGalleryImage = $productImages[$selectedIndex];
        }

        Product::query()->create([
            'title' => $validated['title'],
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'is_available' => $request->boolean('is_available'),
            'description' => $validated['description'],
            'benefits' => $this->parseArray($validated['benefits'] ?? []),
            'diseases' => $this->parseArray($validated['diseases'] ?? []),
            'usage_methods' => $this->parseArray($validated['usage_methods'] ?? []),
            'sizes' => $this->parseArray($validated['sizes'] ?? []),
            'promo_videos' => $this->parseArray($validated['promo_videos'] ?? []),
            'cover_image' => $primaryGalleryImage,
            'gallery_images' => $productImages,
            'primary_gallery_image' => $primaryGalleryImage,
        ]);

        return redirect()
            ->route('products.create')
            ->with('success', 'تمت إضافة المنتج بنجاح.');
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'is_available' => ['sometimes', 'boolean'],
            'description' => ['required', 'string'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['nullable', 'string', 'max:255'],
            'diseases' => ['nullable', 'array'],
            'diseases.*' => ['nullable', 'string', 'max:255'],
            'usage_methods' => ['nullable', 'array'],
            'usage_methods.*' => ['nullable', 'string', 'max:255'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'string', 'max:100'],
            'promo_videos' => ['nullable', 'array'],
            'promo_videos.*' => ['nullable', 'string', 'max:2048'],
            'product_images' => ['nullable', 'array'],
            'product_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $galleryImages = $product->gallery_images ?? [];
        $primaryGalleryImage = $product->primary_gallery_image;

        if ($request->hasFile('product_images')) {
            $newImages = [];
            foreach ($request->file('product_images') as $imageFile) {
                if ($imageFile !== null) {
                    $newImages[] = $imageFile->store('products/gallery', 'public');
                }
            }

            if ($newImages !== []) {
                $pathsToDelete = collect($product->gallery_images ?? [])
                    ->push($product->cover_image)
                    ->push($product->primary_gallery_image)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                foreach ($pathsToDelete as $path) {
                    Storage::disk('public')->delete($path);
                }

                $selectedIndex = (int) ($validated['primary_image_index'] ?? 0);
                $selectedIndex = max(0, min($selectedIndex, count($newImages) - 1));

                $galleryImages = $newImages;
                $primaryGalleryImage = $newImages[$selectedIndex];
            }
        }

        $product->update([
            'title' => $validated['title'],
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'is_available' => $request->boolean('is_available'),
            'description' => $validated['description'],
            'benefits' => $this->parseArray($validated['benefits'] ?? []),
            'diseases' => $this->parseArray($validated['diseases'] ?? []),
            'usage_methods' => $this->parseArray($validated['usage_methods'] ?? []),
            'sizes' => $this->parseArray($validated['sizes'] ?? []),
            'promo_videos' => $this->parseArray($validated['promo_videos'] ?? []),
            'cover_image' => $primaryGalleryImage,
            'gallery_images' => $galleryImages,
            'primary_gallery_image' => $primaryGalleryImage,
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'تم تعديل المنتج بنجاح.');
    }

    public function settings(): View
    {
        $defaults = [
            'store_name' => 'Mujeza',
            'email' => 'admin@mujeza.local',
            'phone1' => '',
            'phone2' => '',
            'whatsapp' => '',
            'address' => '',
            'facebook' => '',
            'instagram' => '',
            'website' => '',
            'company_about' => '',
            'chatwoot_url' => '',
            'chatwoot_token' => '',
            'evo_url' => '',
            'evo_api_key' => '',
            'evo_instance' => '',
        ];

        $stored = Setting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->toArray();

        return view('dashboard.settings', [
            'settings' => array_merge($defaults, $stored),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone1' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'facebook' => ['nullable', 'url', 'max:2048'],
            'instagram' => ['nullable', 'url', 'max:2048'],
            'website' => ['nullable', 'url', 'max:2048'],
            'company_about' => ['nullable', 'string', 'max:5000'],
            'chatwoot_url' => ['nullable', 'url', 'max:2048'],
            'chatwoot_token' => ['nullable', 'string', 'max:500'],
            'evo_url' => ['nullable', 'url', 'max:2048'],
            'evo_api_key' => ['nullable', 'string', 'max:500'],
            'evo_instance' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value !== null && trim((string) $value) !== '' ? $value : null]
            );
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'تم حفظ بيانات التواصل ونبذة الشركة بنجاح.');
    }

    public function conversations(): View
    {
        $keys = ['chatwoot_url', 'chatwoot_token'];

        $stored = Setting::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        $chatwootUrl = $stored['chatwoot_url'] ?? null;
        $chatwootToken = $stored['chatwoot_token'] ?? null;

        return view('dashboard.conversations', compact('chatwootUrl', 'chatwootToken'));
    }

    public function diseases(): View
    {
        return view('dashboard.diseases', [
            'diseases' => Disease::query()->latest()->get(),
        ]);
    }

    public function createDisease(): View
    {
        return view('dashboard.diseases-create');
    }

    public function storeDisease(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $imagePath = $request->file('image')?->store('diseases', 'public');

        Disease::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route('diseases.create')
            ->with('success', 'تمت إضافة المرض بنجاح.');
    }

    public function suggestDiseases(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:3000'],
            ],
            [
                'title.required' => 'يرجى كتابة عنوان المنتج أولاً.',
                'description.required' => 'يرجى كتابة وصف المنتج أولاً.',
            ]
        );

        try {
            $diseases = $this->askDeepSeekForList(
                $validated['title'],
                $validated['description'],
                "Return ONLY a JSON array of short disease/condition names in Arabic that honey may help support."
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json(['diseases' => $diseases]);
    }

    public function suggestBenefits(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:3000'],
            ],
            [
                'title.required' => 'يرجى كتابة عنوان المنتج أولاً.',
                'description.required' => 'يرجى كتابة وصف المنتج أولاً.',
            ]
        );

        try {
            $benefits = $this->askDeepSeekForList(
                $validated['title'],
                $validated['description'],
                "Return ONLY a JSON array of short benefit statements in Arabic for this honey product."
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json(['benefits' => $benefits]);
    }

    public function suggestUsageMethods(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:3000'],
            ],
            [
                'title.required' => 'يرجى كتابة عنوان المنتج أولاً.',
                'description.required' => 'يرجى كتابة وصف المنتج أولاً.',
            ]
        );

        try {
            $usageMethods = $this->askDeepSeekForList(
                $validated['title'],
                $validated['description'],
                "Return ONLY a JSON array of short usage instructions in Arabic for this honey product."
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json(['usage_methods' => $usageMethods]);
    }

    public function destroyProduct(Product $product): RedirectResponse
    {
        $pathsToDelete = collect($product->gallery_images ?? [])
            ->push($product->cover_image)
            ->push($product->primary_gallery_image)
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($pathsToDelete as $path) {
            Storage::disk('public')->delete($path);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'تم حذف المنتج بنجاح.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function parseArray(array $values): array
    {
        return collect($values)
            ->flatMap(function ($item) {
                return preg_split('/\r\n|\r|\n/', (string) $item) ?: [];
            })
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function transformProductForApi(Product $product): array
    {
        $coverImageUrl = $product->cover_image ? asset('storage/'.$product->cover_image) : null;
        $galleryImageUrls = collect($product->gallery_images ?? [])
            ->filter()
            ->map(fn (string $path) => asset('storage/'.$path))
            ->values()
            ->all();

        $promoVideos = collect($product->promo_videos ?? [])
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $product->id,
            'title' => $product->title,
            'price' => $product->price,
            'discount_price' => $product->discount_price,
            'description' => $product->description,
            'benefits' => $product->benefits ?? [],
            'diseases' => $product->diseases ?? [],
            'usage_methods' => $product->usage_methods ?? [],
            'sizes' => $product->sizes ?? [],
            'cover_image' => $product->cover_image,
            'cover_image_url' => $coverImageUrl,
            'gallery_images' => $product->gallery_images ?? [],
            'gallery_image_urls' => $galleryImageUrls,
            'primary_gallery_image' => $product->primary_gallery_image,
            'primary_gallery_image_url' => $product->primary_gallery_image ? asset('storage/'.$product->primary_gallery_image) : null,
            'promo_videos' => $promoVideos,
            'created_at' => optional($product->created_at)?->toISOString(),
            'updated_at' => optional($product->updated_at)?->toISOString(),
        ];
    }

    private function transformComplaintForApi(Complaint $complaint): array
    {
        return [
            'id' => $complaint->id,
            'title' => $complaint->title,
            'description' => $complaint->description,
            'phone' => $complaint->phone,
            'created_at' => optional($complaint->created_at)?->toISOString(),
            'updated_at' => optional($complaint->updated_at)?->toISOString(),
        ];
    }

    private function transformBranchForApi(Branch $branch): array
    {
        return [
            'id' => $branch->id,
            'name' => $branch->name,
            'phone1' => $branch->phone1,
            'phone2' => $branch->phone2,
            'address' => $branch->address,
            'latitude' => $branch->latitude,
            'longitude' => $branch->longitude,
            'map_url' => $branch->map_url,
            'created_at' => optional($branch->created_at)?->toISOString(),
            'updated_at' => optional($branch->updated_at)?->toISOString(),
        ];
    }

    private function transformOrderForApi(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'phone' => $order->phone,
            'status' => $order->status,
            'total_amount' => (float) $order->total_amount,
            'items' => $order->items->map(fn (OrderItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_title' => $item->product_title,
                'unit_price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'line_total' => (float) $item->line_total,
            ])->values()->all(),
            'created_at' => optional($order->created_at)?->toISOString(),
            'updated_at' => optional($order->updated_at)?->toISOString(),
        ];
    }

    private function askDeepSeekForList(string $title, string $description, string $taskInstruction): array
    {
        $apiKey = (string) config('services.deepseek.api_key', '');
        $baseUrl = rtrim((string) config('services.deepseek.base_url', 'https://api.deepseek.com'), '/');

        if ($apiKey === '') {
            throw new \RuntimeException('مفتاح DeepSeek غير مُعد على السيرفر.');
        }

        $prompt = "You are helping an e-commerce admin prepare product data for honey products.\n"
            .$taskInstruction."\n"
            ."No markdown, no explanation, no extra text.\n"
            ."Provide 5 to 10 items.\n\n"
            ."Product title: ".$title."\n"
            ."Product description: ".$description;

        try {
            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->post($baseUrl.'/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'Return valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.4,
            ]);
        } catch (\Throwable $exception) {
            Log::error('DeepSeek request failed', [
                'message' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('تعذر الاتصال بخدمة الذكاء الاصطناعي حالياً.');
        }

        if (! $response->successful()) {
            Log::warning('DeepSeek non-success response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('خدمة الذكاء الاصطناعي رفضت الطلب. تأكد من المفتاح وصلاحياته.');
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '[]');
        $content = trim(preg_replace('/^```(?:json)?|```$/m', '', $content) ?? $content);

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            Log::warning('DeepSeek invalid JSON response', [
                'content' => $content,
            ]);

            throw new \RuntimeException('تم استلام رد غير صالح من خدمة الذكاء الاصطناعي.');
        }

        return collect($decoded)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
