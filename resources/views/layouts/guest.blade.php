<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script>window.AppUIConfig = @json(config('ui.web', []));</script>
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
