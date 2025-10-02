<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        return view('admin.payment_gateways.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $gateways = PaymentGateway::select('payment_gateways.*');

            return DataTables::of($gateways)
                ->addColumn('status', fn ($row) => $row->is_active ? '<span class="badge badge-success">'.e(__('cms.payment_gateways.active')).'</span>' : '<span class="badge badge-danger">'.e(__('cms.payment_gateways.inactive')).'</span>')
                ->addColumn('action', function ($row) {
                    $editRoute = route('admin.payment-gateways.edit', $row->id);
                    $editLabel = e(__('cms.payment_gateways.edit_title'));
                    $deleteLabel = e(__('cms.payment_gateways.delete'));

                    return <<<HTML
                        <div class="flex flex-col gap-2">
                            <button type="button"
                                    class="btn btn-outline btn-sm w-full btn-edit-gateway"
                                    data-url="{$editRoute}" title="{$editLabel}">
                                {$editLabel}
                            </button>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm w-full btn-delete-gateway"
                                    data-id="{$row->id}" title="{$deleteLabel}">
                                {$deleteLabel}
                            </button>
                        </div>
                    HTML;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function edit($id)
    {
        $paymentGateway = PaymentGateway::with('configs')->findOrFail($id);

        return view('admin.payment_gateways.edit', compact('paymentGateway'));
    }

    public function update(Request $request, $id)
    {

        $gateway = PaymentGateway::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_gateways,code,'.$gateway->id,
            'description' => 'nullable|string',
            'configs.*.key_name' => 'sometimes|required|string|max:100',
            'configs.*.key_value' => 'sometimes|required|string',
            'configs.*.environment' => 'sometimes|required|string',
        ]);

        $data = $request->only(['name', 'code', 'description']);
        $data['is_active'] = $request->has('is_active');
        $gateway->update($data);

        if ($request->has('configs')) {
            foreach ($request->configs as $configId => $configData) {
                $config = $gateway->configs()->find($configId);

                if ($config) {
                    $config->update([
                        'key_name' => $configData['key_name'],
                        'key_value' => $configData['key_value'],
                        'is_encrypted' => isset($configData['is_encrypted']),
                        'environment' => $configData['environment'],
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.payment-gateways.index')
            ->with('success', 'Payment Gateway updated successfully.');
    }

    public function destroy(PaymentGateway $paymentGateway)
    {
        $paymentGateway->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment Gateway deleted successfully.',
        ]);
    }
}
