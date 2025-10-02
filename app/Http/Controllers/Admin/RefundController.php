<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RefundController extends Controller
{
    public function index()
    {
        return view('admin.refunds.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $refunds = Refund::with('payment')->select('refunds.*');

            return DataTables::of($refunds)
                ->addColumn('payment', fn ($row) => $row->payment ? 'Payment #'.$row->payment->id : '—')
                ->addColumn('action', function ($row) {
                    $showRoute = route('admin.refunds.show', $row->id);

                    return '<div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-view-refund" data-url="'.$showRoute.'">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-refund" data-id="'.$row->id.'">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>';
                })
                ->make(true);
        }
    }

    public function show($id)
    {
        $refund = Refund::with('payment.user', 'payment.order', 'payment.gateway')->findOrFail($id);

        return view('admin.refunds.show', compact('refund'));
    }

    public function destroy(Refund $refund)
    {
        $refund->delete();

        return response()->json(['success' => true, 'message' => 'Refund deleted successfully.']);
    }
}
