@extends('layouts.guest')

@section('content')
    <h1>{{ __('ui.auth.verify_title') }}</h1>
    <p>{{ __('ui.auth.verify_subtitle') }}</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <div class="actions">
            <a class="link" href="{{ \App\Support\WebRedirect::postAuthRoute(auth()->user()) }}">{{ __('ui.auth.go_dashboard') }}</a>
            <button class="btn" type="submit">{{ __('ui.auth.resend_verification') }}</button>
        </div>
    </form>
@endsection
