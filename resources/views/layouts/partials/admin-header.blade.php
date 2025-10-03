<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-4">
        <!-- Mobile menu button -->
        <button type="button"
                class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                x-data @click="$dispatch('toggle-sidebar')">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Page title -->
        <div class="flex-1">
            <h1 class="text-2xl font-semibold text-gray-900">
                @yield('page-title', trans_json('messages.dashboard'))
            </h1>
        </div>

        <!-- Right side actions -->
        <div class="flex items-center space-x-4">
            <!-- Language selector -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <img src="{{ get_flag_url(app()->getLocale()) }}" width="20" height="15"
                         alt="{{ get_language_name(app()->getLocale()) }}">
                    <span>{{ get_language_name(app()->getLocale()) }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                    <div class="py-1">
                        @foreach (get_languages() as $language)
                            <a href="#"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 language-select {{ app()->getLocale() == $language->code ? 'bg-primary-50 text-primary-700' : '' }}"
                               data-lang="{{ $language->code }}">
                                <img src="{{ get_flag_url($language->code) }}" width="20" height="15"
                                     class="mr-3">
                                {{ $language->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Profile dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center space-x-3 p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <img src="/assets/images/logo-main.svg" class="h-8 w-8 rounded-full" alt="Profile">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                    <div class="py-1">
                        <a href="{{ route('admin.profile.show') }}"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ trans_json('profile.profile') }}</a>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ trans_json('profile.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
