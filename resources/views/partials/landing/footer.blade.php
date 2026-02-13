<footer id="footer" class="landing-footer">
    <div class="container landing-container py-5">
        <div class="row landing-footer-grid g-4 pb-4 border-bottom border-light border-opacity-10">
            <div class="col-12 col-md-6 col-xl-3">
                <h5 class="landing-footer-title">{{ config('app.name', 'NextApp') }}</h5>
                <p class="landing-footer-about mb-0">{{ __('ui.footer.about') }}</p>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <h6 class="landing-footer-heading">{{ __('ui.footer.quick_links') }}</h6>
                <ul class="list-unstyled m-0 d-grid gap-2 landing-footer-links">
                    <li><a href="{{ route('welcome') }}#top">{{ __('ui.nav.home') }}</a></li>
                    <li><a href="{{ route('welcome') }}#gioi-thieu">{{ __('ui.nav.about') }}</a></li>
                    <li><a href="{{ route('welcome') }}#tinh-nang">{{ __('ui.nav.features') }}</a></li>
                </ul>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <h6 class="landing-footer-heading">{{ __('ui.footer.account') }}</h6>
                <ul class="list-unstyled m-0 d-grid gap-2 landing-footer-links">
                    @auth
                        <li><a href="{{ route('profile.show') }}">{{ __('ui.nav.profile') }}</a></li>
                        @if (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin'))
                            <li><a href="{{ route('dashboard') }}">{{ __('ui.nav.dashboard') }}</a></li>
                        @endif
                        <li>
                            <form
                                action="{{ route('logout') }}"
                                method="POST"
                                data-confirm
                                data-confirm-title="{{ __('ui.confirm.logout_title') }}"
                                data-confirm-message="{{ __('ui.confirm.logout_message') }}"
                            >
                                @csrf
                                <button class="landing-footer-link-btn" type="submit">{{ __('ui.nav.logout') }}</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}">{{ __('ui.nav.login') }}</a></li>
                        <li><a href="{{ route('register') }}">{{ __('ui.nav.register') }}</a></li>
                    @endauth
                </ul>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <h6 class="landing-footer-heading">{{ __('ui.footer.resources') }}</h6>
                <ul class="list-unstyled m-0 d-grid gap-2 landing-footer-links">
                    <li><a href="{{ url('/api/v1/docs') }}">{{ __('ui.footer.swagger') }}</a></li>
                    <li><a href="{{ url('/api/v1/openapi.yaml') }}">{{ __('ui.footer.openapi') }}</a></li>
                    <li><a href="{{ url('/up') }}">{{ __('ui.footer.health') }}</a></li>
                </ul>
            </div>
        </div>
        <p class="small text-center text-secondary mt-4 mb-0">
            {{ __('ui.footer.rights', ['year' => now()->year, 'app' => config('app.name', 'NextApp')]) }}
        </p>
    </div>
</footer>
