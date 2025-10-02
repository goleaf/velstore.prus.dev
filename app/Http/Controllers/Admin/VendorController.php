<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

class VendorController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Vendor::count(),
            'active' => Vendor::where('status', 'active')->count(),
            'inactive' => Vendor::where('status', 'inactive')->count(),
            'banned' => Vendor::where('status', 'banned')->count(),
        ];

        return view('admin.vendors.index', compact('stats'));
    }

    public function getVendorData()
    {
        $vendors = Vendor::select(['id', 'name', 'email', 'phone', 'status']);

        return DataTables::of($vendors)
            ->editColumn('status', function ($vendor) {
                $status = strtolower((string) $vendor->status);

                return match ($status) {
                    'active' => '<span class="badge badge-success">' . e(__('cms.vendors.status_active')) . '</span>',
                    'inactive' => '<span class="badge badge-warning">' . e(__('cms.vendors.status_inactive')) . '</span>',
                    'banned' => '<span class="badge badge-danger">' . e(__('cms.vendors.status_banned')) . '</span>',
                    default => '<span class="badge badge-secondary">' . e(__('cms.vendors.status_unknown')) . '</span>',
                };
            })
            ->addColumn('action', function ($vendor) {
                $deleteLabel = e(__('cms.vendors.delete_button'));

                return <<<HTML
                    <div class="flex flex-col gap-2">
                        <button type="button"
                                class="btn btn-outline-danger btn-sm w-full"
                                data-action="delete-vendor"
                                data-vendor-id="{$vendor->id}"
                                title="{$deleteLabel}">
                            {$deleteLabel}
                        </button>
                    </div>
                HTML;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:vendors,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->symbols(),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
            'status' => ['required', 'in:active,inactive,banned'],
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.regex' => 'Phone number can only contain numbers, spaces, dashes and optional +.',
        ]);

        Vendor::create([
            'name' => trim($validatedData['name']),
            'email' => strtolower(trim($validatedData['email'])),
            'password' => Hash::make($validatedData['password']),
            'phone' => $validatedData['phone'] ?? null,
            'status' => $validatedData['status'],
        ]);

        return redirect()->route('admin.vendors.index')
            ->with('success', __('cms.vendors.success_create'));
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => __('cms.vendors.success_delete'),
        ]);
    }
}
