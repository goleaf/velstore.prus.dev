@extends('admin.layouts.admin')

@php
    $statusKey = $review->status_label;
    $statusClass = $review->status_badge_class;
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.product_reviews.edit_title', ['id' => $review->id])"
    >
        <x-admin.button-link href="{{ route('admin.reviews.index') }}" class="btn-outline">
            {{ __('cms.product_reviews.back_to_list') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <div class="grid gap-6">
        <x-admin.card :title="__('cms.product_reviews.details_title')" :noMargin="true">
            <dl class="grid gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.customer_name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $review->customer_display_name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.product_name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $review->product_display_name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.status') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="{{ $statusClass }}">{{ __('cms.product_reviews.' . $statusKey) }}</span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.rating') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ number_format($review->rating, 1) }}
                        <span class="text-gray-500">/ 5</span>
                    </dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card :title="__('cms.product_reviews.edit_form_title')">
            <p class="text-sm text-gray-600">{{ __('cms.product_reviews.edit_description') }}</p>

            <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="rating" class="form-label">{{ __('cms.product_reviews.rating') }}</label>
                        <input
                            type="number"
                            name="rating"
                            id="rating"
                            min="1"
                            max="5"
                            step="1"
                            value="{{ old('rating', $review->rating) }}"
                            class="form-input @error('rating') border-danger-500 focus:border-danger-500 focus:ring-danger-500 @enderror"
                            required
                        >
                        @error('rating')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_approved" class="form-label">{{ __('cms.product_reviews.status') }}</label>
                        <select
                            name="is_approved"
                            id="is_approved"
                            class="form-select @error('is_approved') border-danger-500 focus:border-danger-500 focus:ring-danger-500 @enderror"
                        >
                            <option value="1" {{ old('is_approved', $review->is_approved) ? 'selected' : '' }}>
                                {{ __('cms.product_reviews.approved') }}
                            </option>
                            <option value="0" {{ ! old('is_approved', $review->is_approved) ? 'selected' : '' }}>
                                {{ __('cms.product_reviews.pending') }}
                            </option>
                        </select>
                        @error('is_approved')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="review" class="form-label">{{ __('cms.product_reviews.review') }}</label>
                    <textarea
                        name="review"
                        id="review"
                        rows="5"
                        class="form-textarea @error('review') border-danger-500 focus:border-danger-500 focus:ring-danger-500 @enderror"
                    >{{ old('review', $review->review) }}</textarea>
                    @error('review')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="btn btn-primary">
                        {{ __('cms.product_reviews.update_button') }}
                    </button>
                    <x-admin.button-link href="{{ route('admin.reviews.show', $review) }}" class="btn-outline">
                        {{ __('cms.product_reviews.cancel') }}
                    </x-admin.button-link>
                </div>
            </form>
        </x-admin.card>
    </div>
@endsection
