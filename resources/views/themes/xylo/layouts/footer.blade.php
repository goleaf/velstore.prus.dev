<footer class="bg-light pt-5">
  <div class="container">
    <div class="row">
      <!-- Column 1: Logo -->
      <div class="col-12 col-md-3 mb-4">
        <img src="{{ $siteSettings?->logo_url ?? asset('assets/images/logo-main.svg') }}" alt="{{ $siteSettings?->site_name ?? __('store.footer.footer_logo_alt') }}" class="img-fluid" style="max-width: 120px;">
        @if (!empty($siteSettings?->tagline))
          <p class="text-muted mt-3">{{ $siteSettings->tagline }}</p>
        @endif
        <ul class="list-unstyled text-muted small mt-3">
          @if (!empty($siteSettings?->contact_email))
            <li class="mb-2"><i class="fa-regular fa-envelope me-2"></i><a href="mailto:{{ $siteSettings->contact_email }}" class="text-muted text-decoration-none">{{ $siteSettings->contact_email }}</a></li>
          @endif
          @if (!empty($siteSettings?->contact_phone))
            <li class="mb-2"><i class="fa fa-phone me-2"></i><a href="tel:{{ $siteSettings->contact_phone }}" class="text-muted text-decoration-none">{{ $siteSettings->contact_phone }}</a></li>
          @endif
          @if (!empty($siteSettings?->address))
            <li><i class="fa fa-location-dot me-2"></i>{{ $siteSettings->address }}</li>
          @endif
        </ul>
      </div>

      <!-- Column 2: Account -->
      <div class="col-6 col-md-3 mb-4">
        <h5> {{ __('store.footer.account') }}</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="#" class="text-muted text-decoration-none">{{ __('store.footer.my_account') }}</a></li>
          <li class="mb-2"><a href="#" class="text-muted text-decoration-none">{{ __('store.footer.wishlist') }}</a></li>
        </ul>
      </div>

      <!-- Column 3: Other Pages -->
      <div class="col-6 col-md-3 mb-4">
        <h5>{{ __('store.footer.pages') }}</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="#" class="text-muted text-decoration-none">{{ __('store.footer.privacy_policy') }}</a></li>
          <li class="mb-2"><a href="#" class="text-muted text-decoration-none">{{ __('store.footer.terms_of_service') }}</a></li>
        </ul>
      </div>

      <!-- Column 4: Social Links -->
    <div class="col-12 col-md-3 mb-4">
    <h5>{{ __('store.footer.follow_us') }}</h5>
    <div class="d-flex gap-3">
        @if (!empty($siteSettings?->facebook_url))
          <a href="{{ $siteSettings->facebook_url }}" class="text-dark fs-5" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
        @endif
        @if (!empty($siteSettings?->twitter_url))
          <a href="{{ $siteSettings->twitter_url }}" class="text-dark fs-5" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
        @endif
        @if (!empty($siteSettings?->instagram_url))
          <a href="{{ $siteSettings->instagram_url }}" class="text-dark fs-5" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
        @endif
        @if (!empty($siteSettings?->linkedin_url))
          <a href="{{ $siteSettings->linkedin_url }}" class="text-dark fs-5" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
        @endif
        @if (empty($siteSettings?->facebook_url) && empty($siteSettings?->twitter_url) && empty($siteSettings?->instagram_url) && empty($siteSettings?->linkedin_url))
          <span class="text-muted small">No social profiles configured yet.</span>
        @endif
    </div>
    </div>

    </div>
  </div>

  <!-- Footer Bottom Strip -->
  <div class="bg-black text-white py-3 mt-4">
    <div class="container">
      <div class="row">
        <div class="col-12 d-flex justify-content-between flex-wrap small">
          <span>{{ $siteSettings?->footer_text ?? __('store.footer.copyright') }}</span>
          <span>{{ __('store.footer.powered_by') }}</span>
        </div>
      </div>
    </div>
  </div>
</footer>
