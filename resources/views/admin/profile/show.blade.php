@extends('admin.layouts.admin')

@section('content')
<x-admin.page-header :title="__('cms.profile_page.title')" :description="__('cms.profile_page.subtitle')">
</x-admin.page-header>

<div class="grid gap-6 lg:grid-cols-3">
    <x-admin.card :title="__('cms.profile_page.account_overview')">
        <dl class="space-y-4 text-sm text-gray-700">
            <div class="flex justify-between gap-6">
                <dt class="text-gray-500">{{ __('cms.profile_page.full_name') }}</dt>
                <dd class="font-medium text-gray-900">{{ $profile['name'] ?: __('cms.profile_page.not_provided') }}</dd>
            </div>
            <div class="flex justify-between gap-6">
                <dt class="text-gray-500">{{ __('cms.profile_page.email') }}</dt>
                <dd class="font-medium text-gray-900">{{ $profile['email'] }}</dd>
            </div>
            <div class="flex justify-between gap-6">
                <dt class="text-gray-500">{{ __('cms.profile_page.joined_on') }}</dt>
                <dd class="font-medium text-gray-900">{{ optional($profile['created_at'])->timezone(config('app.timezone'))->format('M j, Y H:i') }}</dd>
            </div>
            <div class="flex justify-between gap-6">
                <dt class="text-gray-500">{{ __('cms.profile_page.last_updated') }}</dt>
                <dd class="font-medium text-gray-900">{{ optional($profile['updated_at'])->timezone(config('app.timezone'))->diffForHumans() }}</dd>
            </div>
        </dl>
    </x-admin.card>

    <x-admin.card :title="__('cms.profile_page.security_section')">
        <div class="space-y-4 text-sm text-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-900">{{ __('cms.profile_page.email_verified') }}</p>
                    <p class="text-xs text-gray-500">{{ __('cms.profile_page.email_verified_hint') }}</p>
                </div>
                <span class="badge {{ $security['email_verified_at'] ? 'badge-success' : 'badge-warning' }}">
                    {{ $security['email_verified_at'] ? __('cms.profile_page.status_verified') : __('cms.profile_page.status_pending') }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-900">{{ __('cms.profile_page.two_factor') }}</p>
                    <p class="text-xs text-gray-500">{{ __('cms.profile_page.two_factor_hint') }}</p>
                </div>
                <span class="badge {{ $security['two_factor_enabled'] ? 'badge-success' : 'badge-warning' }}">
                    {{ $security['two_factor_enabled'] ? __('cms.profile_page.status_enabled') : __('cms.profile_page.status_disabled') }}
                </span>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card :title="__('cms.profile_page.quick_actions')">
        <div class="space-y-3">
            <x-admin.button-link href="{{ route('admin.dashboard') }}" class="btn-outline w-full justify-start text-left">
                {{ __('cms.profile_page.back_to_dashboard') }}
            </x-admin.button-link>
            <button type="button" class="btn btn-outline w-full justify-start" disabled>
                {{ __('cms.profile_page.update_profile_placeholder') }}
            </button>
            <button type="button" class="btn btn-outline w-full justify-start" disabled>
                {{ __('cms.profile_page.change_password_placeholder') }}
            </button>
        </div>
    </x-admin.card>
</div>
@endsection
