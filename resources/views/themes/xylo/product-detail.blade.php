@extends('themes.xylo.layouts.master')
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endsection
@section('content')
@php $currency = activeCurrency(); @endphp
<section class="breadcrumb-section">
    <div class="container">
        <div class="breadcrumbs" aria-label="breadcrumb">
            <a href="{{ url('/') }}">{{ __('store.product_detail.home') }}</a>
            <i class="fa fa-angle-right"></i>
            @foreach($breadcrumbs as $category)
                <a href="{{ url('category/' . $category->slug) }}">
                    {{ $category->translation->name ?? $category->slug }}
                </a>
                <i class="fa fa-angle-right"></i>
            @endforeach
            <span>{{ $product->translation->name }}</span>
        </div>
    </div>
</section>
<div class="main-detail pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 position-relative">
                <div class="product-slider">
                    @foreach ($product->images as $image)
                        <div>
                            <img src="{{ Storage::url($image['image_url']) }}" alt="{{ $image['name'] }}" style="width: 100%; height: auto;" />
                        </div>
                    @endforeach
                </div>

            </div>
            <div class="col-md-6 pro-textarea">
                @if ($inStock)
                    <div id="product-stock" class="mb-2 mt-3 btnss">IN STOCK</div>
                @else
                    <div id="product-stock" class="mb-2 mt-3 btnss text-danger">OUT OF STOCK</div>
                @endif
                @php
                    $averageRating = round($product->reviews_avg_rating, 1);
                @endphp
                <div class="stars">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= floor($averageRating))
                            <i class="fa-solid fa-star text-warning"></i>
                        @elseif ($i - 0.5 == $averageRating)
                            <i class="fa-solid fa-star-half-alt text-warning"></i>
                        @else
                            <i class="fa-regular fa-star text-muted"></i>
                        @endif
                    @endfor
                    <span class="spanstar"> ({{ $product->reviews_count }}{{ __('store.product_detail.customer_reviews') }})</span>
                </div>
                <h1 class="sec-heading">{{ $product->translation->name }}</h1>
                <h2><span id="currency-symbol">{{ $currency->symbol }}</span><span  id="variant-price" >{{ $product->primaryVariant->converted_price ?? 'N/A' }}</span></h2>
                <p>{{ $product->translation->short_description }}</p>




                <x-themes.xylo.product-variant-picker
                    :product="$product"
                    :variant-map="$variantMap"
                    :in-stock="$inStock"
                />

            </div>
        </div>
    </div>
</div>
<div class="reviewbox py-5">
  <div class="container">
    <div class="row">
      <div class="col-12">

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description"
                    type="button" role="tab" aria-controls="description" aria-selected="true">{{ __('store.product_detail.description') }}</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                    type="button" role="tab" aria-controls="reviews" aria-selected="false">{{ __('store.product_detail.reviews') }} ({{ $product->reviews_count }})</button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content pt-3" id="myTabContent">
          <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
            {!! $product->translation->description !!}
          </div>
          <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
            <div class="product-detail-customer-review">
                @if($product->reviews->isEmpty())
                    <p>No reviews for this product yet.</p>
                @else
                    <ul>
                        @foreach($product->reviews as $review)
                            @if($review->is_approved)
                                <li>
                                    <!-- Display Customer's Image -->
                                    <div class="review-customer-info">
                                        <img src="https://i.ibb.co/HTv1bQrD/customer.jpg" alt="Customer Avatar" class="review-customer-avatar" />
                                        <strong>{{ ucwords($review->customer->name) }}</strong>
                                    </div>

                                    <!-- Display Rating with Stars -->
                                    <div class="review-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="star {{ $i <= $review->rating ? 'filled' : 'unfilled' }}">&#9733;</span>
                                        @endfor
                                        <span class="review-time">
                                            @php
                                                $created_at = \Carbon\Carbon::parse($review->created_at);
                                                $diffInDays = $created_at->diffInDays(\Carbon\Carbon::now());
                                            @endphp
                                            ({{ $diffInDays }} {{ $diffInDays == 1 ? 'day' : 'days' }} ago)
                                        </span>
                                    </div>

                                    <!-- Display Review Text -->
                                    @if($review->review)
                                        <p>{{ $review->review }}</p>
                                    @else
                                        <p>No review written.</p>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>

                        <!-- Display Average Rating -->
                        <div class="average-rating">
                            

                            <div class="review-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($product->reviews_avg_rating))
                                        <span class="star filled">★</span>
                                    @elseif($i == ceil($product->reviews_avg_rating) && ($product->reviews_avg_rating - floor($product->reviews_avg_rating)) >= 0.5)
                                        <span class="star half-filled">★</span>
                                    @else
                                        <span class="star unfilled">★</span>
                                    @endif
                                @endfor

                                {{ number_format($product->reviews_avg_rating, 1) }} <span> Average Rating</span> 
                            </div>
                        </div>
                @endif
                </div> <!-- End div.product-detail-customer-review -->
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>


@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.product-slider').slick({
                arrows: true,
                dots: false,
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                prevArrow: '<button type="button" class="slick-prev">←</button>',
                nextArrow: '<button type="button" class="slick-next">→</button>',
            });
        });
    </script>
@endsection
