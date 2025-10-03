@extends('admin.layouts.admin')

@section('content')

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">Edit Site Settings</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted mb-3">Brand Identity</h6>
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" name="site_name" id="site_name" class="form-control" value="{{ old('site_name', $settings->site_name ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="tagline" class="form-label">Tagline</label>
                            <input type="text" name="tagline" id="tagline" class="form-control" value="{{ old('tagline', $settings->tagline ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label for="top_bar_message" class="form-label">Top Bar Message</label>
                            <input type="text" name="top_bar_message" id="top_bar_message" class="form-control" value="{{ old('top_bar_message', $settings->top_bar_message ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" name="logo" id="logo" class="form-control">
                            <small class="text-muted">Upload a square PNG, JPG, or SVG up to 2MB.</small>
                            @if (!empty($settings?->logo_url))
                                <div class="mt-2">
                                    <img src="{{ $settings->logo_url }}" alt="Current logo" class="img-fluid" style="max-height: 80px;">
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="favicon" class="form-label">Favicon</label>
                            <input type="file" name="favicon" id="favicon" class="form-control">
                            <small class="text-muted">PNG, JPG, or ICO up to 512KB.</small>
                            @if (!empty($settings?->favicon_url))
                                <div class="mt-2">
                                    <img src="{{ $settings->favicon_url }}" alt="Current favicon" class="img-thumbnail" style="max-height: 48px; width: auto;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted mb-3">SEO</h6>
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title', $settings->meta_title ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" class="form-control" rows="3">{{ old('meta_description', $settings->meta_description ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" value="{{ old('meta_keywords', $settings->meta_keywords ?? '') }}">
                            <small class="text-muted">Separate keywords with commas.</small>
                        </div>

                        <h6 class="text-uppercase text-muted mb-3 mt-4">Contact Details</h6>
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $settings->address ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="footer_text" class="form-label">Footer Text</label>
                            <textarea name="footer_text" id="footer_text" class="form-control" rows="3">{{ old('footer_text', $settings->footer_text ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-12">
                        <h6 class="text-uppercase text-muted mb-3">Social Profiles</h6>
                    </div>
                    <div class="col-md-6">
                        <label for="facebook_url" class="form-label">Facebook</label>
                        <input type="url" name="facebook_url" id="facebook_url" class="form-control" value="{{ old('facebook_url', $settings->facebook_url ?? '') }}" placeholder="https://facebook.com/your-page">
                    </div>
                    <div class="col-md-6">
                        <label for="instagram_url" class="form-label">Instagram</label>
                        <input type="url" name="instagram_url" id="instagram_url" class="form-control" value="{{ old('instagram_url', $settings->instagram_url ?? '') }}" placeholder="https://instagram.com/your-profile">
                    </div>
                    <div class="col-md-6">
                        <label for="twitter_url" class="form-label">Twitter / X</label>
                        <input type="url" name="twitter_url" id="twitter_url" class="form-control" value="{{ old('twitter_url', $settings->twitter_url ?? '') }}" placeholder="https://x.com/your-handle">
                    </div>
                    <div class="col-md-6">
                        <label for="linkedin_url" class="form-label">LinkedIn</label>
                        <input type="url" name="linkedin_url" id="linkedin_url" class="form-control" value="{{ old('linkedin_url', $settings->linkedin_url ?? '') }}" placeholder="https://www.linkedin.com/company/your-company">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-success">Update Settings</button>
                </div>
            </form>
        </div>
    </div>

@endsection
