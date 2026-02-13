@extends('layouts.guest')

@section('content')
    <div class="auth-top">
        <h1 class="auth-brand">{{ config('app.name', 'NextApp') }}</h1>
        <a class="auth-back" href="{{ route('welcome') }}">{{ __('ui.auth.back_home') }}</a>
    </div>

    <h2 class="auth-title">{{ __('ui.auth.register_title') }}</h2>
    <p class="auth-subtitle">{{ __('ui.auth.register_subtitle') }}</p>

    <form method="POST" action="{{ route('register') }}" id="register-form" data-validate novalidate>
        @csrf

        <div class="field">
            <label for="name">{{ __('ui.form.name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="{{ __('ui.placeholder.name') }}" data-label="{{ __('ui.form.name') }}">
        </div>

        <div class="field">
            <label for="email">{{ __('ui.form.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('ui.placeholder.email') }}" data-label="{{ __('ui.form.email') }}">
        </div>

        <div class="field">
            <label for="password">{{ __('ui.form.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="{{ __('ui.placeholder.password') }}" data-label="{{ __('ui.form.password') }}" data-min-length="8">
        </div>

        <div class="field">
            <label for="password_confirmation">{{ __('ui.form.password_confirm') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="{{ __('ui.placeholder.password') }}" data-label="{{ __('ui.form.password_confirm') }}" data-match="#password">
        </div>

        <button class="auth-submit" type="submit" id="register-submit-btn" data-loading-text="{{ __('ui.auth.submit_register_loading') }}">{{ __('ui.auth.submit_register') }}</button>
    </form>

    <p class="auth-register">
        {{ __('ui.auth.has_account') }}
        <a href="{{ route('login') }}">{{ __('ui.auth.login_now') }}</a>
    </p>
@endsection

@push('scripts')
    @vite('resources/js/pages/auth.js')
@endpush
