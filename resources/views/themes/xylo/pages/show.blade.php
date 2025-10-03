@extends('themes.xylo.layouts.master')

@section('page_title', $metaTitle . ' | ' . config('app.name'))
@section('meta_description', $metaDescription)

@section('content')
    @if($page->template === 'with-hero' && $translation->image_url)
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="display-5 fw-semibold">{{ $translation->title }}</h1>
                        @if($translation->excerpt)
                            <p class="lead text-muted">{{ $translation->excerpt }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <img src="{{ Storage::url($translation->image_url) }}" class="img-fluid rounded-3 shadow-sm"
                             alt="{{ $translation->title }}">
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="py-5 bg-light">
            <div class="container">
                <h1 class="display-5 fw-semibold mb-0">{{ $translation->title }}</h1>
                @if($translation->excerpt)
                    <p class="text-muted mt-3">{{ $translation->excerpt }}</p>
                @endif
            </div>
        </section>
    @endif

    <section class="py-5">
        <div class="container">
            <article class="mx-auto" style="max-width: 820px;">
                <div class="content-body cms-content">
                    {!! $translation->content !!}
                </div>
            </article>
        </div>
    </section>

    @if($relatedPages->count())
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="h4 mb-4">{{ __('store.pages.related_heading') }}</h2>
                <div class="row g-4">
                    @foreach($relatedPages as $relatedPage)
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0">
                                @if($relatedPage['image_url'])
                                    <img src="{{ $relatedPage['image_url'] }}" class="card-img-top" alt="{{ $relatedPage['title'] }}">
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h3 class="h6">{{ $relatedPage['title'] }}</h3>
                                    @if(!empty($relatedPage['excerpt']))
                                        <p class="text-muted small">{{ Str::limit(strip_tags($relatedPage['excerpt']), 120) }}</p>
                                    @endif
                                    <div class="mt-auto">
                                        <a href="{{ route('store.pages.show', $relatedPage['slug']) }}" class="btn btn-link px-0">
                                            {{ __('store.pages.featured_cta') }} <i class="fa fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
