@php
    use Illuminate\Support\Carbon;

    $couponModel = $coupon ?? null;
    $action = $formAction ?? ($action ?? route('admin.coupons.store'));
    $formMethod = strtoupper($formMethod ?? ($method ?? 'POST'));
    $submitLabel = $submitLabel ?? __('cms.coupons.save');
    $cancelUrl = $cancelUrl ?? route('admin.coupons.index');

    $codeValue = old('code', $couponModel->code ?? '');
    $discountValue = old('discount', $couponModel->discount ?? '');
    $minimumSpendValue = old('minimum_spend', $couponModel->minimum_spend ?? '');
    $usageLimitValue = old('usage_limit', $couponModel->usage_limit ?? '');
    $typeValue = old('type', $couponModel->type ?? 'percentage');

    if (! in_array($typeValue, ['percentage', 'fixed'], true)) {
        $typeValue = 'percentage';
    }

    $timezone = config('app.timezone', 'UTC');
    $now = Carbon::now($timezone);
    $oldInput = session()->getOldInput() ?? [];
    $hasOldExpiresInput = array_key_exists('expires_at', $oldInput);
    $rawExpiresAt = $hasOldExpiresInput ? $oldInput['expires_at'] : null;

    $formatDateTime = static function ($value, $tz) {
        if (blank($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->timezone($tz)->format('Y-m-d\\TH:i');
        } catch (\Throwable) {
            return '';
        }
    };

    if ($hasOldExpiresInput) {
        $expiresAtValue = $formatDateTime($rawExpiresAt, $timezone);
        $shouldShowExpiry = filled($rawExpiresAt);
    } elseif ($couponModel && $couponModel->expires_at) {
        $expiresAtValue = $couponModel->expires_at->timezone($timezone)->format('Y-m-d\\TH:i');
        $shouldShowExpiry = true;
    } else {
        $expiresAtValue = '';
        $shouldShowExpiry = false;
    }

    $templatePresets = [
        [
            'key' => 'flash_sale',
            'label' => __('cms.coupons.templates.flash_sale.label'),
            'description' => __('cms.coupons.templates.flash_sale.description'),
            'code' => 'FLASH24',
            'type' => 'percentage',
            'discount' => 30,
            'minimum_spend' => 75,
            'usage_limit' => 100,
            'expires_at' => $formatDateTime($now->copy()->addDay(), $timezone),
        ],
        [
            'key' => 'loyalty',
            'label' => __('cms.coupons.templates.loyalty.label'),
            'description' => __('cms.coupons.templates.loyalty.description'),
            'code' => 'LOYALTY15',
            'type' => 'percentage',
            'discount' => 15,
            'minimum_spend' => 200,
            'usage_limit' => null,
            'expires_at' => null,
        ],
        [
            'key' => 'shipping',
            'label' => __('cms.coupons.templates.shipping.label'),
            'description' => __('cms.coupons.templates.shipping.description'),
            'code' => 'SHIPFREE',
            'type' => 'fixed',
            'discount' => 12,
            'minimum_spend' => 60,
            'usage_limit' => null,
            'expires_at' => $formatDateTime($now->copy()->addWeeks(2), $timezone),
        ],
    ];
@endphp

<p class="mb-6 text-sm text-gray-500">{{ __('cms.coupons.form_description') }}</p>

@if ($errors->any())
    <div class="mb-6 rounded-md border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700">
        <p class="font-semibold">{{ __('cms.notifications.validation_error') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    action="{{ $action }}"
    method="POST"
    class="space-y-8"
    novalidate
    x-data="{
        type: @js($typeValue),
        showExpiry: @js($shouldShowExpiry),
        form: {
            code: @js($codeValue),
            discount: @js($discountValue),
            minimumSpend: @js($minimumSpendValue),
            usageLimit: @js($usageLimitValue),
            expiresAt: @js($expiresAtValue),
        },
        templates: @js($templatePresets),
        init() {
            if (this.form.expiresAt && this.$refs.expiresInput) {
                this.$refs.expiresInput.value = this.form.expiresAt;
            }

            this.$watch('showExpiry', (value) => {
                if (!value) {
                    this.form.expiresAt = '';
                    if (this.$refs.expiresInput) {
                        this.$refs.expiresInput.value = '';
                    }
                }
            });
        },
        generateCode() {
            const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let generated = '';
            for (let index = 0; index < 10; index++) {
                generated += characters[Math.floor(Math.random() * characters.length)];
            }

            this.form.code = generated;

            if (this.$refs.codeInput) {
                this.$refs.codeInput.value = generated;
                this.$refs.codeInput.focus();
            }
        },
        applyTemplate(template) {
            this.type = template.type;
            this.form.code = template.code;
            this.form.discount = template.discount;
            this.form.minimumSpend = template.minimum_spend ?? '';
            this.form.usageLimit = template.usage_limit ?? '';
            this.showExpiry = Boolean(template.expires_at);
            this.form.expiresAt = template.expires_at ?? '';

            queueMicrotask(() => {
                if (this.$refs.codeInput) {
                    this.$refs.codeInput.value = this.form.code;
                    this.$refs.codeInput.focus();
                }

                if (this.$refs.expiresInput) {
                    this.$refs.expiresInput.value = this.form.expiresAt;
                }
            });
        },
        get previewDiscountLabel() {
            const value = parseFloat(this.form.discount ?? '');

            if (Number.isNaN(value)) {
                return '{{ __('cms.coupons.preview.no_discount') }}';
            }

            if (this.type === 'percentage') {
                return `${value}% {{ __('cms.coupons.preview.off_label') }}`;
            }

            return `{{ __('cms.coupons.preview.currency_prefix') }}${value.toFixed(2)} {{ __('cms.coupons.preview.off_label') }}`;
        },
        get minimumSpendLabel() {
            if (!this.form.minimumSpend) {
                return '{{ __('cms.coupons.preview.no_minimum_spend') }}';
            }

            return `{{ __('cms.coupons.preview.minimum_prefix') }}${Number(this.form.minimumSpend).toFixed(2)}`;
        },
        get usageLimitLabel() {
            if (!this.form.usageLimit) {
                return '{{ __('cms.coupons.preview.unlimited_usage') }}';
            }

            return `{{ __('cms.coupons.preview.usage_prefix') }}${this.form.usageLimit}`;
        },
        get expiresAtLabel() {
            if (!this.showExpiry || !this.form.expiresAt) {
                return '{{ __('cms.coupons.preview.no_expiry') }}';
            }

            try {
                return `{{ __('cms.coupons.preview.expires_prefix') }}${new Date(this.form.expiresAt).toLocaleString()}`;
            } catch (error) {
                return '{{ __('cms.coupons.preview.no_expiry') }}';
            }
        },
        get discountWarning() {
            if (this.type !== 'percentage') {
                return null;
            }

            const value = parseFloat(this.form.discount ?? '');

            if (Number.isNaN(value) || value < 80) {
                return null;
            }

            return '{{ __('cms.coupons.preview.warning_high_percentage') }}';
        },
    }"
>
    @csrf
    @if (! in_array($formMethod, ['GET', 'POST']))
        @method($formMethod)
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div class="space-y-8">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="code" class="form-label">{{ __('cms.coupons.code') }}</label>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input
                            type="text"
                            name="code"
                            id="code"
                            x-ref="codeInput"
                            value="{{ $codeValue }}"
                            x-model="form.code"
                            class="form-control @error('code') is-invalid @enderror"
                            maxlength="255"
                            required
                        >
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm whitespace-nowrap"
                            @click.prevent="generateCode()"
                        >
                            {{ __('cms.coupons.generate_code') }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">{{ __('cms.coupons.generate_code_hint') }}</p>
                    @error('code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="type" class="form-label">{{ __('cms.coupons.type') }}</label>
                    <select
                        name="type"
                        id="type"
                        class="form-select @error('type') is-invalid @enderror"
                        required
                        x-model="type"
                    >
                        <option value="percentage" @selected($typeValue === 'percentage')>
                            {{ __('cms.coupons.type_labels.percentage') }}
                        </option>
                        <option value="fixed" @selected($typeValue === 'fixed')>
                            {{ __('cms.coupons.type_labels.fixed') }}
                        </option>
                    </select>
                    <p class="text-xs text-gray-500" x-show="type === 'percentage'" x-cloak>
                        {{ __('cms.coupons.discount_hint_percentage') }}
                    </p>
                    <p class="text-xs text-gray-500" x-show="type === 'fixed'" x-cloak>
                        {{ __('cms.coupons.discount_hint_fixed') }}
                    </p>
                    @error('type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="space-y-2">
                <label for="discount" class="form-label">{{ __('cms.coupons.discount') }}</label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input
                        type="number"
                        name="discount"
                        id="discount"
                        class="form-control @error('discount') is-invalid @enderror"
                        value="{{ $discountValue }}"
                        x-model="form.discount"
                        step="0.01"
                        min="0"
                        required
                    >
                    <span
                        class="inline-flex h-10 items-center rounded-md border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-600"
                        x-text="type === 'percentage' ? '%' : @js(__('cms.coupons.discount_fixed_suffix'))"
                    ></span>
                </div>
                <p class="text-xs text-gray-500">{{ __('cms.coupons.discount_hint') }}</p>
                @error('discount')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="minimum_spend" class="form-label">{{ __('cms.coupons.minimum_spend') }}</label>
                    <input
                        type="number"
                        name="minimum_spend"
                        id="minimum_spend"
                        class="form-control @error('minimum_spend') is-invalid @enderror"
                        value="{{ $minimumSpendValue }}"
                        x-model="form.minimumSpend"
                        step="0.01"
                        min="0"
                    >
                    <p class="text-xs text-gray-500">{{ __('cms.coupons.minimum_spend_hint') }}</p>
                    @error('minimum_spend')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="usage_limit" class="form-label">{{ __('cms.coupons.usage_limit') }}</label>
                    <input
                        type="number"
                        name="usage_limit"
                        id="usage_limit"
                        class="form-control @error('usage_limit') is-invalid @enderror"
                        value="{{ $usageLimitValue }}"
                        x-model="form.usageLimit"
                        min="1"
                        step="1"
                    >
                    <p class="text-xs text-gray-500">{{ __('cms.coupons.usage_limit_hint') }}</p>
                    @if ($couponModel && $couponModel->usage_count)
                        <p class="text-xs text-gray-500">{{ __('cms.coupons.usage_count_info', ['count' => $couponModel->usage_count]) }}</p>
                    @endif
                    @error('usage_limit')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="space-y-4 border-t border-gray-200 pt-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ __('cms.coupons.expiry_section_title') }}</h3>
                    <p class="mt-1 text-xs text-gray-500">{{ __('cms.coupons.expiry_section_description') }}</p>
                </div>

                <label class="form-check inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        x-model="showExpiry"
                        @change="if (!showExpiry && $refs.expiresInput) { $refs.expiresInput.value = ''; }"
                    >
                    <span class="text-sm font-medium text-gray-700">{{ __('cms.coupons.expiry_toggle_label') }}</span>
                </label>
                <p class="text-xs text-gray-500">{{ __('cms.coupons.expiry_toggle_hint') }}</p>

                <div x-show="showExpiry" x-cloak class="space-y-2">
                    <label for="expires_at" class="form-label">{{ __('cms.coupons.expires_at') }}</label>
                    <input
                        type="datetime-local"
                        name="expires_at"
                        id="expires_at"
                        x-ref="expiresInput"
                        class="form-control @error('expires_at') is-invalid @enderror"
                        value="{{ $expiresAtValue }}"
                        x-model="form.expiresAt"
                    >
                    @if ($timezone)
                        <p class="text-xs text-gray-500">{{ __('cms.coupons.expiry_timezone_hint', ['timezone' => $timezone]) }}</p>
                    @endif
                    @error('expires_at')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" data-coupon-preview>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('cms.coupons.preview.heading') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('cms.coupons.preview.description') }}</p>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700"
                        x-text="type === 'percentage' ? @js(__('cms.coupons.type_labels.percentage')) : @js(__('cms.coupons.type_labels.fixed'))"
                    ></span>
                </div>

                <div class="mt-4 rounded-md border border-dashed border-gray-300 bg-gray-50 p-4 text-sm">
                    <p class="font-semibold text-gray-900" x-text="form.code || @js(__('cms.coupons.preview.placeholder_code'))"></p>
                    <p class="mt-2 text-gray-700" x-text="previewDiscountLabel"></p>
                    <dl class="mt-4 space-y-3 text-xs text-gray-600">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="font-medium text-gray-700">{{ __('cms.coupons.minimum_spend') }}</dt>
                            <dd class="text-right" x-text="minimumSpendLabel"></dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="font-medium text-gray-700">{{ __('cms.coupons.usage_limit') }}</dt>
                            <dd class="text-right" x-text="usageLimitLabel"></dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="font-medium text-gray-700">{{ __('cms.coupons.expires_at') }}</dt>
                            <dd class="text-right" x-text="expiresAtLabel"></dd>
                        </div>
                    </dl>
                </div>

                <div
                    x-show="discountWarning"
                    x-cloak
                    class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"
                >
                    <span x-text="discountWarning"></span>
                </div>
            </div>

            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-5">
                <h3 class="text-sm font-semibold text-indigo-900">{{ __('cms.coupons.templates.heading') }}</h3>
                <p class="mt-1 text-xs text-indigo-900/80">{{ __('cms.coupons.templates.description') }}</p>
                <div class="mt-4 space-y-3">
                    <template x-for="template in templates" :key="template.key">
                        <button
                            type="button"
                            class="w-full rounded-md border border-indigo-200 bg-white px-4 py-3 text-left text-sm text-indigo-900 transition hover:border-indigo-300 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            @click.prevent="applyTemplate(template)"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold" x-text="template.label"></span>
                                <span class="text-xs font-medium uppercase tracking-wide text-indigo-500">{{ __('cms.coupons.templates.apply') }}</span>
                            </div>
                            <p class="mt-2 text-xs text-indigo-700" x-text="template.description"></p>
                        </button>
                    </template>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-800">{{ __('cms.coupons.helper.heading') }}</h3>
                <p class="mt-1 text-xs text-gray-500">{{ __('cms.coupons.helper.description') }}</p>
                <ul class="mt-4 space-y-3 text-xs text-gray-600">
                    @foreach (__('cms.coupons.helper.items') as $item)
                        <li class="flex gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-[10px] font-semibold text-gray-600">{{ $loop->iteration }}</span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-gray-500">{{ __('cms.coupons.helper.footer') }}</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
        <x-admin.button-link href="{{ $cancelUrl }}" class="btn-outline">
            {{ __('cms.coupons.back_to_list') }}
        </x-admin.button-link>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
