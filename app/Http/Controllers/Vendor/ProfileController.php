<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function edit()
    {
        $vendor = Auth::guard('vendor')->user();

        return view('vendor.profile.edit', compact('vendor'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $vendor = Auth::guard('vendor')->user();

        try {
            DB::transaction(function () use ($request, $vendor) {
                $this->updateVendorProfile($vendor, $request->validated());
            });

            Log::info('Vendor profile updated', ['vendor_id' => $vendor->id]);

            return redirect()->route('vendor.profile.edit')
                ->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update vendor profile', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating your profile. Please try again.');
        }
    }

    private function updateVendorProfile($vendor, array $data): void
    {
        $fillableFields = ['name', 'email', 'phone'];

        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $vendor->{$field} = $data[$field];
            }
        }

        if (isset($data['password'])) {
            $vendor->password = Hash::make($data['password']);
        }

        if (request()->hasFile('avatar')) {
            $imageService = app(\App\Services\Admin\ImageService::class);
            $avatarPath = $imageService->uploadImage(request()->file('avatar'), 'vendors/avatar');
            $vendor->avatar = $avatarPath;
        }

        $vendor->save();
    }
}
