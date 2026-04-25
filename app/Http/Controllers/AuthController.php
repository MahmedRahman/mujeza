<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Disease;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
        return view('dashboard.home');
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

    public function products(): View
    {
        return view('dashboard.products', [
            'products' => Product::query()->latest()->get(),
        ]);
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

        $items = $products->map(function (Product $product) {
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
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
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
        return view('dashboard.settings');
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

        $diseases = $this->askDeepSeekForList(
            $validated['title'],
            $validated['description'],
            "Return ONLY a JSON array of short disease/condition names in Arabic that honey may help support."
        );

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

        $benefits = $this->askDeepSeekForList(
            $validated['title'],
            $validated['description'],
            "Return ONLY a JSON array of short benefit statements in Arabic for this honey product."
        );

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

        $usageMethods = $this->askDeepSeekForList(
            $validated['title'],
            $validated['description'],
            "Return ONLY a JSON array of short usage instructions in Arabic for this honey product."
        );

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

    private function askDeepSeekForList(string $title, string $description, string $taskInstruction): array
    {
        $apiKey = (string) env('DEEPSEEK_API_KEY');
        if ($apiKey === '') {
            return [];
        }

        $prompt = "You are helping an e-commerce admin prepare product data for honey products.\n"
            .$taskInstruction."\n"
            ."No markdown, no explanation, no extra text.\n"
            ."Provide 5 to 10 items.\n\n"
            ."Product title: ".$title."\n"
            ."Product description: ".$description;

        $response = Http::timeout(30)
            ->withToken($apiKey)
            ->post('https://api.deepseek.com/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'Return valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.4,
            ]);

        if (! $response->successful()) {
            return [];
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '[]');
        $content = trim(preg_replace('/^```(?:json)?|```$/m', '', $content) ?? $content);

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
