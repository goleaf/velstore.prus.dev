@extends('admin.layouts.admin')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mt-4">
        <div>
            <h4 class="mb-1">Site Settings Overview</h4>
            <p class="text-muted mb-0">Review the configuration that powers your storefront experience.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.site-settings.edit') }}" class="btn btn-primary">
                <i class="bi bi-gear-wide-connected me-1"></i> Manage Settings
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $settings = $settings ?? null;

        $sections = [
            'Brand Identity' => [
                'Site Name' => $settings->site_name ?? 'Not set',
                'Tagline' => $settings->tagline ?? 'Not set',
                'Logo' => $settings->logo ?? 'Not set',
                'Favicon' => $settings->favicon ?? 'Not set',
                'Primary Color' => $settings->primary_color ?? 'Not set',
                'Secondary Color' => $settings->secondary_color ?? 'Not set',
            ],
            'SEO Fundamentals' => [
                'Meta Title' => $settings->meta_title ?? 'Not set',
                'Meta Description' => $settings->meta_description ?? 'Not set',
                'Meta Keywords' => $settings->meta_keywords ?? 'Not set',
            ],
            'Contact & Support' => [
                'Contact Email' => $settings->contact_email ?? 'Not set',
                'Support Email' => $settings->support_email ?? 'Not set',
                'Contact Phone' => $settings->contact_phone ?? 'Not set',
                'Support Hours' => $settings->support_hours ?? 'Not set',
                'Address' => $settings->address ?? 'Not set',
            ],
            'Social Presence' => array_filter([
                'Facebook' => $settings->facebook_url ?? null,
                'Twitter' => $settings->twitter_url ?? null,
                'Instagram' => $settings->instagram_url ?? null,
                'LinkedIn' => $settings->linkedin_url ?? null,
            ]),
        ];

        $maintenanceEnabled = $settings->maintenance_mode ?? false;
    @endphp

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="row g-4">
                @foreach ($sections as $title => $items)
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0">{{ $title }}</h6>
                            </div>
                            <div class="card-body">
                                @if (empty($items))
                                    <p class="text-muted mb-0">No information provided.</p>
                                @else
                                    <dl class="row g-0 mb-0">
                                        @foreach ($items as $label => $value)
                                            <dt class="col-5 text-muted small mb-2">{{ $label }}</dt>
                                            <dd class="col-7 mb-2 text-break">
                                                @if (\Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($label), 'primary color') && $value && $value !== 'Not set')
                                                    <span class="badge rounded-pill" style="background-color: {{ $value }};">{{ $value }}</span>
                                                @elseif (\Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($label), 'secondary color') && $value && $value !== 'Not set')
                                                    <span class="badge rounded-pill" style="background-color: {{ $value }};">{{ $value }}</span>
                                                @elseif (\Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($label), 'facebook') || \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($label), 'twitter') || \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($label), 'instagram') || \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($label), 'linkedin'))
                                                    @if ($value)
                                                        <a href="{{ $value }}" target="_blank" rel="noopener" class="text-decoration-none">{{ $value }}</a>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </dd>
                                        @endforeach
                                    </dl>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Maintenance & Messaging</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge {{ $maintenanceEnabled ? 'bg-danger' : 'bg-success' }} me-2">
                            {{ $maintenanceEnabled ? 'Enabled' : 'Disabled' }}
                        </span>
                        <span class="text-muted small">Maintenance mode</span>
                    </div>
                    <p class="text-muted small mb-2">What customers will see when maintenance mode is active:</p>
                    <p class="border rounded p-3 bg-light small mb-0">
                        {{ $settings->maintenance_message ?? 'No maintenance message configured.' }}
                    </p>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <p class="text-muted small mb-1">Footer Message</p>
                    <p class="small mb-0">{{ $settings->footer_text ?? 'No footer text configured.' }}</p>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Last updated</span>
                        <span class="fw-semibold small">{{ optional($settings->updated_at)->diffForHumans() ?? 'Never' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $maintenanceEnabled ? 40 : 80 }}%" aria-valuenow="{{ $maintenanceEnabled ? 40 : 80 }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        Keep your storefront message aligned with your brand by reviewing these settings regularly.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
