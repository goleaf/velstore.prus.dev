<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerAddressStoreRequest;
use App\Http\Requests\Admin\CustomerAddressUpdateRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;

class CustomerAddressController extends Controller
{
    public function store(CustomerAddressStoreRequest $request, Customer $customer): RedirectResponse
    {
        $address = $customer->addresses()->create($request->validated());

        if ($address->is_default) {
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        return back()->with('success', __('cms.customers.address_created'));
    }

    public function update(CustomerAddressUpdateRequest $request, Customer $customer, CustomerAddress $address): RedirectResponse
    {
        $address->update($request->validated());

        if ($address->is_default) {
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        return back()->with('success', __('cms.customers.address_updated'));
    }

    public function destroy(Customer $customer, CustomerAddress $address): RedirectResponse
    {
        $address->delete();

        return back()->with('success', __('cms.customers.address_deleted'));
    }

    public function setDefault(Customer $customer, CustomerAddress $address): RedirectResponse
    {
        $customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', __('cms.customers.address_set_default'));
    }
}
