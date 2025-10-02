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
                ->addColumn('payment', fn($row) => $row->payment ? 'Payment #' . $row->payment->id : '—')
                ->addColumn('action', function ($row) {
                    $showRoute = route('admin.refunds.show', $row->id);
                    $viewLabel = e(__('cms.messages.view_details'));
                    $deleteLabel = e(__('cms.refunds.delete'));

                    return <<<HTML
                        <div class="flex flex-col gap-2">
                            <button type="button"
                                    class="btn btn-outline btn-sm w-full btn-view-refund"
                                    data-url="{$showRoute}" title="{$viewLabel}">
                                {$viewLabel}
                            </button>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm w-full btn-delete-refund"
                                    data-id="{$row->id}" title="{$deleteLabel}">
                                {$deleteLabel}
                            </button>
                        </div>
                    HTML;
                })
                ->make(true);
        }
    }

    public function show($id)
    {
        $refund = Refund::with('payment.order', 'payment.gateway')->findOrFail($id);

        return view('admin.refunds.show', compact('refund'));
    }

    public function destroy(Refund $refund)
    {
        $refund->delete();

        return response()->json(['success' => true, 'message' => 'Refund deleted successfully.']);
    }
}
