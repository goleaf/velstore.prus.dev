@extends('vendor.layouts.master')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 90vh;">
    <div class="card shadow-sm border-0" style="width:800px; max-width: 95vw; transform: translateY(-80px);">
        <div class="card-header bg-secondary text-white rounded-top" style="font-weight: 600; font-size: 1.15rem; border-bottom: 1px solid #e0e0e0;">
            Edit Profile
        </div>
        <div class="card-body bg-white rounded-bottom">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('vendor.profile.update') }}" autocomplete="off" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row mb-4">
                    <div class="col-12 d-flex flex-column align-items-center">
                        <div class="mb-2">
                            <img src="{{ $vendor->avatar ? asset('storage/' . $vendor->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($vendor->name) . '&background=1976d2&color=fff&size=128' }}" alt="Avatar" class="rounded-circle shadow" style="width: 90px; height: 90px; object-fit: cover; border: 2px solid #e0e0e0;">
                        </div>
                        <label for="avatar" class="form-label mb-1">Avatar</label>
                        <input class="form-control form-control-sm bg-light border-0 rounded-2" type="file" id="avatar" name="avatar" accept="image/*" style="max-width: 250px;">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="name" class="form-label mb-1">Name</label>
                        <input type="text" class="form-control border-0 bg-light rounded-2" id="name" name="name" value="{{ old('name', $vendor->name) }}" placeholder="Enter your name">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="email" class="form-label mb-1">Email</label>
                        <input type="email" class="form-control border-0 bg-light rounded-2" id="email" name="email" value="{{ old('email', $vendor->email) }}" placeholder="Enter your email">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="phone" class="form-label mb-1">Phone</label>
                        <input type="text" class="form-control border-0 bg-light rounded-2" id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}" placeholder="Enter your phone number">
                    </div>
                </div>
                  <div class="row mb-3">
                    <div class="col-12">
                        <label for="current_password" class="form-label mb-1">Current Password</label>
                        <input type="password" class="form-control border-0 bg-light rounded-2" id="current_password" name="current_password" placeholder="Enter your current password to change password" autocomplete="current-password">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label for="password" class="form-label mb-1">New password</label>
                        <input type="password" class="form-control border-0 bg-light rounded-2" id="password" name="password" placeholder="Enter new password (leave blank to keep current)" autocomplete="new-password">
                    </div>
                    <div class="col-6">
                        <label for="password_confirmation" class="form-label mb-1">Confirm new password</label>
                        <input type="password" class="form-control border-0 bg-light rounded-2" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 d-flex align-items-center" id="saveProfileBtn" style="background: #1976d2; border: none; font-weight: 500;">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="profileLoader" role="status" aria-hidden="true"></span>
                        <span>Save Profile</span>
                    </button>
                </div>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('form[action="{{ route('vendor.profile.update') }}"]');
                    const btn = document.getElementById('saveProfileBtn');
                    const loader = document.getElementById('profileLoader');
                    form.addEventListener('submit', function() {
                        btn.setAttribute('disabled', 'disabled');
                        loader.classList.remove('d-none');
                    });
                });
            </script>
        </div>
    </div>
</div>
@endsection
