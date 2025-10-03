<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Removed auth middleware - no user authentication required
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, DashboardService $dashboardService)
    {
        $shopId = $request->integer('shop_id');
        $dashboard = $dashboardService->generate($shopId);

        $shops = Shop::query()
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        return view('admin.home', array_merge($dashboard, [
            'shops' => $shops,
        ]));
    }
}
