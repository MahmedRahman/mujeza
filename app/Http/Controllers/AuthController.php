<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Disease;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Services\DashboardAlertsService;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

    public function dashboard(DashboardAlertsService $dashboardAlerts): View
    {
        $alerts = $dashboardAlerts->build();

        return view('dashboard.home', [
            'alerts' => $alerts,
            'hasAlerts' => $dashboardAlerts->hasAlerts($alerts),
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

    public function customers(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $customersQuery = Customer::query();
        if ($q !== '') {
            $customersQuery->where(function ($builder) use ($q) {
                $builder->where('name', 'like', '%'.$q.'%')
                    ->orWhere('phone', 'like', '%'.$q.'%')
                    ->orWhere('remote_jid', 'like', '%'.$q.'%')
                    ->orWhere('address', 'like', '%'.$q.'%');
            });
        }

        $customers = $customersQuery->withCount(['orders', 'complaints'])->latest()->paginate(20)->withQueryString();

        $autoReplyRaw = (string) (Setting::query()->where('key', 'whatsapp_auto_reply_global_enabled')->value('value') ?? '1');
        $autoReplyEnabled = in_array(strtolower($autoReplyRaw), ['1', 'true', 'on', 'yes'], true);

        $overridesRaw = Setting::query()->where('key', 'whatsapp_auto_reply_chat_overrides')->value('value');
        $chatOverrides = [];
        if (is_string($overridesRaw) && trim($overridesRaw) !== '') {
            $decoded = json_decode($overridesRaw, true);
            if (is_array($decoded)) {
                $chatOverrides = $decoded;
            }
        }

        return view('dashboard.customers', [
            'customers'        => $customers,
            'q'                => $q,
            'totalCount'       => Customer::query()->count(),
            'autoReplyEnabled' => $autoReplyEnabled,
            'chatOverrides'    => $chatOverrides,
        ]);
    }

    public function createCustomer(): View
    {
        $autoReplyRaw     = (string) (Setting::query()->where('key', 'whatsapp_auto_reply_global_enabled')->value('value') ?? '1');
        $autoReplyEnabled = in_array(strtolower($autoReplyRaw), ['1', 'true', 'on', 'yes'], true);

        return view('dashboard.customers-create', [
            'globalAutoReply' => $autoReplyEnabled,
        ]);
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'remote_jid' => ['required', 'string', 'max:255', 'unique:customers,remote_jid'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'name'       => ['required', 'string', 'max:255'],
            'address'    => ['nullable', 'string', 'max:1000'],
            'auto_reply' => ['nullable', 'in:0,1'],
        ]);

        Customer::query()->create([
            'remote_jid' => $validated['remote_jid'],
            'phone'      => $validated['phone'] ?? null,
            'name'       => $validated['name'],
            'address'    => $validated['address'] ?? null,
        ]);

        // حفظ override للرد الآلي لو اختلف عن الإعداد العام
        if (isset($validated['auto_reply'])) {
            $newVal    = (bool) $validated['auto_reply'];
            $globalRaw = (string) (Setting::query()->where('key', 'whatsapp_auto_reply_global_enabled')->value('value') ?? '1');
            $global    = in_array(strtolower($globalRaw), ['1', 'true', 'on', 'yes'], true);

            if ($newVal !== $global) {
                $chatId       = $validated['remote_jid'];
                $overridesRaw = Setting::query()->where('key', 'whatsapp_auto_reply_chat_overrides')->value('value');
                $overrides    = [];
                if (is_string($overridesRaw) && trim($overridesRaw) !== '') {
                    $decoded = json_decode($overridesRaw, true);
                    if (is_array($decoded)) {
                        $overrides = $decoded;
                    }
                }
                $overrides[$chatId] = $newVal;
                Setting::query()->updateOrCreate(
                    ['key' => 'whatsapp_auto_reply_chat_overrides'],
                    ['value' => json_encode($overrides, JSON_UNESCAPED_UNICODE)]
                );
            }
        }

        return redirect()->route('customers.index')->with('success', 'تمت إضافة المستخدم بنجاح.');
    }

    public function editCustomer(Customer $customer): View
    {
        $autoReplyRaw  = (string) (Setting::query()->where('key', 'whatsapp_auto_reply_global_enabled')->value('value') ?? '1');
        $globalEnabled = in_array(strtolower($autoReplyRaw), ['1', 'true', 'on', 'yes'], true);

        $overridesRaw  = Setting::query()->where('key', 'whatsapp_auto_reply_chat_overrides')->value('value');
        $chatOverrides = [];
        if (is_string($overridesRaw) && trim($overridesRaw) !== '') {
            $decoded = json_decode($overridesRaw, true);
            if (is_array($decoded)) {
                $chatOverrides = $decoded;
            }
        }

        // remote_jid IS the chat ID
        $chatId      = $customer->remote_jid;
        $hasOverride = array_key_exists($chatId, $chatOverrides);
        $autoReply   = $hasOverride ? (bool) $chatOverrides[$chatId] : $globalEnabled;

        return view('dashboard.customers-edit', [
            'customer'      => $customer,
            'chatId'        => $chatId,
            'autoReply'     => $autoReply,
            'hasOverride'   => $hasOverride,
            'globalEnabled' => $globalEnabled,
        ]);
    }

    public function updateCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'phone'   => ['nullable', 'string', 'max:50'],
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    public function destroyCustomer(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }

    public function campaigns(): View
    {
        $campaigns = Campaign::query()->latest()->paginate(20);

        return view('dashboard.campaigns', [
            'campaigns' => $campaigns,
        ]);
    }

    public function createCampaign(): View
    {
        $customers = Customer::query()->orderBy('created_at', 'desc')->get();
        $phoneLimit = (int) (Setting::query()->where('key', 'campaign_phone_limit')->value('value') ?? 100);

        return view('dashboard.campaigns-create', [
            'customers'  => $customers,
            'phoneLimit' => $phoneLimit,
        ]);
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $phoneLimit = (int) (Setting::query()->where('key', 'campaign_phone_limit')->value('value') ?? 100);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phones'   => ['required', 'array', 'min:1', 'max:'.$phoneLimit],
            'phones.*' => ['required', 'string'],
            'message'  => ['required', 'string', 'max:4000'],
        ], [
            'phones.max' => 'لا يمكن إضافة أكثر من '.$phoneLimit.' رقم في الحملة الواحدة. يمكن تغيير الحد من الإعدادات.',
        ]);

        $phones = collect($validated['phones'])->map(function (string $phone) {
            $customer = Customer::query()->find($phone);

            return [
                'phone' => $phone,
                'name'  => $customer?->name ?? $phone,
            ];
        })->values()->all();

        $campaign = Campaign::query()->create([
            'name'          => $validated['name'],
            'message'       => $validated['message'],
            'phones_count'  => count($phones),
            'success_count' => 0,
            'failed_count'  => 0,
            'status'        => 'pending',
            'results'       => $phones,
        ]);

        return redirect()->route('campaigns.show', $campaign)->with('success', 'تم حفظ الحملة. يمكنك تشغيلها متى تريد.');
    }

    public function showCampaign(Campaign $campaign): View
    {
        return view('dashboard.campaigns-show', [
            'campaign' => $campaign,
        ]);
    }

    public function dispatchCampaign(Campaign $campaign): RedirectResponse
    {
        if ($campaign->status === 'cancelled') {
            return back()->with('error', 'لا يمكن تشغيل حملة ملغاة.');
        }

        $cfg = $this->evoConfig();
        $results = [];

        foreach (($campaign->results ?? []) as $entry) {
            $results[] = $this->evoSendToPhone(
                cfg: $cfg,
                phone: $entry['phone'],
                name: $entry['name'],
                text: $campaign->message,
            );
        }

        $campaign->update([
            'status'        => 'sent',
            'success_count' => collect($results)->where('status', 'success')->count(),
            'failed_count'  => collect($results)->where('status', 'error')->count(),
            'results'       => $results,
        ]);

        return redirect()->route('campaigns.show', $campaign)->with('success', 'تم إرسال الحملة بنجاح.');
    }

    public function cancelCampaign(Campaign $campaign): RedirectResponse
    {
        if ($campaign->status !== 'pending') {
            return back()->with('error', 'لا يمكن إلغاء إلا الحملات التي لم تُرسَل بعد.');
        }

        $campaign->update(['status' => 'cancelled']);

        return redirect()->route('campaigns.show', $campaign)->with('success', 'تم إلغاء الحملة.');
    }

    public function resendCampaign(Campaign $campaign): RedirectResponse
    {
        $cfg = $this->evoConfig();
        $results = [];

        $previous = collect($campaign->results ?? []);

        foreach ($previous as $entry) {
            $results[] = $this->evoSendToPhone(
                cfg: $cfg,
                phone: $entry['phone'],
                name: $entry['name'],
                text: $campaign->message,
            );
        }

        $campaign->update([
            'status'        => 'sent',
            'success_count' => collect($results)->where('status', 'success')->count(),
            'failed_count'  => collect($results)->where('status', 'error')->count(),
            'results'       => $results,
        ]);

        return redirect()->route('campaigns.show', $campaign)->with('success', 'تمت إعادة إرسال الحملة.');
    }

    public function destroyCampaign(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'تم حذف الحملة بنجاح.');
    }

    private function evoConfig(): array
    {
        $stored = Setting::query()
            ->whereIn('key', ['evo_url', 'evo_api_key', 'evo_instance'])
            ->pluck('value', 'key')
            ->toArray();

        return [
            'url'      => rtrim($stored['evo_url'] ?? '', '/'),
            'key'      => $stored['evo_api_key'] ?? '',
            'instance' => $stored['evo_instance'] ?? '',
        ];
    }

    private function evoSendToPhone(array $cfg, string $phone, string $name, string $text): array
    {
        $base = [
            'phone' => $phone,
            'name'  => $name,
        ];

        if (! $cfg['url'] || ! $cfg['instance']) {
            return array_merge($base, [
                'status'  => 'error',
                'message' => 'Evolution API غير مُهيّأة في الإعدادات.',
            ]);
        }

        try {
            $response = Http::withHeaders([
                'apikey'       => $cfg['key'],
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("{$cfg['url']}/message/sendText/{$cfg['instance']}", [
                'number' => $phone,
                'text'   => $text,
            ]);

            if ($response->successful()) {
                return array_merge($base, ['status' => 'success', 'message' => 'تم الإرسال بنجاح']);
            }

            Log::warning('Campaign send failed', ['phone' => $phone, 'status' => $response->status()]);

            return array_merge($base, [
                'status'  => 'error',
                'message' => 'فشل الإرسال — كود '.$response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Campaign send exception', ['phone' => $phone, 'error' => $e->getMessage()]);

            return array_merge($base, [
                'status'  => 'error',
                'message' => 'خطأ في الاتصال: '.$e->getMessage(),
            ]);
        }
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

        $filteredProductsCount = (clone $productsQuery)->count();
        $products = $productsQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

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
            'filteredProductsCount' => $filteredProductsCount,
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
            'working_hours' => ['nullable', 'string', 'max:5000'],
        ]);

        // If map_url provided as empty string, normalize to null.
        $validated['map_url'] = isset($validated['map_url']) && trim((string) $validated['map_url']) !== '' ? $validated['map_url'] : null;
        $validated['address'] = isset($validated['address']) && trim((string) $validated['address']) !== '' ? $validated['address'] : null;
        $validated['phone1'] = isset($validated['phone1']) && trim((string) $validated['phone1']) !== '' ? $validated['phone1'] : null;
        $validated['phone2'] = isset($validated['phone2']) && trim((string) $validated['phone2']) !== '' ? $validated['phone2'] : null;
        $validated['working_hours'] = isset($validated['working_hours']) && trim((string) $validated['working_hours']) !== '' ? $validated['working_hours'] : null;

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
            'working_hours' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['map_url'] = isset($validated['map_url']) && trim((string) $validated['map_url']) !== '' ? $validated['map_url'] : null;
        $validated['address'] = isset($validated['address']) && trim((string) $validated['address']) !== '' ? $validated['address'] : null;
        $validated['phone1'] = isset($validated['phone1']) && trim((string) $validated['phone1']) !== '' ? $validated['phone1'] : null;
        $validated['phone2'] = isset($validated['phone2']) && trim((string) $validated['phone2']) !== '' ? $validated['phone2'] : null;
        $validated['working_hours'] = isset($validated['working_hours']) && trim((string) $validated['working_hours']) !== '' ? $validated['working_hours'] : null;

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

    public function orders(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $ordersQuery = Order::query();
        if ($q !== '') {
            $ordersQuery->where(function ($builder) use ($q) {
                $builder->where('remote_jid', 'like', '%'.$q.'%')
                    ->orWhere('customer_name', 'like', '%'.$q.'%')
                    ->orWhere('phone', 'like', '%'.$q.'%')
                    ->orWhere('order_number', 'like', '%'.$q.'%');
            });
        }

        $orders = $ordersQuery->with('customer')->latest()->get();

        return view('dashboard.orders', [
            'orders' => $orders,
            'q'      => $q,
        ]);
    }

    public function createOrder(): View
    {
        $customers = Customer::query()->orderBy('name')->get(['remote_jid', 'phone', 'name', 'address']);

        return view('dashboard.orders-create', [
            'statuses'  => Order::STATUSES,
            'customers' => $customers,
        ]);
    }

    public function editOrder(Order $order): View
    {
        $order->load([
            'customer',
            'statusHistories' => fn ($query) => $query->orderBy('created_at'),
        ]);

        return view('dashboard.orders-edit', [
            'order'    => $order,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'remote_jid'       => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:2000'],
            'items'            => ['required', 'string', 'max:5000'],
            'status'           => ['required', 'string', Rule::in(Order::STATUSES)],
        ]);

        $orderNumber = (int) (Order::query()->max('order_number') ?? 0) + 1;

        $remoteJid = trim((string) ($validated['remote_jid'] ?? ''));
        $customer  = $remoteJid ? Customer::query()->where('remote_jid', $remoteJid)->first() : null;

        $order = Order::query()->create([
            'order_number'     => $orderNumber,
            'remote_jid'       => $remoteJid ?: null,
            'customer_name'    => $customer?->name,
            'phone'            => $customer?->phone,
            'delivery_address' => trim((string) ($validated['delivery_address'] ?? '')),
            'items_text'       => trim((string) $validated['items']),
            'status'           => $validated['status'],
            'status_changed_at'=> now(),
            'total_amount'     => 0,
            'delivery_fee'     => 0,
        ]);

        $this->recordOrderStatusChange($order, $validated['status'], 'إنشاء الطلب');

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'تم إنشاء الطلب بنجاح.');
    }

    public function updateOrder(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'remote_jid'       => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:2000'],
            'status'           => ['required', 'string', Rule::in(Order::STATUSES)],
            'notify_customer'  => ['nullable', 'boolean'],
        ]);

        $remoteJid  = trim((string) ($validated['remote_jid'] ?? ''));
        $customer   = $remoteJid ? Customer::query()->where('remote_jid', $remoteJid)->first() : null;
        $oldStatus  = $order->status;
        $newStatus  = $validated['status'];

        $order->update([
            'remote_jid'       => $remoteJid ?: null,
            'customer_name'    => $customer?->name ?? $order->customer_name,
            'phone'            => $customer?->phone ?? $order->phone,
            'delivery_address' => trim((string) ($validated['delivery_address'] ?? '')),
            'status'           => $newStatus,
            'status_changed_at'=> $oldStatus !== $newStatus ? now() : $order->status_changed_at,
        ]);

        if ($oldStatus !== $newStatus) {
            $this->recordOrderStatusChange($order, $newStatus, 'تعديل من صفحة التعديل');
        }

        $shouldNotify = (bool) ($validated['notify_customer'] ?? false);

        if ($shouldNotify && $remoteJid !== '') {
            $customerName = $order->displayCustomerName() !== '—' ? $order->displayCustomerName() : 'عزيزي العميل';
            $message = "مرحباً {$customerName} 👋\n\n"
                . "تم تحديث حالة طلبك رقم *{$order->order_number}*\n\n"
                . "📦 الحالة الجديدة: *{$newStatus}*\n\n"
                . "شكراً لتعاملكم معنا 🌿";

            $this->sendWhatsAppText($remoteJid, $message);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'تم تعديل الطلب بنجاح.');
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status'          => ['required', 'string', Rule::in(Order::STATUSES)],
            'notify_customer' => ['nullable', 'boolean'],
            'status_note'     => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            $order->update([
                'status'            => $newStatus,
                'status_changed_at' => now(),
            ]);

            $this->recordOrderStatusChange(
                $order,
                $newStatus,
                trim((string) ($validated['status_note'] ?? '')) ?: 'تغيير الحالة من صفحة التفاصيل'
            );
        }

        $remoteJid = trim((string) ($order->remote_jid ?? ''));
        if ($request->boolean('notify_customer') && $remoteJid !== '' && $oldStatus !== $newStatus) {
            $customerName = $order->displayCustomerName() !== '—' ? $order->displayCustomerName() : 'عزيزي العميل';
            $message = "مرحباً {$customerName} 👋\n\n"
                . "تم تحديث حالة طلبك رقم *{$order->order_number}*\n\n"
                . "📦 الحالة الجديدة: *{$newStatus}*\n\n"
                . "شكراً لتعاملكم معنا 🌿";

            $this->sendWhatsAppText($remoteJid, $message);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'تم تحديث حالة الطلب بنجاح.');
    }

    public function updateOrderNotes(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'delivery_fee'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $order->update([
            'internal_notes' => trim((string) ($validated['internal_notes'] ?? '')),
            'delivery_fee'   => (float) ($validated['delivery_fee'] ?? 0),
        ]);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'تم حفظ الملاحظات بنجاح.');
    }

    private function recordOrderStatusChange(Order $order, string $status, ?string $note = null, ?string $changedBy = null): void
    {
        OrderStatusHistory::query()->create([
            'order_id'   => $order->id,
            'status'     => $status,
            'changed_by' => $changedBy ?? Auth::user()?->email ?? 'api',
            'note'       => $note,
        ]);
    }

    private function applyPhoneFilterToOrdersQuery($query, string $phone): void
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        $query->where(function ($builder) use ($phone, $digits) {
            $builder->where('phone', $phone);

            if ($digits !== '') {
                $builder->orWhere('phone', 'like', '%'.$digits.'%')
                    ->orWhere('remote_jid', 'like', '%'.$digits.'%');
            }
        });
    }

    private function sendWhatsAppText(string $remoteJid, string $text): bool
    {
        $stored = Setting::query()
            ->whereIn('key', ['evo_url', 'evo_api_key', 'evo_instance'])
            ->pluck('value', 'key')
            ->toArray();

        $url      = rtrim($stored['evo_url'] ?? '', '/');
        $apiKey   = $stored['evo_api_key'] ?? '';
        $instance = $stored['evo_instance'] ?? '';

        if (! $url || ! $instance) {
            Log::warning('WhatsApp notification skipped: Evolution API not configured.');
            return false;
        }

        $number = (string) preg_replace('/@[^@]+$/', '', $remoteJid);

        try {
            $response = Http::withHeaders([
                'apikey'       => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("{$url}/message/sendText/{$instance}", [
                'number' => $number,
                'text'   => $text,
            ]);

            if (! $response->successful()) {
                Log::error('WhatsApp order notification failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'jid'    => $remoteJid,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp order notification exception', ['error' => $e->getMessage(), 'jid' => $remoteJid]);
            return false;
        }
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
        $order->load(['items', 'customer', 'statusHistories']);

        return view('dashboard.orders-show', [
            'order'    => $order,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function invoiceOrder(Order $order): View
    {
        $order->load(['items', 'customer']);

        return view('dashboard.orders-invoice', [
            'order' => $order,
        ]);
    }

    public function complaints(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $complaintsQuery = Complaint::query();
        if ($q !== '') {
            $complaintsQuery->where(function ($builder) use ($q) {
                $builder->where('remote_jid', 'like', '%'.$q.'%')
                    ->orWhere('title', 'like', '%'.$q.'%')
                    ->orWhere('description', 'like', '%'.$q.'%');
            });
        }

        $complaints = $complaintsQuery->with('customer')->latest()->get();

        return view('dashboard.complaints', [
            'complaints'      => $complaints,
            'q'               => $q,
            'complaintsStats' => [
                'total'    => Complaint::query()->count(),
                'today'    => Complaint::query()->whereDate('created_at', now()->toDateString())->count(),
                'last7days'=> Complaint::query()->where('created_at', '>=', now()->subDays(7))->count(),
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
            'remote_jid'  => ['nullable', 'string', 'max:255'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
        ]);

        Complaint::query()->create($validated);

        return redirect()
            ->route('complaints.index')
            ->with('success', 'تم حفظ الشكوى بنجاح.');
    }

    public function editComplaint(Complaint $complaint): View
    {
        return view('dashboard.complaints-edit', [
            'complaint' => $complaint,
            'statuses'  => \App\Models\Complaint::STATUSES,
        ]);
    }

    public function updateComplaint(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'remote_jid'      => ['nullable', 'string', 'max:255'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['required', 'string', 'max:3000'],
            'status'          => ['required', 'string', 'in:' . implode(',', \App\Models\Complaint::STATUSES)],
            'notify_customer' => ['nullable', 'boolean'],
        ]);

        $oldStatus = $complaint->status;
        $newStatus = $validated['status'];

        $complaint->update([
            'remote_jid'  => $validated['remote_jid'] ?? null,
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'status'      => $newStatus,
        ]);

        $shouldNotify = (bool) ($validated['notify_customer'] ?? false);
        $remoteJid    = trim((string) ($validated['remote_jid'] ?? $complaint->remote_jid ?? ''));

        if ($shouldNotify && $remoteJid !== '') {
            $customer     = \App\Models\Customer::query()->where('remote_jid', $remoteJid)->first();
            $customerName = $customer?->name ?? 'عزيزي العميل';

            $statusEmoji = match ($newStatus) {
                'جديدة'         => '🆕',
                'قيد المعالجة'  => '🔄',
                'تم الحل'       => '✅',
                'مغلقة'         => '🔒',
                default         => '📋',
            };

            $message = "مرحباً {$customerName} 👋\n\n"
                . "بخصوص شكواك: *{$complaint->title}*\n\n"
                . "{$statusEmoji} تم تحديث الحالة إلى: *{$newStatus}*\n\n"
                . "شكراً لتواصلكم معنا 🌿";

            $this->sendWhatsAppText($remoteJid, $message);
        }

        return redirect()
            ->route('complaints.index')
            ->with('success', 'تم تعديل الشكوى بنجاح.');
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
        path: '/api/products/search-by-disease',
        operationId: 'searchProductsByDisease',
        tags: ['Products'],
        summary: 'Search recommended products by disease name',
        description: 'Returns available products that are recommended for a given disease. The search is partial and case-insensitive — sending "سكر" will match "مرض السكري" etc.',
        parameters: [
            new OA\Parameter(
                name: 'disease',
                description: 'Disease name to search for (partial match)',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'السكري')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Products fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'disease', type: 'string', example: 'السكري'),
                        new OA\Property(property: 'count', type: 'integer', example: 3),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'حقل disease مطلوب'),
        ]
    )]
    public function apiSearchProductsByDisease(Request $request): JsonResponse
    {
        $request->validate([
            'disease' => ['required', 'string', 'max:255'],
        ]);

        $disease = trim((string) $request->query('disease'));

        // SQLite stores JSON with unicode escapes so DB-level LIKE on Arabic text
        // never matches. We fetch all available products and filter in PHP after
        // the model decodes the JSON cast correctly.
        $products = Product::query()
            ->where('is_available', true)
            ->latest()
            ->get()
            ->filter(fn (Product $product) =>
                collect($product->diseases ?? [])->contains(
                    fn ($d) => mb_stripos((string) $d, $disease) !== false
                )
            )
            ->values();

        $items = $products->map(fn (Product $product) => $this->transformProductForApi($product))->values();

        return response()->json([
            'success' => true,
            'disease' => $disease,
            'count'   => $items->count(),
            'data'    => $items,
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
        summary: 'Get complaints list',
        description: 'Returns complaints list with optional text search in title, description, or remote_jid.',
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
                    ->orWhere('remote_jid', 'like', '%'.$query.'%');
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
        summary: 'Create complaint',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description'],
                properties: [
                    new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net', nullable: true),
                    new OA\Property(property: 'title', type: 'string', example: 'استفسار عن المنتج'),
                    new OA\Property(property: 'description', type: 'string', example: 'أريد معرفة طريقة الاستخدام المناسبة.'),
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
            'remote_jid'  => ['nullable', 'string', 'max:255'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
        ]);

        $complaint = Complaint::query()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الشكوى بنجاح.',
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
        summary: 'Update complaint',
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
                required: ['title', 'description'],
                properties: [
                    new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net', nullable: true),
                    new OA\Property(property: 'title', type: 'string', example: 'شكوى عن تأخر الرد'),
                    new OA\Property(property: 'description', type: 'string', example: 'لم يصلني رد حتى الآن.'),
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
            'remote_jid'  => ['nullable', 'string', 'max:255'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
        ]);

        $complaint->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الشكوى بنجاح.',
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
            'message' => 'تم حذف الشكوى بنجاح.',
        ]);
    }

    #[OA\Post(
        path: '/api/orders',
        operationId: 'storeOrder',
        tags: ['Orders'],
        summary: 'Create order',
        description: 'Creates a new order. The items field must be a single text value describing requested products. Pass remote_jid to auto-fill customer name and phone from the customers table.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net', nullable: true),
                    new OA\Property(property: 'customer_name', type: 'string', example: 'محمد أحمد', nullable: true),
                    new OA\Property(property: 'phone', type: 'string', example: '96550000000', nullable: true),
                    new OA\Property(property: 'delivery_address', type: 'string', example: 'الكويت - حولي - شارع بيروت - قطعة 4 - منزل 12', nullable: true),
                    new OA\Property(property: 'delivery_fee', type: 'number', example: 1.5, nullable: true),
                    new OA\Property(property: 'status', ref: '#/components/schemas/OrderStatus'),
                    new OA\Property(property: 'items', type: 'string', example: 'عسل سدر 2 عبوة + عسل كشميري 1 عبوة'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/OrderMutationResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiStoreOrder(Request $request): JsonResponse
    {
        if (is_array($request->input('items'))) {
            return response()->json([
                'success' => false,
                'message' => 'حقل items لازم يكون نص واحد وليس list/array.',
                'example' => [
                    'items' => 'عسل سدر 2 عبوة + عسل كشميري 1 عبوة',
                ],
            ], 422);
        }

        $validated = $request->validate([
            'remote_jid'       => ['nullable', 'string', 'max:255'],
            'customer_name'    => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'delivery_address' => ['nullable', 'string', 'max:2000'],
            'delivery_fee'     => ['nullable', 'numeric', 'min:0'],
            'status'           => ['nullable', 'string', Rule::in(Order::STATUSES)],
            'items'            => ['required', 'string', 'max:5000'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $orderNumber = (int) (Order::query()->max('order_number') ?? 0) + 1;
            $remoteJid   = trim((string) ($validated['remote_jid'] ?? ''));
            $customer    = $remoteJid ? Customer::query()->where('remote_jid', $remoteJid)->first() : null;
            $status      = $validated['status'] ?? Order::DEFAULT_STATUS;

            $deliveryAddress = trim((string) ($validated['delivery_address'] ?? ''));
            if ($deliveryAddress === '' && $customer?->address) {
                $deliveryAddress = trim((string) $customer->address);
            }

            $order = Order::query()->create([
                'order_number'      => $orderNumber,
                'remote_jid'        => $remoteJid ?: null,
                'customer_name'     => trim((string) ($validated['customer_name'] ?? '')) ?: $customer?->name,
                'phone'             => trim((string) ($validated['phone'] ?? '')) ?: $customer?->phone,
                'delivery_address'  => $deliveryAddress,
                'items_text'        => trim((string) $validated['items']),
                'status'            => $status,
                'status_changed_at' => now(),
                'total_amount'      => 0,
                'delivery_fee'      => (float) ($validated['delivery_fee'] ?? 0),
            ]);

            $this->recordOrderStatusChange($order, $status, 'إنشاء الطلب عبر API', 'api');

            return $order;
        });

        $order->load(['items', 'customer', 'statusHistories' => fn ($query) => $query->orderBy('created_at')]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الطلب بنجاح.',
            'data' => $this->transformOrderForApi($order),
        ], 201);
    }

    #[OA\Get(
        path: '/api/orders/{order_number}',
        operationId: 'showOrder',
        tags: ['Orders'],
        summary: 'Get order by order number',
        parameters: [
            new OA\Parameter(
                name: 'order_number',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order fetched successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/OrderShowResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Order not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiShowOrder(int $order_number): JsonResponse
    {
        $order = Order::query()
            ->with(['items', 'customer', 'statusHistories' => fn ($query) => $query->orderBy('created_at')])
            ->where('order_number', $order_number)
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->transformOrderForApi($order),
        ]);
    }

    #[OA\Patch(
        path: '/api/orders/{order_number}',
        operationId: 'updateOrder',
        tags: ['Orders'],
        summary: 'Update order',
        parameters: [
            new OA\Parameter(
                name: 'order_number',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', ref: '#/components/schemas/OrderStatus'),
                    new OA\Property(property: 'delivery_address', type: 'string', nullable: true),
                    new OA\Property(property: 'delivery_fee', type: 'number', example: 1.5),
                    new OA\Property(property: 'items', type: 'string', nullable: true, description: 'Single text describing requested products (not an array)'),
                    new OA\Property(property: 'status_note', type: 'string', nullable: true, description: 'Note recorded in status history when status changes'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/OrderMutationResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Order not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiUpdateOrder(Request $request, int $order_number): JsonResponse
    {
        $order = Order::query()->where('order_number', $order_number)->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود.',
            ], 404);
        }

        if ($request->has('items') && is_array($request->input('items'))) {
            return response()->json([
                'success' => false,
                'message' => 'حقل items لازم يكون نص واحد وليس list/array.',
            ], 422);
        }

        $validated = $request->validate([
            'status'           => ['sometimes', 'string', Rule::in(Order::STATUSES)],
            'delivery_address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'delivery_fee'     => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'items'            => ['sometimes', 'string', 'max:5000'],
            'status_note'      => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $validated, $oldStatus) {
            $updates = [];

            if (array_key_exists('delivery_address', $validated)) {
                $updates['delivery_address'] = trim((string) ($validated['delivery_address'] ?? ''));
            }

            if (array_key_exists('delivery_fee', $validated)) {
                $updates['delivery_fee'] = (float) ($validated['delivery_fee'] ?? 0);
            }

            if (array_key_exists('items', $validated)) {
                $updates['items_text'] = trim((string) $validated['items']);
            }

            if (array_key_exists('status', $validated)) {
                $newStatus = $validated['status'];
                $updates['status'] = $newStatus;

                if ($newStatus !== $oldStatus) {
                    $updates['status_changed_at'] = now();
                }
            }

            if ($updates !== []) {
                $order->update($updates);
            }

            if (array_key_exists('status', $validated) && $validated['status'] !== $oldStatus) {
                $this->recordOrderStatusChange(
                    $order,
                    $validated['status'],
                    trim((string) ($validated['status_note'] ?? '')) ?: 'تحديث الحالة عبر API',
                    'api'
                );
            }
        });

        $order->refresh()->load(['items', 'customer', 'statusHistories' => fn ($query) => $query->orderBy('created_at')]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الطلب بنجاح.',
            'data'    => $this->transformOrderForApi($order),
        ]);
    }

    #[OA\Get(
        path: '/api/orders/status',
        operationId: 'getOrderStatusByPhone',
        tags: ['Orders'],
        summary: 'Get order status',
        description: 'Returns latest order status and all matching orders by order_number, customer_name, or phone. When searching by phone, cancelled ("ملغي") and completed ("مكتمل") orders are excluded by default — pass include_closed=1 to include them.',
        parameters: [
            new OA\Parameter(
                name: 'order_number',
                description: 'Order number (preferred)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1024)
            ),
            new OA\Parameter(
                name: 'customer_name',
                description: 'Customer name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'محمد أحمد')
            ),
            new OA\Parameter(
                name: 'phone',
                description: 'Customer phone number',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: '96550000000')
            ),
            new OA\Parameter(
                name: 'include_closed',
                description: 'Set to 1 to include cancelled (ملغي) and completed (مكتمل) orders (only applies to phone search)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 0)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order status fetched successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/OrderStatusSearchResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error — provide order_number, customer_name, or phone',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiOrderStatusByPhone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number'  => ['nullable', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'include_closed' => ['nullable', 'boolean'],
        ]);

        $orderNumber  = isset($validated['order_number']) ? (int) $validated['order_number'] : null;
        $customerName = trim((string) ($validated['customer_name'] ?? ''));
        $phone        = trim((string) ($validated['phone'] ?? ''));
        $includeClosed = filter_var($validated['include_closed'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $orderNumber && $customerName === '' && $phone === '') {
            return response()->json([
                'success' => false,
                'message' => 'يرجى إرسال order_number أو customer_name أو phone.',
            ], 422);
        }

        $ordersQuery = Order::query()->with(['items', 'customer', 'statusHistories' => fn ($query) => $query->orderBy('created_at')]);

        if ($orderNumber) {
            $ordersQuery->where('order_number', $orderNumber);
        } elseif ($customerName !== '') {
            $ordersQuery->where('customer_name', 'like', '%'.$customerName.'%');
        } else {
            $this->applyPhoneFilterToOrdersQuery($ordersQuery, $phone);

            if (! $includeClosed) {
                $ordersQuery->whereNotIn('status', Order::CLOSED_STATUSES);
            }
        }

        $orders = $ordersQuery->latest()->get();

        $items  = $orders->map(fn (Order $order) => $this->transformOrderForApi($order))->values();
        $latest = $items->first();

        return response()->json([
            'success'       => true,
            'found'         => $latest !== null,
            'latest_status' => $latest['status'] ?? null,
            'count'         => $items->count(),
            'latest_order'  => $latest,
            'orders'        => $items,
        ]);
    }

    #[OA\Get(
        path: '/api/orders/by-phone',
        operationId: 'getOrdersByPhone',
        tags: ['Orders'],
        summary: 'Get active orders by phone number',
        description: 'Returns all orders for the given phone number, excluding cancelled ("ملغي") and completed ("مكتمل") orders.',
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
            new OA\Response(
                response: 200,
                description: 'Orders fetched successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/OrdersByPhoneResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'حقل phone مطلوب',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiOrdersByPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $phone = trim((string) $request->query('phone'));

        $ordersQuery = Order::query()
            ->with(['items', 'customer', 'statusHistories' => fn ($query) => $query->orderBy('created_at')])
            ->whereNotIn('status', Order::CLOSED_STATUSES);

        $this->applyPhoneFilterToOrdersQuery($ordersQuery, $phone);

        $orders = $ordersQuery->latest()->get();

        $items = $orders->map(fn (Order $order) => $this->transformOrderForApi($order))->values();

        return response()->json([
            'success' => true,
            'phone'   => $phone,
            'count'   => $items->count(),
            'orders'  => $items,
        ]);
    }

    #[OA\Get(
        path: '/api/agent/prompts',
        operationId: 'getAgentPrompts',
        tags: ['AI Agent'],
        summary: 'Get AI Agent prompts',
        description: 'Returns Positive Prompt and Negative Prompt saved in system settings.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Agent prompts fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'positive_prompt', type: 'string', nullable: true, example: 'كن ودودًا وركز على احتياج العميل'),
                                new OA\Property(property: 'negative_prompt', type: 'string', nullable: true, example: 'لا تقدم وعودًا غير واقعية'),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function apiAgentPrompts(Request $request): JsonResponse
    {
        $prompts = Setting::query()
            ->whereIn('key', ['ai_positive_prompt', 'ai_negative_prompt'])
            ->pluck('value', 'key')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'positive_prompt' => $prompts['ai_positive_prompt'] ?? null,
                'negative_prompt' => $prompts['ai_negative_prompt'] ?? null,
            ],
        ]);
    }

    public function createProduct(): View
    {
        return view('dashboard.products-create');
    }

    public function editProduct(Product $product): View
    {
        $product->load('categories');

        return view('dashboard.products-edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('title')->get(),
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
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
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

        $product->categories()->sync($validated['category_ids'] ?? []);

        return redirect()
            ->route('products.index')
            ->with('success', 'تم تعديل المنتج بنجاح.');
    }

    // ─── FAQs ────────────────────────────────────────────────────────────────

    public function faqs(): View
    {
        $faqs = \App\Models\Faq::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('dashboard.faqs', compact('faqs'));
    }

    public function createFaq(): View
    {
        return view('dashboard.faqs-create');
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question'   => ['required', 'string', 'max:1000'],
            'answer'     => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        \App\Models\Faq::query()->create([
            'question'   => $validated['question'],
            'answer'     => $validated['answer'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => isset($validated['is_active']) ? (bool) $validated['is_active'] : true,
        ]);

        return redirect()->route('faqs.index')->with('success', 'تم إضافة السؤال والجواب بنجاح.');
    }

    public function editFaq(\App\Models\Faq $faq): View
    {
        return view('dashboard.faqs-edit', compact('faq'));
    }

    public function updateFaq(Request $request, \App\Models\Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'question'   => ['required', 'string', 'max:1000'],
            'answer'     => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $faq->update([
            'question'   => $validated['question'],
            'answer'     => $validated['answer'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : false,
        ]);

        return redirect()->route('faqs.index')->with('success', 'تم تعديل السؤال والجواب بنجاح.');
    }

    public function destroyFaq(\App\Models\Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('faqs.index')->with('success', 'تم حذف السؤال والجواب بنجاح.');
    }

    // ─── API: FAQs ───────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/faqs',
        operationId: 'getFaqs',
        tags: ['FAQs'],
        summary: 'Get active FAQs list',
        description: 'Returns all active frequently asked questions ordered by sort_order.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'FAQs fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'question', type: 'string', example: 'ما هي فوائد عسل السدر؟'),
                                    new OA\Property(property: 'answer', type: 'string', example: 'عسل السدر غني بالمعادن والفيتامينات...'),
                                    new OA\Property(property: 'sort_order', type: 'integer', example: 0),
                                ]
                            )
                        ),
                    ]
                )
            ),
        ]
    )]
    public function apiFaqs(): JsonResponse
    {
        $faqs = \App\Models\Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (\App\Models\Faq $faq) => [
                'id'         => $faq->id,
                'question'   => $faq->question,
                'answer'     => $faq->answer,
                'sort_order' => $faq->sort_order,
            ]);

        return response()->json(['data' => $faqs]);
    }

    #[OA\Get(
        path: '/api/faqs/text',
        operationId: 'getFaqsAsText',
        tags: ['FAQs'],
        summary: 'Get all active FAQs as a single text block',
        description: 'Returns all active FAQs combined into one text string — questions and answers concatenated together, useful for AI agents and prompts.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'FAQs text fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'string',
                            example: "س: ما هي فوائد عسل السدر؟\nج: عسل السدر غني بالمعادن والفيتامينات ويُقوّي جهاز المناعة.\n\nس: هل يمكن تناوله يومياً؟\nج: نعم، ملعقة كبيرة صباحاً على الريق."
                        ),
                    ]
                )
            ),
        ]
    )]
    public function apiFaqsText(): JsonResponse
    {
        $faqs = \App\Models\Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $text = $faqs->map(fn (\App\Models\Faq $faq) =>
            'س: '.trim($faq->question)."\nج: ".trim($faq->answer)
        )->implode("\n\n");

        return response()->json(['data' => $text]);
    }

    // ─── API: Customers ──────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/customers/check',
        operationId: 'checkCustomer',
        tags: ['Customers'],
        summary: 'التحقق من تسجيل عميل بالهاتف أو remoteJid',
        description: 'يتحقق إذا كان العميل مسجلاً بـ phone أو remote_jid. يجب إرسال أحدهما على الأقل. إذا كان مسجلاً تُرجع بياناته مسطّحة، وإلا يُرجع registered: false.',
        parameters: [
            new OA\Parameter(
                name: 'phone',
                description: 'رقم الهاتف (أو أرسل remote_jid)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: '01012345678')
            ),
            new OA\Parameter(
                name: 'remote_jid',
                description: 'remoteJid (أو أرسل phone)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: '96501012345@s.whatsapp.net')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'نتيجة التحقق — registered: true مع بيانات العميل، أو registered: false مع رسالة',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: '#/components/schemas/CustomerCheckRegisteredResponse'),
                        new OA\Schema(ref: '#/components/schemas/CustomerCheckUnregisteredResponse'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'يجب إرسال phone أو remote_jid',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiCheckCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'phone'      => ['nullable', 'string'],
            'remote_jid' => ['nullable', 'string'],
        ]);

        $phone     = trim((string) ($request->query('phone') ?? ''));
        $remoteJid = trim((string) ($request->query('remote_jid') ?? ''));

        if ($phone === '' && $remoteJid === '') {
            return response()->json([
                'success' => false,
                'message' => 'يجب إرسال phone أو remote_jid.',
            ], 422);
        }

        $customer = null;

        if ($phone !== '') {
            $customer = Customer::query()->find($phone);
        }

        if (! $customer && $remoteJid !== '') {
            $customer = Customer::query()->where('remote_jid', $remoteJid)->first();
        }

        if ($customer) {
            $autoReply = $this->loadAutoReplySettings();

            return response()->json(array_merge(
                ['registered' => true],
                $this->transformCustomerForApi($customer, $autoReply)
            ));
        }

        return response()->json([
            'registered' => false,
            'message'    => 'هذا الرقم غير مسجل.',
        ]);
    }

    #[OA\Get(
        path: '/api/customers/check-and-save',
        operationId: 'checkAndSaveCustomer',
        tags: ['Customers'],
        summary: 'تحقق وسجّل تلقائياً بالـ remoteJid',
        description: 'يبحث عن العميل بالـ remote_jid. إذا وُجد يُرجع registered: true وبياناته. إذا لم يُوجد يُسجّله تلقائياً (remote_jid فقط، phone فارغ) ويُرجع registered: false و newly_created: true. مناسب لتسجيل العملاء تلقائياً عند أول رسالة واتساب.',
        parameters: [
            new OA\Parameter(
                name: 'remote_jid',
                description: 'معرّف واتساب للعميل (remoteJid)',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: '96550000000@s.whatsapp.net')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'نتيجة التحقق والحفظ — بدون حقل name',
                content: new OA\JsonContent(ref: '#/components/schemas/CustomerCheckAndSaveResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'حقل remote_jid مطلوب',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiCheckAndSaveCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'remote_jid' => ['required', 'string', 'max:255'],
        ]);

        $remoteJid = trim((string) $request->query('remote_jid'));

        $customer = Customer::query()->where('remote_jid', $remoteJid)->first();

        $autoReply    = $this->loadAutoReplySettings();
        $newlyCreated = false;

        if (! $customer) {
            $customer = Customer::query()->create([
                'remote_jid' => $remoteJid,
                'phone'      => null,
                'name'       => '',
                'address'    => null,
            ]);
            $newlyCreated = true;
        }

        $orders = Order::query()
            ->where('remote_jid', $remoteJid)
            ->latest()
            ->get(['order_number', 'status', 'items_text', 'created_at']);

        $complaints = Complaint::query()
            ->where('remote_jid', $remoteJid)
            ->latest()
            ->get(['id', 'title', 'status', 'created_at']);

        return response()->json(array_merge(
            [
                'registered'        => ! $newlyCreated,
                'newly_created'     => $newlyCreated,
                'global_auto_reply' => (bool) ($autoReply['global'] ?? false),
            ],
            $this->transformCustomerForApi($customer, $autoReply, includeName: false),
            [
                'orders' => $orders->map(fn ($o) => [
                    'order_number' => $o->order_number,
                    'status'       => $o->status,
                    'items'        => $o->items_text,
                ])->values(),

                'complaints' => $complaints->map(fn ($c) => [
                    'id'     => $c->id,
                    'title'  => $c->title,
                    'status' => $c->status,
                ])->values(),
            ]
        ));
    }

    #[OA\Get(
        path: '/api/customers',
        operationId: 'getCustomers',
        tags: ['Customers'],
        summary: 'Get customers list',
        description: 'Returns all customers. Supports optional search by name, phone, address, or remoteJid.',
        parameters: [
            new OA\Parameter(
                name: 'q',
                description: 'Search query (optional) — searches name, phone, address, remote_jid',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'محمد')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Customers fetched successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/CustomerListResponse')
            ),
        ]
    )]
    public function apiCustomers(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        $customersQuery = Customer::query();
        if ($query !== '') {
            $customersQuery->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%'.$query.'%')
                    ->orWhere('phone', 'like', '%'.$query.'%')
                    ->orWhere('address', 'like', '%'.$query.'%')
                    ->orWhere('remote_jid', 'like', '%'.$query.'%');
            });
        }

        $customers = $customersQuery->latest()->get();
        $autoReply = $this->loadAutoReplySettings();
        $items = $customers->map(fn (Customer $customer) => $this->transformCustomerForApi($customer, $autoReply))->values();

        return response()->json([
            'success' => true,
            'query' => $query,
            'count' => $items->count(),
            'data' => $items,
        ]);
    }

    #[OA\Get(
        path: '/api/customers/{remote_jid}',
        operationId: 'showCustomer',
        tags: ['Customers'],
        summary: 'Get a single customer by remoteJid',
        parameters: [
            new OA\Parameter(
                name: 'remote_jid',
                description: 'Customer remoteJid (primary key)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '96550000000@s.whatsapp.net')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Customer fetched successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/CustomerShowResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiShowCustomer(string $remote_jid): JsonResponse
    {
        $customer = Customer::query()->find($remote_jid);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformCustomerForApi($customer, $this->loadAutoReplySettings()),
        ]);
    }

    #[OA\Post(
        path: '/api/customers',
        operationId: 'storeCustomer',
        tags: ['Customers'],
        summary: 'Create a new customer',
        description: 'remoteJid is the primary key and cannot be changed after creation. Phone is optional.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['remote_jid', 'name'],
                properties: [
                    new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '96550000000'),
                    new OA\Property(property: 'name', type: 'string', example: 'محمد أحمد'),
                    new OA\Property(property: 'address', type: 'string', example: 'القاهرة - مدينة نصر', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Customer created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/CustomerMutationResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error — remote_jid already exists or missing required fields',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiStoreCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'remote_jid' => ['required', 'string', 'max:255', 'unique:customers,remote_jid'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'name'       => ['required', 'string', 'max:255'],
            'address'    => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = Customer::query()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المستخدم بنجاح.',
            'data' => $this->transformCustomerForApi($customer, $this->loadAutoReplySettings()),
        ], 201);
    }

    #[OA\Put(
        path: '/api/customers/{remote_jid}',
        operationId: 'updateCustomer',
        tags: ['Customers'],
        summary: 'Update customer data',
        description: 'remoteJid is fixed and cannot be changed. Phone, name, and address can be updated.',
        parameters: [
            new OA\Parameter(
                name: 'remote_jid',
                description: 'Customer remoteJid (primary key)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '96550000000@s.whatsapp.net')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '96550000000'),
                    new OA\Property(property: 'name', type: 'string', example: 'محمد أحمد محدث'),
                    new OA\Property(property: 'address', type: 'string', example: 'الإسكندرية - سيدي بشر', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Customer updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/CustomerMutationResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiUpdateCustomer(Request $request, string $remote_jid): JsonResponse
    {
        $customer = Customer::query()->find($remote_jid);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        $validated = $request->validate([
            'phone'   => ['nullable', 'string', 'max:50'],
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات المستخدم بنجاح.',
            'data' => $this->transformCustomerForApi($customer->fresh(), $this->loadAutoReplySettings()),
        ]);
    }

    #[OA\Delete(
        path: '/api/customers/{remote_jid}',
        operationId: 'deleteCustomer',
        tags: ['Customers'],
        summary: 'Delete a customer',
        parameters: [
            new OA\Parameter(
                name: 'remote_jid',
                description: 'Customer remoteJid (primary key)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '96550000000@s.whatsapp.net')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Customer deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف المستخدم بنجاح.'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function apiDestroyCustomer(string $remote_jid): JsonResponse
    {
        $customer = Customer::query()->find($remote_jid);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح.',
        ]);
    }

    // ─── Settings ────────────────────────────────────────────────────────────

    public function settings(): View
    {
        $defaults = [
            'store_name' => 'Mujeza',
            'email' => 'admin@mujeza.local',
            'ai_positive_prompt' => '',
            'ai_negative_prompt' => '',
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
            'campaign_phone_limit' => '100',
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
            'ai_positive_prompt' => ['nullable', 'string', 'max:10000'],
            'ai_negative_prompt' => ['nullable', 'string', 'max:10000'],
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
            'campaign_phone_limit' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value !== null && trim((string) $value) !== '' ? $value : null]
            );
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'تم حفظ إعدادات النظام وبيانات الـ AI Agent بنجاح.');
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

    private function loadAutoReplySettings(): array
    {
        $globalRaw = (string) (Setting::query()->where('key', 'whatsapp_auto_reply_global_enabled')->value('value') ?? '1');
        $global    = in_array(strtolower($globalRaw), ['1', 'true', 'on', 'yes'], true);

        $overridesRaw = Setting::query()->where('key', 'whatsapp_auto_reply_chat_overrides')->value('value');
        $overrides    = [];
        if (is_string($overridesRaw) && trim($overridesRaw) !== '') {
            $decoded = json_decode($overridesRaw, true);
            if (is_array($decoded)) {
                $overrides = $decoded;
            }
        }

        return ['global' => $global, 'overrides' => $overrides];
    }

    private function transformCustomerForApi(Customer $customer, array $autoReply = [], bool $includeName = true): array
    {
        $global    = $autoReply['global'] ?? false;
        $overrides = $autoReply['overrides'] ?? [];
        // remote_jid IS the chat identifier
        $chatId           = $customer->remote_jid;
        $autoReplyEnabled = array_key_exists($chatId, $overrides)
            ? (bool) $overrides[$chatId]
            : $global;

        $data = [
            'remote_jid'  => $customer->remote_jid,
            'phone'       => $customer->phone,
            'address'     => $customer->address,
            'auto_reply'  => $autoReplyEnabled,
            'created_at'  => optional($customer->created_at)?->toISOString(),
            'updated_at'  => optional($customer->updated_at)?->toISOString(),
        ];

        if ($includeName) {
            $data['name'] = $customer->name;
        }

        return $data;
    }

    private function transformComplaintForApi(Complaint $complaint): array
    {
        return [
            'id'          => $complaint->id,
            'remote_jid'  => $complaint->remote_jid,
            'title'       => $complaint->title,
            'description' => $complaint->description,
            'created_at'  => optional($complaint->created_at)?->toISOString(),
            'updated_at'  => optional($complaint->updated_at)?->toISOString(),
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
            'working_hours' => $branch->working_hours,
            'created_at' => optional($branch->created_at)?->toISOString(),
            'updated_at' => optional($branch->updated_at)?->toISOString(),
        ];
    }

    private function transformOrderForApi(Order $order): array
    {
        $itemsText = trim((string) ($order->items_text ?? ''));
        $lineItems = [];

        if ($order->relationLoaded('items') && $order->items->isNotEmpty()) {
            $lineItems = $order->items->map(fn (OrderItem $item) => [
                'product_id'    => $item->product_id,
                'product_title' => $item->product_title,
                'unit_price'    => (float) $item->unit_price,
                'quantity'      => (int) $item->quantity,
                'line_total'    => (float) $item->line_total,
            ])->values()->all();

            if ($itemsText === '') {
                $itemsText = $order->items
                    ->map(fn (OrderItem $item) => $item->product_title.' x'.$item->quantity)
                    ->implode(' + ');
            }
        }

        $statusHistories = $order->relationLoaded('statusHistories')
            ? $order->statusHistories->sortBy('created_at')->values()->map(fn (OrderStatusHistory $history) => [
                'status'     => $history->status,
                'note'       => $history->note,
                'changed_by' => $history->changed_by,
                'created_at' => optional($history->created_at)?->toISOString(),
            ])->all()
            : [];

        return [
            'id'                 => $order->id,
            'order_number'       => $order->order_number,
            'remote_jid'         => $order->remote_jid,
            'customer_name'      => $order->displayCustomerName() !== '—' ? $order->displayCustomerName() : null,
            'phone'              => $order->displayPhone() !== '—' ? $order->displayPhone() : null,
            'delivery_address'   => $order->displayAddress() !== '—' ? $order->displayAddress() : null,
            'status'             => $order->status,
            'available_statuses' => Order::STATUSES,
            'status_changed_at'  => optional($order->status_changed_at)?->toISOString(),
            'subtotal'           => $order->itemsSubtotal(),
            'delivery_fee'       => (float) $order->delivery_fee,
            'grand_total'        => $order->grandTotal(),
            'total_amount'       => $order->grandTotal(),
            'items'              => $itemsText,
            'line_items'         => $lineItems,
            'status_history'     => $statusHistories,
            'created_at'         => optional($order->created_at)?->toISOString(),
            'updated_at'         => optional($order->updated_at)?->toISOString(),
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
