@extends('admin.layouts.admin')

@section('content')
    <div class="mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h4 class="mb-1">Update Site Settings</h4>
                <p class="text-muted mb-0">Fine-tune the storefront experience across branding, communication, and availability.</p>
            </div>
            <a href="{{ route('admin.site-settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to overview
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.site-settings.update') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">Brand Identity</h5>
                            <p class="text-muted small mb-0">Control the visible elements of your storefront experience.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                                    <input type="text" id="site_name" name="site_name" class="form-control @error('site_name') is-invalid @enderror"
                                           value="{{ old('site_name', $settings->site_name) }}" required>
                                    @error('site_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tagline" class="form-label">Tagline</label>
                                    <input type="text" id="tagline" name="tagline" class="form-control @error('tagline') is-invalid @enderror"
                                           value="{{ old('tagline', $settings->tagline) }}">
                                    @error('tagline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="logo" class="form-label">Logo Path</label>
                                    <input type="text" id="logo" name="logo" class="form-control @error('logo') is-invalid @enderror"
                                           value="{{ old('logo', $settings->logo) }}" placeholder="images/logo.svg">
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="favicon" class="form-label">Favicon Path</label>
                                    <input type="text" id="favicon" name="favicon" class="form-control @error('favicon') is-invalid @enderror"
                                           value="{{ old('favicon', $settings->favicon) }}" placeholder="images/favicon.ico">
                                    @error('favicon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="primary_color" class="form-label">Primary Color</label>
                                    <input type="color" id="primary_color" name="primary_color"
                                           class="form-control form-control-color w-100 @error('primary_color') is-invalid @enderror"
                                           value="{{ old('primary_color', $settings->primary_color ?? '#0d6efd') }}">
                                    @error('primary_color')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="secondary_color" class="form-label">Secondary Color</label>
                                    <input type="color" id="secondary_color" name="secondary_color"
                                           class="form-control form-control-color w-100 @error('secondary_color') is-invalid @enderror"
                                           value="{{ old('secondary_color', $settings->secondary_color ?? '#6610f2') }}">
                                    @error('secondary_color')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">SEO Fundamentals</h5>
                            <p class="text-muted small mb-0">Help customers discover your storefront with clear metadata.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" id="meta_title" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror"
                                           value="{{ old('meta_title', $settings->meta_title) }}">
                                    @error('meta_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea id="meta_description" name="meta_description" rows="3"
                                              class="form-control @error('meta_description') is-invalid @enderror"
                                              placeholder="A concise summary shown in search results">{{ old('meta_description', $settings->meta_description) }}</textarea>
                                    @error('meta_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" id="meta_keywords" name="meta_keywords"
                                           class="form-control @error('meta_keywords') is-invalid @enderror"
                                           value="{{ old('meta_keywords', $settings->meta_keywords) }}" placeholder="commerce, laravel, storefront">
                                    @error('meta_keywords')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">Maintenance & Messaging</h5>
                            <p class="text-muted small mb-0">Communicate downtime gracefully and set the footer message.</p>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode"
                                       value="1" {{ old('maintenance_mode', $settings->maintenance_mode) ? 'checked' : '' }}>
                                <label class="form-check-label" for="maintenance_mode">Enable maintenance mode</label>
                            </div>
                            <div class="mb-3">
                                <label for="maintenance_message" class="form-label">Maintenance Message</label>
                                <textarea id="maintenance_message" name="maintenance_message" rows="3"
                                          class="form-control @error('maintenance_message') is-invalid @enderror"
                                          placeholder="Let customers know when you will return">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea>
                                @error('maintenance_message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="footer_text" class="form-label">Footer Text</label>
                                <textarea id="footer_text" name="footer_text" rows="3"
                                          class="form-control @error('footer_text') is-invalid @enderror"
                                          placeholder="© {{ now()->year }} Your company. All rights reserved.">{{ old('footer_text', $settings->footer_text) }}</textarea>
                                @error('footer_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">Contact Channels</h5>
                            <p class="text-muted small mb-0">Keep your audience informed about how and when to reach you.</p>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" id="contact_email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                                       value="{{ old('contact_email', $settings->contact_email) }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="support_email" class="form-label">Support Email</label>
                                <input type="email" id="support_email" name="support_email" class="form-control @error('support_email') is-invalid @enderror"
                                       value="{{ old('support_email', $settings->support_email) }}">
                                @error('support_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                                       value="{{ old('contact_phone', $settings->contact_phone) }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="support_hours" class="form-label">Support Hours</label>
                                <input type="text" id="support_hours" name="support_hours" class="form-control @error('support_hours') is-invalid @enderror"
                                       value="{{ old('support_hours', $settings->support_hours) }}" placeholder="Mon – Fri, 9 AM – 5 PM">
                                @error('support_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" rows="3"
                                          class="form-control @error('address') is-invalid @enderror">{{ old('address', $settings->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">Social Presence</h5>
                            <p class="text-muted small mb-0">Share where customers can follow along with updates.</p>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="facebook_url" class="form-label">Facebook URL</label>
                                <input type="url" id="facebook_url" name="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror"
                                       value="{{ old('facebook_url', $settings->facebook_url) }}">
                                @error('facebook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                <input type="url" id="twitter_url" name="twitter_url" class="form-control @error('twitter_url') is-invalid @enderror"
                                       value="{{ old('twitter_url', $settings->twitter_url) }}">
                                @error('twitter_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="instagram_url" class="form-label">Instagram URL</label>
                                <input type="url" id="instagram_url" name="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror"
                                       value="{{ old('instagram_url', $settings->instagram_url) }}">
                                @error('instagram_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                <input type="url" id="linkedin_url" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror"
                                       value="{{ old('linkedin_url', $settings->linkedin_url) }}">
                                @error('linkedin_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.site-settings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save me-1"></i> Save changes
                </button>
            </div>
        </form>
    </div>
@endsection
