<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CustomerAddressStoreRequest;
use App\Http\Requests\Store\CustomerAddressUpdateRequest;
use App\Models\CustomerAddress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomerAddressController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();
        $addresses = $customer->addresses()->orderByDesc('is_default')->get();

        return view('themes.xylo.profile.addresses', compact('customer', 'addresses'));
    }

    public function store(CustomerAddressStoreRequest $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $address = $customer->addresses()->create($request->validated());

        if ($address->is_default) {
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        return back()->with('success', __('cms.customers.address_created'));
    }

    public function update(CustomerAddressUpdateRequest $request, CustomerAddress $address): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        abort_if($address->customer_id !== $customer->id, 403);
        $address->update($request->validated());

        if ($address->is_default) {
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        return back()->with('success', __('cms.customers.address_updated'));
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        abort_if($address->customer_id !== $customer->id, 403);
        $address->delete();

        return back()->with('success', __('cms.customers.address_deleted'));
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        abort_if($address->customer_id !== $customer->id, 403);
        $customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', __('cms.customers.address_set_default'));
    }
}
