@php
    use Illuminate\Support\Facades\Route;

    $menuOpen = [
        'products' => Route::is('admin.products.*'),
        'productVariants' => Route::is('admin.product_variants.*'),
        'categories' => Route::is('admin.categories.*'),
        'brands' => Route::is('admin.brands.*'),
        'attributes' => Route::is('admin.attributes.*') || Route::is('admin.values.*') || Route::is('admin.values.translations.*'),
        'coupons' => Route::is('admin.coupons.*'),
        'menus' => Route::is('admin.menus.*') || Route::is('admin.menus.items.*') || Route::is('admin.menus.item.*'),
        'banners' => Route::is('admin.banners.*'),
        'social' => Route::is('admin.social-media-links.*'),
        'orders' => Route::is('admin.orders.*'),
        'customers' => Route::is('admin.customers.*'),
        'vendors' => Route::is('admin.vendors.*'),
        'pages' => Route::is('admin.pages.*'),
        'payments' => Route::is('admin.payments.*'),
        'refunds' => Route::is('admin.refunds.*'),
        'paymentGateways' => Route::is('admin.payment-gateways.*'),
        'reviews' => Route::is('admin.reviews.*'),
    ];
@endphp

<div class="sidebar">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200 flex-shrink-0">
        <img src="{{ asset('storage/brands/logo-ready.png') }}" alt="{{ __('cms.sidebar.logo') }}" class="h-8 w-auto">
    </div>

    <!-- Search -->
    <div class="px-4 py-4 border-b border-gray-200 flex-shrink-0">
        <div class="relative">
            <input type="text"
                   class="form-input pl-10"
                   placeholder="{{ __('cms.sidebar.search_placeholder') }}"
                   id="searchInput"
                   autocomplete="off">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav thin-scrollbar">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-nav-item {{ Route::is('admin.dashboard') ? 'sidebar-nav-item-active' : 'sidebar-nav-item-inactive' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 5a2 2 0 012-2h4a2 2 0 012 2v4H8V5z"></path>
            </svg>
            {{ __('cms.sidebar.dashboard') }}
        </a>

        <!-- Products -->
        <div x-data="{ open: {{ $menuOpen['products'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    {{ __('cms.sidebar.products.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.products.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.products.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.products.add_new') }}
                </a>
                <a href="{{ route('admin.products.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.products.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.products.list') }}
                </a>
            </div>
        </div>

        <!-- Product Variants -->
        <div x-data="{ open: {{ $menuOpen['productVariants'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M10 14h10M10 18h10M4 14h2m-2 4h2"></path>
                    </svg>
                    {{ __('cms.sidebar.product_variants.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.product_variants.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.product_variants.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.product_variants.add_new') }}
                </a>
                <a href="{{ route('admin.product_variants.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.product_variants.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.product_variants.list') }}
                </a>
            </div>
        </div>

        <!-- Categories -->
        <div x-data="{ open: {{ $menuOpen['categories'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    {{ __('cms.sidebar.categories.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.categories.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.categories.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.categories.add_new') }}
                </a>
                <a href="{{ route('admin.categories.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.categories.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.categories.list') }}
                </a>
            </div>
        </div>

        <!-- Brands -->
        <div x-data="{ open: {{ $menuOpen['brands'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ __('cms.sidebar.brands.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.brands.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.brands.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.brands.add_new') }}
                </a>
                <a href="{{ route('admin.brands.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.brands.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.brands.list') }}
                </a>
            </div>
        </div>

        <!-- Attributes -->
        <div x-data="{ open: {{ $menuOpen['attributes'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h10M4 18h10"></path>
                    </svg>
                    {{ __('cms.sidebar.attributes.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.attributes.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.attributes.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.attributes.add_new') }}
                </a>
                <a href="{{ route('admin.attributes.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.attributes.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.attributes.list') }}
                </a>
            </div>
        </div>

        <!-- Coupons -->
        <div x-data="{ open: {{ $menuOpen['coupons'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 14l6-6m0 0l-3-3m3 3l3 3M6 9l-3 3m0 0l3 3m-3-3h12"></path>
                    </svg>
                    {{ __('cms.sidebar.coupons.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.coupons.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.coupons.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.coupons.add_new') }}
                </a>
                <a href="{{ route('admin.coupons.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.coupons.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.coupons.list') }}
                </a>
            </div>
        </div>

        <!-- Banners -->
        <div x-data="{ open: {{ $menuOpen['banners'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v13a1 1 0 01-1.514.858L12 14l-7.486 3.858A1 1 0 013 17V4z"></path>
                    </svg>
                    {{ __('cms.sidebar.banners.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.banners.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.banners.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.banners.add_new') }}
                </a>
                <a href="{{ route('admin.banners.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.banners.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.banners.list') }}
                </a>
            </div>
        </div>

        <!-- Menus -->
        <div x-data="{ open: {{ $menuOpen['menus'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    {{ __('cms.sidebar.menu.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.menus.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.menus.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.menu.add_new') }}
                </a>
                <a href="{{ route('admin.menus.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.menus.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.menu.list') }}
                </a>
                <a href="{{ route('admin.menus.item.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.menus.item.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.menu_items.list') }}
                </a>
            </div>
        </div>

        <!-- Social Media Links -->
        <div x-data="{ open: {{ $menuOpen['social'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18 2h-3a2 2 0 00-2 2v3H9v4h4v7h4v-7h3l1-4h-4V4a1 1 0 011-1h3z"></path>
                    </svg>
                    {{ __('cms.sidebar.social_media_links.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.social-media-links.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.social-media-links.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.social_media_links.add_new') }}
                </a>
                <a href="{{ route('admin.social-media-links.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.social-media-links.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.social_media_links.list') }}
                </a>
            </div>
        </div>

        <!-- Orders -->
        <div x-data="{ open: {{ $menuOpen['orders'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h18M9 3v18m6-18v18M4 7h16M4 11h16M4 15h16M4 19h16"></path>
                    </svg>
                    {{ __('cms.sidebar.orders.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.orders.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.orders.index') && !request('status') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.orders.all_orders') }}
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.orders.index') && request('status') === 'pending' ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.orders.pending_orders') }}
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.orders.index') && request('status') === 'completed' ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.orders.completed_orders') }}
                </a>
            </div>
        </div>

        <!-- Customers -->
        <div x-data="{ open: {{ $menuOpen['customers'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"></path>
                    </svg>
                    {{ __('cms.sidebar.customers.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.customers.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.customers.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.customers.add_new') }}
                </a>
                <a href="{{ route('admin.customers.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.customers.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.customers.list') }}
                </a>
            </div>
        </div>

        <!-- Vendors -->
        <div x-data="{ open: {{ $menuOpen['vendors'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 8h10M7 12h5m-5 4h8M5 6a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6z"></path>
                    </svg>
                    {{ __('cms.sidebar.vendors.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.vendors.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.vendors.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.vendors.add_new') }}
                </a>
                <a href="{{ route('admin.vendors.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.vendors.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.vendors.list') }}
                </a>
            </div>
        </div>

        <!-- Pages -->
        <div x-data="{ open: {{ $menuOpen['pages'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 20l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 12l9-5-9-5-9 5 9 5z"></path>
                    </svg>
                    {{ __('cms.sidebar.pages.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.pages.create') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.pages.create') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.pages.add_new') }}
                </a>
                <a href="{{ route('admin.pages.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.pages.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.pages.list') }}
                </a>
            </div>
        </div>

        <!-- Payments -->
        <div x-data="{ open: {{ $menuOpen['payments'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a5 5 0 00-10 0v2a3 3 0 00-3 3v5a3 3 0 003 3h10a3 3 0 003-3v-5a3 3 0 00-3-3z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 13h6"></path>
                    </svg>
                    {{ __('cms.sidebar.payments.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.payments.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.payments.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.payments.list') }}
                </a>
            </div>
        </div>

        <!-- Refunds -->
        <div x-data="{ open: {{ $menuOpen['refunds'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0014-7V9a1 1 0 012 0v3a11 11 0 01-20 7"></path>
                    </svg>
                    {{ __('cms.sidebar.refunds.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.refunds.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.refunds.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.refunds.list') }}
                </a>
            </div>
        </div>

        <!-- Payment Gateways -->
        <div x-data="{ open: {{ $menuOpen['paymentGateways'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 14l6-6m-6 0l6 6M3 7h18v10H3z"></path>
                    </svg>
                    {{ __('cms.sidebar.payment_gateways.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.payment-gateways.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.payment-gateways.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.payment_gateways.list') }}
                </a>
            </div>
        </div>

        <!-- Product Reviews -->
        <div x-data="{ open: {{ $menuOpen['reviews'] ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open"
                    class="sidebar-nav-item sidebar-nav-item-inactive w-full flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.959a1 1 0 00.95.69h4.163c.969 0 1.371 1.24.588 1.81l-3.37 2.449a1 1 0 00-.364 1.118l1.287 3.96c.3.92-.755 1.688-1.54 1.118l-3.371-2.45a1 1 0 00-1.175 0l-3.37 2.45c-.786.57-1.84-.197-1.54-1.118l1.287-3.96a1 1 0 00-.364-1.118L2.07 9.386c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.959z"></path>
                    </svg>
                    {{ __('cms.sidebar.product_reviews.title') }}
                </div>
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition x-cloak class="sidebar-nav-submenu">
                <a href="{{ route('admin.reviews.index') }}"
                   class="sidebar-nav-submenu-item {{ Route::is('admin.reviews.index') ? 'sidebar-nav-submenu-item-active' : '' }}">
                    {{ __('cms.sidebar.product_reviews.list') }}
                </a>
            </div>
        </div>

        <!-- Site Settings -->
        <a href="{{ route('admin.site-settings.index') }}"
           class="mt-2 sidebar-nav-item {{ Route::is('admin.site-settings.*') ? 'sidebar-nav-item-active' : 'sidebar-nav-item-inactive' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            {{ __('cms.sidebar.site_settings.title') }}
        </a>
    </nav>
</div>
