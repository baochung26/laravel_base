<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    @vite(['resources/css/landing.css', 'resources/js/app.js'])
</head>
<body class="landing-body">
    @include('partials.landing.header')

    <main>
        @yield('content')
    </main>

    @include('partials.landing.footer')
    @stack('scripts')
</body>
</html>
