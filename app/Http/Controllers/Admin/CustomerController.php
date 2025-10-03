<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerStoreRequest;
use App\Http\Requests\Admin\CustomerUpdateRequest;
use App\Models\Customer;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    /**
     * Show the form to create a new customer.
     */
    public function create()
    {
        $statusOptions = [
            'active' => __('cms.customers.active'),
            'inactive' => __('cms.customers.inactive'),
        ];
        $loyaltyTierOptions = [
            'bronze' => __('cms.customers.loyalty_tier_bronze'),
            'silver' => __('cms.customers.loyalty_tier_silver'),
            'gold' => __('cms.customers.loyalty_tier_gold'),
            'platinum' => __('cms.customers.loyalty_tier_platinum'),
        ];

        return view('admin.customers.create', [
            'statusOptions' => $statusOptions,
            'loyaltyTierOptions' => $loyaltyTierOptions,
        ]);
    }

    /**
     * Store a new customer.
     */
    public function store(CustomerStoreRequest $request)
    {
        Customer::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->input('password')),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'status' => $request->string('status'),
            'marketing_opt_in' => $request->boolean('marketing_opt_in'),
            'loyalty_tier' => $request->string('loyalty_tier'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * List all customers.
     */

    public function index(Request $request)
    {
        $statusOptions = [
            '' => __('cms.customers.filter_status_all'),
            'active' => __('cms.customers.active'),
            'inactive' => __('cms.customers.inactive'),
        ];
        $loyaltyTierOptions = [
            '' => __('cms.customers.filter_tier_all'),
            'bronze' => __('cms.customers.loyalty_tier_bronze'),
            'silver' => __('cms.customers.loyalty_tier_silver'),
            'gold' => __('cms.customers.loyalty_tier_gold'),
            'platinum' => __('cms.customers.loyalty_tier_platinum'),
        ];
        $marketingOptions = [
            '' => __('cms.customers.filter_marketing_all'),
            'opted_in' => __('cms.customers.filter_marketing_opted_in'),
            'opted_out' => __('cms.customers.filter_marketing_opted_out'),
        ];

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $tier = (string) $request->query('tier', '');
        $marketing = (string) $request->query('marketing', '');

        if (! array_key_exists($status, $statusOptions)) {
            $status = '';
        }

        if (! array_key_exists($tier, $loyaltyTierOptions)) {
            $tier = '';
        }

        if (! array_key_exists($marketing, $marketingOptions)) {
            $marketing = '';
        }

        $filters = [
            'search' => $search,
            'status' => $status,
            'tier' => $tier,
            'marketing' => $marketing,
        ];

        $query = Customer::query()
            ->with('defaultAddress')
            ->when($filters['search'] !== '', function (Builder $builder) use ($filters): void {
                $builder->where(function (Builder $nested) use ($filters): void {
                    $term = '%' . $filters['search'] . '%';
                    $nested
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when(in_array($filters['status'], ['active', 'inactive'], true), function (Builder $builder) use ($filters): void {
                $builder->where('status', $filters['status']);
            })
            ->when(in_array($filters['tier'], ['bronze', 'silver', 'gold', 'platinum'], true), function (Builder $builder) use ($filters): void {
                $builder->where('loyalty_tier', $filters['tier']);
            })
            ->when($filters['marketing'] === 'opted_in', function (Builder $builder): void {
                $builder->where('marketing_opt_in', true);
            })
            ->when($filters['marketing'] === 'opted_out', function (Builder $builder): void {
                $builder->where('marketing_opt_in', false);
            })
            ->latest();

        $customers = $query->paginate(15)->withQueryString();

        $statusCounts = Customer::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $marketingCounts = Customer::query()
            ->selectRaw('marketing_opt_in, COUNT(*) as aggregate')
            ->groupBy('marketing_opt_in')
            ->pluck('aggregate', 'marketing_opt_in');

        $tierCounts = Customer::query()
            ->selectRaw('loyalty_tier, COUNT(*) as aggregate')
            ->groupBy('loyalty_tier')
            ->pluck('aggregate', 'loyalty_tier');

        $tierCountsArray = [
            'bronze' => (int) ($tierCounts['bronze'] ?? 0),
            'silver' => (int) ($tierCounts['silver'] ?? 0),
            'gold' => (int) ($tierCounts['gold'] ?? 0),
            'platinum' => (int) ($tierCounts['platinum'] ?? 0),
        ];

        $topTierKey = collect($tierCountsArray)
            ->sortByDesc(fn ($count) => $count)
            ->filter(fn ($count) => $count > 0)
            ->keys()
            ->first();

        $topTier = [
            'label' => $topTierKey
                ? __('cms.customers.loyalty_tier_' . $topTierKey)
                : __('cms.customers.loyalty_tier_none'),
            'count' => $topTierKey ? $tierCountsArray[$topTierKey] : 0,
        ];

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => $filters,
            'statusOptions' => $statusOptions,
            'loyaltyTierOptions' => $loyaltyTierOptions,
            'marketingOptions' => $marketingOptions,
            'statusCounts' => [
                'active' => (int) ($statusCounts['active'] ?? 0),
                'inactive' => (int) ($statusCounts['inactive'] ?? 0),
            ],
            'marketingCounts' => [
                'opted_in' => (int) ($marketingCounts[true] ?? 0),
                'opted_out' => (int) ($marketingCounts[false] ?? 0),
            ],
            'tierCounts' => $tierCountsArray,
            'topTier' => $topTier,
        ]);
    }


    public function getCustomerData()
    {
        $customers = Customer::with('defaultAddress')
            ->select(['id', 'name', 'email', 'phone', 'address', 'status', 'marketing_opt_in', 'loyalty_tier']);

        return DataTables::of($customers)
            ->editColumn('address', function (Customer $customer) {
                $address = $customer->primary_address_line;

                return $address
                    ? e($address)
                    : e(__('cms.customers.not_available'));
            })
            ->addColumn('status', function ($customer) {
                $label = $customer->status === 'active'
                    ? __('cms.customers.active')
                    : __('cms.customers.inactive');

                $class = $customer->status === 'active'
                    ? 'badge badge-success'
                    : 'badge badge-danger';

                return '<span class="' . $class . '">' . e($label) . '</span>';
            })
            ->addColumn('marketing_opt_in', function ($customer) {
                $label = $customer->marketing_opt_in
                    ? __('cms.customers.marketing_opted_in')
                    : __('cms.customers.marketing_opted_out');

                $class = $customer->marketing_opt_in
                    ? 'badge badge-success'
                    : 'badge';

                return '<span class="' . $class . '">' . e($label) . '</span>';
            })
            ->addColumn('loyalty_tier', function ($customer) {
                $tierKey = match ($customer->loyalty_tier) {
                    'silver' => 'loyalty_tier_silver',
                    'gold' => 'loyalty_tier_gold',
                    'platinum' => 'loyalty_tier_platinum',
                    default => 'loyalty_tier_bronze',
                };

                return e(__('cms.customers.' . $tierKey));
            })
            ->addColumn('action', function ($customer) {
                $viewUrl = route('admin.customers.show', $customer);
                $viewLabel = __('cms.customers.view_button');
                $deleteLabel = __('cms.customers.delete_button');
                $viewLabelEscaped = e($viewLabel);
                $deleteLabelEscaped = e($deleteLabel);

                return <<<HTML
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" data-url="{$viewUrl}" title="{$viewLabelEscaped}">
                                <i class="bi bi-eye"></i>
                                <span class="ms-1">{$viewLabelEscaped}</span>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-delete-customer" data-id="{$customer->id}" title="{$deleteLabelEscaped}">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    HTML;
            })
            ->rawColumns(['status', 'marketing_opt_in', 'action'])
            ->make(true);
    }

    /**
     * Show the edit form for a customer.
     */
    public function edit(Customer $customer)
    {
        $statusOptions = [
            'active' => __('cms.customers.active'),
            'inactive' => __('cms.customers.inactive'),
        ];
        $loyaltyTierOptions = [
            'bronze' => __('cms.customers.loyalty_tier_bronze'),
            'silver' => __('cms.customers.loyalty_tier_silver'),
            'gold' => __('cms.customers.loyalty_tier_gold'),
            'platinum' => __('cms.customers.loyalty_tier_platinum'),
        ];

        return view('admin.customers.edit', compact('customer', 'statusOptions', 'loyaltyTierOptions'));
    }

    /**
     * Display the specified customer along with related data.
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'orders' => function ($query) {
                $query->with([
                    'details.product.translation',
                    'shippingAddress',
                    'payments.gateway',
                    'latestPayment.gateway',
                ])->latest();
            },
            'wishlists' => function ($query) {
                $query->with(['product.translation']);
            },
            'reviews' => function ($query) {
                $query->with(['product.translation'])->latest();
            },
            'addresses',
            'defaultAddress',
        ]);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Update a customer.
     */
    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $data = $request->only(['name', 'email', 'phone', 'address', 'status', 'loyalty_tier', 'notes']);

        $data['marketing_opt_in'] = $request->boolean('marketing_opt_in');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $customer->update($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Delete a customer.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.customers.delete_success_message'),
        ]);
    }
}
