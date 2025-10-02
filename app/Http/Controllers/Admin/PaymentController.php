<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $payments = Payment::with(['user', 'order', 'gateway'])->select('payments.*');

            return DataTables::of($payments)
                ->addColumn('user', fn ($row) => $row->user?->name ?? '—')
                ->addColumn('order', fn ($row) => $row->order ? 'Order #'.$row->order->id : '—')
                ->addColumn('gateway', fn ($row) => $row->gateway?->name ?? '—')
                ->addColumn('action', function ($row) {
                    $showRoute = route('admin.payments.show', $row->id);

                    return '<div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-view-payment" data-url="'.$showRoute.'">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-payment" data-id="'.$row->id.'">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function show($id)
    {
        $payment = Payment::with(['user', 'order', 'gateway'])->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['success' => true, 'message' => 'Payment deleted successfully.']);
    }
}
