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

        return view('admin.customers.create', [
            'statusOptions' => $statusOptions,
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

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        if (! array_key_exists($status, $statusOptions)) {
            $status = '';
        }

        $filters = [
            'search' => $search,
            'status' => $status,
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
            ->latest();

        $customers = $query->paginate(15)->withQueryString();

        $statusCounts = Customer::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => $filters,
            'statusOptions' => $statusOptions,
            'statusCounts' => [
                'active' => (int) ($statusCounts['active'] ?? 0),
                'inactive' => (int) ($statusCounts['inactive'] ?? 0),
            ],
        ]);
    }

    public function getCustomerData()
    {
        $customers = Customer::with('defaultAddress')
            ->select(['id', 'name', 'email', 'phone', 'address', 'status']);

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
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    /**
     * Show the edit form for a customer.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
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
        $data = $request->only(['name', 'email', 'phone', 'address', 'status']);

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
