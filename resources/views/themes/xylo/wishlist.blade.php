@extends('themes.xylo.layouts.master')

@section('content')
    <div class="container">
        <h2>Your Wishlist</h2>

        @if($products->isEmpty())
            <p>You have no items in your wishlist.</p>
        @else
            <div class="row">
                @foreach($products as $product)
                    <div class="col-md-3">
                        <div class="card mb-4">
                            <img src="{{ $product->thumbnail->url ?? '/images/default.png' }}" class="card-img-top" alt="{{ $product->translation->name ?? 'Product' }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $product->translation->name ?? 'Unnamed Product' }}</h5>
                                <p class="card-text">Reviews: {{ $product->reviews_count }}</p>
                                {{-- Add more product info here --}}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
