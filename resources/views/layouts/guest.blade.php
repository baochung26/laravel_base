<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @php
        $uiConfig = config('ui.web', []);
        $uiConfig['loading']['text'] = __('ui.js.loading');
        $uiConfig['i18n'] = trans('ui.js');
    @endphp
    <script>window.AppUIConfig = @json($uiConfig);</script>
    <script>window.AppFormErrors = @json($errors->toArray());</script>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
</head>
<body
    class="auth-body"
    @if (session('status'))
        data-alert-type="success"
        data-alert-message="{{ session('status') }}"
    @endif
>
    <main class="auth-card">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
