<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <script>window.AppUIConfig = @json(config('ui.web', []));</script>
    @vite(['resources/css/landing.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body
    class="landing-body"
    @if (session('status'))
        data-alert-type="success"
        data-alert-message="{{ session('status') }}"
    @elseif ($errors->any())
        data-alert-type="error"
        data-alert-message="{{ $errors->first() }}"
    @endif
>
    @include('partials.landing.header')

    <main>
        @yield('content')
    </main>

    @include('partials.landing.footer')
    @stack('scripts')
</body>
</html>
