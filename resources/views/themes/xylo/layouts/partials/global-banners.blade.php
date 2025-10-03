@if (isset($banners) && $banners->isNotEmpty())
    <section class="global-banner-strip py-2 bg-primary text-white">
        <div class="container">
            <div class="global-banner-slider">
                @foreach ($banners as $banner)
                    @php
                        $translation = $banner->resolveTranslation();
                        $title = $translation?->title ?? $banner->title;
                        $description = $translation?->description;
                        $buttonText = $translation?->button_text;
                        $buttonUrl = $translation?->resolvedButtonUrl();
                    @endphp
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div class="me-auto">
                            <strong>{{ $title }}</strong>
                            @if ($description)
                                <span class="ms-2 text-white-50">{{ $description }}</span>
                            @endif
                        </div>
                        @if ($buttonText)
                            <a class="btn btn-sm btn-light text-primary fw-semibold" href="{{ $buttonUrl ?? '#' }}">
                                {{ $buttonText }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
