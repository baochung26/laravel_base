@extends('layouts.guest')

@section('content')
    <div class="auth-top">
        <h1 class="auth-brand">{{ config('app.name', 'NextApp') }}</h1>
        <a class="auth-back" href="{{ route('welcome') }}">{{ __('ui.auth.back_home') }}</a>
    </div>

    <h2 class="auth-title">{{ __('ui.auth.forgot_title') }}</h2>
    <p class="auth-subtitle">{{ __('ui.auth.forgot_subtitle') }}</p>

    <form method="POST" action="{{ route('password.email') }}" id="forgot-password-form" data-validate novalidate>
        @csrf

        <div class="field">
            <label for="email">{{ __('ui.form.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="{{ __('ui.placeholder.email_alt') }}" data-label="{{ __('ui.form.email') }}">
        </div>

        <button class="auth-submit" type="submit">{{ __('ui.auth.submit_forgot') }}</button>
    </form>

    <p class="auth-register">
        <a href="{{ route('login') }}">{{ __('ui.auth.back_login') }}</a>
    </p>
@endsection
