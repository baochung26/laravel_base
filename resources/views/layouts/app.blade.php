<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel').' Dashboard')</title>
    <script>window.AppUIConfig = @json(config('ui.web', []));</script>
    <script>window.AppFormErrors = @json($errors->toArray());</script>
    @vite(['resources/css/dashboard.css', 'resources/js/app.js'])
</head>
<body
    class="dash-body"
    @if (session('status'))
        data-alert-type="success"
        data-alert-message="{{ session('status') }}"
    @elseif (session('error'))
        data-alert-type="error"
        data-alert-message="{{ session('error') }}"
    @endif
>
<div class="dash-shell">
    <aside class="dash-sidebar">
        <h2 class="dash-logo">Dashboard</h2>

        <nav class="dash-nav">
            @foreach ($sidebarMenu ?? [] as $item)
                <a href="{{ $item['url'] ?? '#' }}" class="dash-nav-item {{ ($item['is_active'] ?? false) ? 'active' : '' }}">
                    <x-dashboard.icon :name="$item['icon'] ?? 'circle'" class="dash-nav-icon" />
                    {{ $item['label'] ?? 'Menu' }}
                </a>
            @endforeach
        </nav>

        <div class="dash-sidebar-bottom">
            @foreach ($sidebarFooter ?? [] as $item)
                <a href="{{ $item['url'] ?? '#' }}" class="dash-ghost-link">
                    <x-dashboard.icon :name="$item['icon'] ?? 'circle'" class="dash-nav-icon" />
                    {{ $item['label'] ?? 'Link' }}
                </a>
            @endforeach

            <form
                method="POST"
                action="{{ route('logout') }}"
                data-confirm
                data-confirm-title="Đăng xuất"
                data-confirm-message="Bạn có chắc chắn muốn đăng xuất?"
            >
                @csrf
                <button type="submit" class="dash-logout-btn">
                    <x-dashboard.icon name="logout" class="dash-nav-icon" />
                    Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    <section class="dash-main">
        <header class="dash-topbar">
            <h1>@yield('page_title', 'Tổng quan')</h1>
            <div class="dash-topbar-right">
                <a href="{{ route('welcome') }}" class="dash-home-link">← Về trang chủ</a>
                <div class="dash-user">
                    <div class="dash-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                    <div class="dash-user-meta">
                        <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                        <span>{{ auth()->user()->email ?? '' }}</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="dash-content">@yield('content')</main>
    </section>
</div>
@stack('scripts')
</body>
</html>
