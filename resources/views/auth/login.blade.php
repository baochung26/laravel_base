@extends('layouts.guest')

@section('content')
    <div class="auth-top">
        <h1 class="auth-brand">{{ config('app.name', 'NextApp') }}</h1>
        <a class="auth-back" href="{{ route('welcome') }}">{{ __('ui.auth.back_home') }}</a>
    </div>

    <h2 class="auth-title">{{ __('ui.auth.login_title') }}</h2>
    <p class="auth-subtitle">{{ __('ui.auth.login_subtitle') }}</p>

    <form method="POST" action="{{ route('login') }}" id="login-form" data-validate novalidate>
        @csrf

        <div class="field">
            <label for="email">{{ __('ui.form.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="{{ __('ui.placeholder.email') }}" data-label="{{ __('ui.form.email') }}">
        </div>

        <div class="field">
            <label for="password">{{ __('ui.form.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="{{ __('ui.placeholder.password') }}" data-label="{{ __('ui.form.password') }}" data-min-length="8">
        </div>

        <div class="auth-row">
            <label class="auth-remember-label">
                <input type="checkbox" name="remember" class="auth-remember-checkbox">
                {{ __('ui.auth.remember') }}
            </label>
            <a class="auth-forgot" href="{{ route('password.request') }}">{{ __('ui.auth.forgot_link') }}</a>
        </div>

        <button class="auth-submit" type="submit" id="submit-btn" data-loading-text="{{ __('ui.auth.submit_login_loading') }}">{{ __('ui.auth.submit_login') }}</button>
    </form>

    <div class="auth-divider">{{ __('ui.auth.or_continue') }}</div>

    <div class="google-wrap">
        @if (filled(config('services.google.client_id')))
            <div id="g_id_onload"
                 data-client_id="{{ config('services.google.client_id') }}"
                 data-callback="onGoogleSignInCallback">
            </div>

            <div class="g_id_signin"
                 data-type="standard"
                 data-theme="outline"
                 data-size="large"
                 data-shape="rectangular"
                 data-text="signin_with"
                 data-logo_alignment="left"
                 data-width="100%">
            </div>

            <form id="google-login-form" method="POST" action="{{ route('login.google') }}" style="display:none;" novalidate>
                @csrf
                <input type="hidden" name="id_token" id="google-id-token">
            </form>
        @else
            <button class="auth-submit" type="button" disabled>{{ __('ui.auth.google_login') }}</button>
            <p class="google-login-note">{{ __('ui.auth.google_missing_config') }}</p>
        @endif
    </div>

    <p class="auth-register">
        {{ __('ui.auth.no_account') }}
        <a href="{{ route('register') }}">{{ __('ui.auth.register_now') }}</a>
    </p>

@endsection

@push('scripts')
    @vite('resources/js/pages/auth.js')
@endpush
