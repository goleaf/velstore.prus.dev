@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('Edit Review') }}</h6>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-light btn-sm">{{ __('Back to Reviews') }}</a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-4">
                    <label for="rating" class="form-label">{{ __('Rating') }}</label>
                    <input type="number" name="rating" id="rating" min="1" max="5"
                           class="form-control @error('rating') is-invalid @enderror"
                           value="{{ old('rating', $review->rating) }}" required>
                    @error('rating')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="review" class="form-label">{{ __('Review') }}</label>
                    <textarea name="review" id="review" rows="4"
                              class="form-control @error('review') is-invalid @enderror">{{ old('review', $review->review) }}</textarea>
                    @error('review')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 form-check">
                    <input type="hidden" name="is_approved" value="0">
                    <input type="checkbox" name="is_approved" id="is_approved" value="1"
                           class="form-check-input" {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}>
                    <label for="is_approved" class="form-check-label">{{ __('Approved') }}</label>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success">{{ __('Update Review') }}</button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
