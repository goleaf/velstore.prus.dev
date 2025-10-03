<!-- resources/views/admin/site-settings/index.blade.php -->

@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Site Settings</h6>
            <a href="{{ route('admin.site-settings.edit') }}" class="btn btn-light btn-sm text-primary">Edit Settings</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-uppercase text-muted mb-3">Brand Identity</h6>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="border rounded p-2 bg-light">
                            @if (!empty($settings?->logo_url))
                                <img src="{{ $settings->logo_url }}" alt="Site logo" class="img-fluid" style="max-height: 64px;">
                            @else
                                <span class="text-muted small">No logo uploaded</span>
                            @endif
                        </div>
                        <div>
                            <p class="mb-1"><strong>{{ $settings->site_name ?? 'Not set' }}</strong></p>
                            <p class="text-muted mb-0">{{ $settings->tagline ?? 'Tagline not set' }}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Top Bar Message</h6>
                        <p class="mb-0 text-muted">{{ $settings->top_bar_message ?? 'Not set' }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">SEO</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Meta Title</dt>
                            <dd class="col-sm-8">{{ $settings->meta_title ?? 'Not set' }}</dd>
                            <dt class="col-sm-4">Meta Description</dt>
                            <dd class="col-sm-8">{{ $settings->meta_description ?? 'Not set' }}</dd>
                            <dt class="col-sm-4">Meta Keywords</dt>
                            <dd class="col-sm-8">{{ $settings->meta_keywords ?? 'Not set' }}</dd>
                        </dl>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Favicon</h6>
                        @if (!empty($settings?->favicon_url))
                            <img src="{{ $settings->favicon_url }}" alt="Favicon" class="img-thumbnail" style="max-height: 48px; width: auto;">
                        @else
                            <p class="text-muted mb-0">No favicon uploaded</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-uppercase text-muted mb-3">Contact &amp; Footer</h6>
                    <dl class="row mb-3">
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $settings->contact_email ?? 'Not set' }}</dd>
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $settings->contact_phone ?? 'Not set' }}</dd>
                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $settings->address ?? 'Not set' }}</dd>
                    </dl>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Footer Text</h6>
                        <p class="mb-0 text-muted">{{ $settings->footer_text ?? 'Not set' }}</p>
                    </div>

                    <div>
                        <h6 class="text-uppercase text-muted mb-3">Social Links</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Facebook</span>
                                <span class="text-muted">{{ $settings->facebook_url ?? 'Not set' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Instagram</span>
                                <span class="text-muted">{{ $settings->instagram_url ?? 'Not set' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Twitter / X</span>
                                <span class="text-muted">{{ $settings->twitter_url ?? 'Not set' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>LinkedIn</span>
                                <span class="text-muted">{{ $settings->linkedin_url ?? 'Not set' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
