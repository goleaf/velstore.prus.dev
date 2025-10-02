@extends('admin.layouts.admin')

@section('css')
@endsection

@section('content')
    @php
        $salesToday = $kpi['sales_today'] ?? 0;
        $salesYesterday = $kpi['sales_yesterday'] ?? 0;
        $dayDelta = $salesYesterday > 0 ? round((($salesToday - $salesYesterday) / $salesYesterday) * 100, 1) : null;

        $weeklyRevenue = $kpi['weekly_revenue'] ?? 0;
        $weeklyRevenueChange = $kpi['weekly_revenue_change'] ?? null;

        $openOrders = ($kpi['orders_pending'] ?? 0) + ($kpi['orders_processing'] ?? 0);
        $ordersTotal = $kpi['orders_total'] ?? 0;
        $completionRate = $kpi['orders_completion_rate'] ?? 0;

        $customersGrowth = $kpi['customers_growth'] ?? null;
        $customersMonth = $kpi['customers_month'] ?? 0;

        $statusMap = [
            'pending' => __('cms.dashboard.pending_orders'),
            'processing' => __('cms.dashboard.processing_orders'),
            'completed' => __('cms.dashboard.completed_orders'),
            'cancelled' => __('cms.dashboard.cancelled_orders'),
        ];

        $statusCounts = [];
        if (isset($orderStatusBreakdown)) {
            foreach ($orderStatusBreakdown as $status => $count) {
                $label = $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
                $statusCounts[$label] = $count;
            }
        } else {
            $statusCounts = [
                $statusMap['pending'] => $kpi['orders_pending'] ?? 0,
                $statusMap['processing'] => $kpi['orders_processing'] ?? 0,
                $statusMap['completed'] => $kpi['orders_completed'] ?? 0,
                $statusMap['cancelled'] => $kpi['orders_cancelled'] ?? 0,
            ];
        }
        $statusTotal = array_sum($statusCounts);
    @endphp
    @php
        $charts = $cardCharts ?? [];
    @endphp
    <div class="max-w-7xl mx-auto mt-4 px-4 space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('cms.dashboard.title') }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ __('cms.dashboard.overview_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.dashboard.daily_revenue') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($salesToday, 2) }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.daily_revenue_description') }}</p>
                <div class="mt-3 text-sm font-medium">
                    @if(!is_null($dayDelta))
                        @if($dayDelta > 0)
                            <span class="text-emerald-600"><i class="fas fa-arrow-up mr-1"></i>{{ __('cms.dashboard.change_positive', ['value' => number_format($dayDelta, 1)]) }}</span>
                        @elseif($dayDelta < 0)
                            <span class="text-rose-600"><i class="fas fa-arrow-down mr-1"></i>{{ __('cms.dashboard.change_negative', ['value' => number_format(abs($dayDelta), 1)]) }}</span>
                        @else
                            <span class="text-gray-500">{{ __('cms.dashboard.change_neutral') }}</span>
                        @endif
                    @else
                        <span class="text-gray-500">{{ __('cms.dashboard.yesterday_revenue') }}: {{ number_format($salesYesterday, 2) }}</span>
                    @endif
                </div>
                @if(!empty($charts['sales_today']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['sales_today'])'
                            aria-label="{{ $charts['sales_today']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.dashboard.weekly_revenue') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($weeklyRevenue, 2) }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-chart-line"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.weekly_revenue_description') }}</p>
                <div class="mt-3 text-sm font-medium">
                    @if(!is_null($weeklyRevenueChange))
                        @if($weeklyRevenueChange > 0)
                            <span class="text-emerald-600"><i class="fas fa-arrow-up mr-1"></i>{{ __('cms.dashboard.change_positive', ['value' => number_format($weeklyRevenueChange, 1)]) }}</span>
                        @elseif($weeklyRevenueChange < 0)
                            <span class="text-rose-600"><i class="fas fa-arrow-down mr-1"></i>{{ __('cms.dashboard.change_negative', ['value' => number_format(abs($weeklyRevenueChange), 1)]) }}</span>
                        @else
                            <span class="text-gray-500">{{ __('cms.dashboard.change_neutral') }}</span>
                        @endif
                    @else
                        <span class="text-gray-500">{{ __('cms.dashboard.weekly_revenue_change') }}</span>
                    @endif
                </div>
                @if(!empty($charts['weekly_revenue']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['weekly_revenue'])'
                            aria-label="{{ $charts['weekly_revenue']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.dashboard.net_revenue') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($kpi['net_revenue'] ?? 0, 2) }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                        <i class="fas fa-wallet"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.net_revenue_description') }}</p>
                <div class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.refunds') }}: {{ number_format($kpi['refunds_total'] ?? 0, 2) }} ({{ number_format($kpi['refund_rate'] ?? 0, 2) }}%)</div>
                @if(!empty($charts['net_revenue']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['net_revenue'])'
                            aria-label="{{ $charts['net_revenue']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.dashboard.average_order_value') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($kpi['aov'] ?? 0, 2) }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500">
                        <i class="fas fa-receipt"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.average_order_value_description') }}</p>
                @if(!empty($charts['aov']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['aov'])'
                            aria-label="{{ $charts['aov']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.dashboard.order_completion_rate') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($completionRate, 1) }}%</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-green-50 border border-green-100 flex items-center justify-center text-green-600">
                        <i class="fas fa-check-circle"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.order_completion_rate_description') }}</p>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.completed_orders') }}: {{ $kpi['orders_completed'] ?? 0 }} / {{ $ordersTotal }}</p>
                @if(!empty($charts['completion_rate']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['completion_rate'])'
                            aria-label="{{ $charts['completion_rate']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.dashboard.open_orders') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $openOrders }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-orange-50 border border-orange-100 flex items-center justify-center text-orange-500">
                        <i class="fas fa-box"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.open_orders_description') }}</p>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.pending_orders') }}: {{ $kpi['orders_pending'] ?? 0 }} · {{ __('cms.dashboard.processing_orders') }}: {{ $kpi['orders_processing'] ?? 0 }} · {{ __('cms.dashboard.cancelled_orders') }}: {{ $kpi['orders_cancelled'] ?? 0 }}</p>
                @if(!empty($charts['open_orders']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['open_orders'])'
                            aria-label="{{ $charts['open_orders']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.dashboard.customers') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $kpi['customers_total'] ?? 0 }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-500">
                        <i class="fas fa-users"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.customers_description') }}</p>
                <div class="mt-3 text-sm font-medium">
                    <span class="text-gray-700">{{ __('cms.dashboard.new_customers') }}: {{ $customersMonth }}</span>
                    <div class="mt-1">
                        @if(!is_null($customersGrowth))
                            @if($customersGrowth > 0)
                                <span class="text-emerald-600"><i class="fas fa-arrow-up mr-1"></i>{{ __('cms.dashboard.change_positive', ['value' => number_format($customersGrowth, 1)]) }}</span>
                            @elseif($customersGrowth < 0)
                                <span class="text-rose-600"><i class="fas fa-arrow-down mr-1"></i>{{ __('cms.dashboard.change_negative', ['value' => number_format(abs($customersGrowth), 1)]) }}</span>
                            @else
                                <span class="text-gray-500">{{ __('cms.dashboard.change_neutral') }}</span>
                            @endif
                        @else
                            <span class="text-gray-500">{{ __('cms.dashboard.customers_growth') }}</span>
                        @endif
                    </div>
                </div>
                @if(!empty($charts['customers']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['customers'])'
                            aria-label="{{ $charts['customers']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('cms.vendors.title_list') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $kpi['vendors_total'] ?? 0 }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500">
                        <i class="fas fa-store"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ __('cms.dashboard.vendors_description') }}</p>
                @if(!empty($charts['vendors']['values'] ?? []))
                    <div class="mt-5 h-16">
                        <canvas
                            class="w-full h-full"
                            data-chart='@json($charts['vendors'])'
                            aria-label="{{ $charts['vendors']['label'] ?? '' }}"
                            role="img"
                        ></canvas>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('cms.dashboard.revenue_trend') }}</h3>
                    <span class="text-sm text-gray-500">{{ __('cms.dashboard.weekly_revenue_description') }}</span>
                </div>
                @if(isset($revenueTrend) && $revenueTrend->count())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($revenueTrend as $point)
                            <li class="py-3 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700">{{ \\Carbon\\Carbon::parse($point['date'])->format('M d, Y') }}</span>
                                <span class="text-gray-900 font-semibold">{{ number_format($point['amount'], 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">{{ __('cms.dashboard.no_data') }}</p>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('cms.dashboard.order_status_breakdown') }}</h3>
                    <span class="text-sm text-gray-500">{{ __('cms.dashboard.open_orders_total') }}: {{ $ordersTotal }}</span>
                </div>
                @if($statusTotal > 0)
                    <div class="space-y-4">
                        @foreach ($statusCounts as $label => $count)
                            @php
                                $percentage = $statusTotal > 0 ? round(($count / $statusTotal) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span class="font-medium text-gray-700">{{ $label }}</span>
                                    <span>{{ $count }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{ __('cms.dashboard.no_data') }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('cms.dashboard.top_products') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('cms.dashboard.top_products_description') }}</p>
                    </div>
                    <span class="text-sm text-gray-500">{{ __('cms.dashboard.reviews') }}</span>
                </div>
                @if(isset($topProducts) && $topProducts->count())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($topProducts as $product)
                            <li class="py-4 text-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ optional($product->translation)->name ?? $product->slug }}</p>
                                        <p class="text-xs text-gray-500">{{ __('cms.dashboard.unit_price') }}: {{ number_format($product->price, 2) }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('cms.dashboard.top_products_stock') }}: {{ number_format($product->stock ?? 0) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">{{ $product->reviews_count }}</p>
                                        <p class="text-xs text-gray-500">{{ __('cms.dashboard.reviews') }}</p>
                                    </div>
                                </div>
                                <dl class="mt-3 grid grid-cols-2 gap-3 text-xs text-gray-600">
                                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                                        <dt class="font-medium text-gray-500">{{ __('cms.dashboard.top_products_units_sold') }}</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ number_format($product->units_sold ?? 0) }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                                        <dt class="font-medium text-gray-500">{{ __('cms.dashboard.top_products_revenue') }}</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ number_format($product->revenue_generated ?? 0, 2) }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                                        <dt class="font-medium text-gray-500">{{ __('cms.dashboard.top_products_rating') }}</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ number_format($product->average_rating ?? 0, 1) }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                                        <dt class="font-medium text-gray-500">{{ __('cms.dashboard.top_products_conversion') }}</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ number_format($product->reviews_count > 0 ? ($product->units_sold ? ($product->reviews_count / max($product->units_sold, 1) * 100) : 0) : 0, 1) }}%</dd>
                                    </div>
                                </dl>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">{{ __('cms.dashboard.no_data') }}</p>
                @endif
            </div>

            <div class="p-5 rounded-2xl border border-gray-200 bg-white shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('cms.dashboard.insights') }}</h3>
                <ul class="space-y-3 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-emerald-500"><i class="fas fa-circle"></i></span>
                        <span>{{ __('cms.dashboard.insight_completion', ['value' => number_format($completionRate, 1), 'total' => $ordersTotal]) }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-blue-500"><i class="fas fa-circle"></i></span>
                        @if(!is_null($weeklyRevenueChange))
                            @php
                                $direction = $weeklyRevenueChange > 0 ? __('cms.dashboard.direction_up') : ($weeklyRevenueChange < 0 ? __('cms.dashboard.direction_down') : __('cms.dashboard.direction_flat'));
                            @endphp
                            <span>{{ __('cms.dashboard.insight_weekly_revenue', ['direction' => $direction, 'value' => number_format(abs($weeklyRevenueChange), 1)]) }}</span>
                        @else
                            <span>{{ __('cms.dashboard.weekly_revenue_description') }}</span>
                        @endif
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500"><i class="fas fa-circle"></i></span>
                        <span>{{ __('cms.dashboard.insight_refund_rate', ['value' => number_format($kpi['refund_rate'] ?? 0, 2)]) }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-indigo-500"><i class="fas fa-circle"></i></span>
                        @if(!is_null($customersGrowth))
                            <span>{{ __('cms.dashboard.insight_customers', ['value' => number_format(abs($customersGrowth), 1)]) }}</span>
                        @else
                            <span>{{ __('cms.dashboard.customers_growth') }}</span>
                        @endif
                    </li>
                    @foreach(($productInsights ?? []) as $insight)
                        <li class="flex items-start gap-2">
                            <span class="mt-1 text-rose-500"><i class="fas fa-circle"></i></span>
                            <span>{{ $insight }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined') {
                return;
            }

            document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
                const dataset = (() => {
                    try {
                        return JSON.parse(canvas.dataset.chart || '{}');
                    } catch (error) {
                        console.warn('Unable to parse chart data', error);
                        return null;
                    }
                })();

                if (!dataset || !Array.isArray(dataset.values) || dataset.values.length === 0) {
                    return;
                }

                const labels = dataset.labels || [];
                const values = dataset.values || [];
                const color = dataset.color || '#2563eb';
                const background = dataset.background || 'rgba(37, 99, 235, 0.12)';

                new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                data: values,
                                borderColor: color,
                                backgroundColor: background,
                                borderWidth: 2,
                                tension: 0.45,
                                fill: true,
                                pointRadius: 0,
                                pointHoverRadius: 3,
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                padding: 8,
                                displayColors: false,
                                callbacks: {
                                    label: (context) => {
                                        const suffix = dataset.suffix || '';
                                        return `${context.formattedValue}${suffix}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                display: false,
                            },
                            y: {
                                display: false,
                            },
                        },
                    },
                });
            });
        });
    </script>
@endpush
