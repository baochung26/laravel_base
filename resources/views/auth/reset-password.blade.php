@extends('layouts.guest')

@section('content')
    <h1>{{ __('ui.auth.reset_title') }}</h1>
    <p>{{ __('ui.auth.reset_subtitle') }}</p>

    <form method="POST" action="{{ route('password.update') }}" data-validate novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="field">
            <label for="email">{{ __('ui.form.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus data-label="{{ __('ui.form.email') }}">
        </div>

        <div class="field">
            <label for="password">{{ __('ui.form.new_password') }}</label>
            <input id="password" name="password" type="password" required data-label="{{ __('ui.form.new_password') }}" data-min-length="8">
        </div>

        <div class="field">
            <label for="password_confirmation">{{ __('ui.form.password_confirm') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required data-label="{{ __('ui.form.password_confirm') }}" data-match="#password">
        </div>

        <div class="actions">
            <a class="link" href="{{ route('login') }}">{{ __('ui.auth.back_login') }}</a>
            <button class="btn" type="submit">{{ __('ui.auth.submit_reset') }}</button>
        </div>
    </form>
@endsection
