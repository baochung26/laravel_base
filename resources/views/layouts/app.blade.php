<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel').' Dashboard')</title>
    @php
        $uiConfig = config('ui.web', []);
        $uiConfig['loading']['text'] = __('ui.js.loading');
        $uiConfig['i18n'] = trans('ui.js');
    @endphp
    <script>window.AppUIConfig = @json($uiConfig);</script>
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
        @php
            $appName = config('app.name', 'Laravel');
            $appInitial = strtoupper(mb_substr($appName, 0, 1));
        @endphp
        <a class="dash-logo" href="{{ route('welcome') }}">
            <span class="dash-logo-mark">{{ $appInitial }}</span>
            <span class="dash-logo-text">{{ $appName }}</span>
        </a>

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
                data-confirm-title="{{ __('ui.confirm.logout_title') }}"
                data-confirm-message="{{ __('ui.confirm.logout_message') }}"
            >
                @csrf
                <button type="submit" class="dash-logout-btn">
                    <x-dashboard.icon name="logout" class="dash-nav-icon" />
                    {{ __('ui.nav.logout') }}
                </button>
            </form>
        </div>
    </aside>

    <section class="dash-main">
        <header class="dash-topbar">
            <div class="dash-topbar-left">
                <a class="dash-topbar-brand" href="{{ route('welcome') }}">
                    <span class="dash-topbar-brand-mark">{{ $appInitial }}</span>
                    <span class="dash-topbar-brand-text">{{ $appName }}</span>
                </a>
                <h1>@yield('page_title', __('ui.dashboard.overview'))</h1>
            </div>
            <div class="dash-topbar-right">
                @php
                    $currentLocale = app()->getLocale();
                @endphp
                <form method="POST" action="{{ route('locale.switch') }}" class="dash-lang-switch" aria-label="Language switcher">
                    @csrf
                    <button
                        type="submit"
                        name="locale"
                        value="vi"
                        class="dash-lang-btn {{ $currentLocale === 'vi' ? 'is-active' : '' }}"
                        aria-pressed="{{ $currentLocale === 'vi' ? 'true' : 'false' }}"
                    >
                        VI
                    </button>
                    <button
                        type="submit"
                        name="locale"
                        value="en"
                        class="dash-lang-btn {{ $currentLocale === 'en' ? 'is-active' : '' }}"
                        aria-pressed="{{ $currentLocale === 'en' ? 'true' : 'false' }}"
                    >
                        EN
                    </button>
                </form>
                <a href="{{ route('welcome') }}" class="dash-home-link">{{ __('ui.nav.back_home') }}</a>
                @php
                    $user = auth()->user();
                    $nameParts = preg_split('/\s+/', trim($user?->name ?? 'U')) ?: ['U'];
                    $initials = strtoupper(
                        collect($nameParts)
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->implode('')
                    );
                    $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
                @endphp
                <div class="dropdown dash-user-dropdown">
                    <button
                        class="dash-user-summary dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                    >
                        @if ($user?->avatar_url)
                            <img
                                src="{{ $user->avatar_url }}"
                                alt="Avatar {{ $user->name }}"
                                class="dash-avatar dash-avatar-image"
                            >
                        @else
                            <span class="dash-avatar">{{ $initials ?: 'U' }}</span>
                        @endif
                        <span class="dash-user-meta">
                            <strong>{{ $user?->name ?? 'User' }}</strong>
                            <small>{{ $user?->email ?? '' }}</small>
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end dash-user-menu">
                        <div class="dash-user-menu-head">
                            <strong>{{ $user?->name ?? 'User' }}</strong>
                            <small>{{ $user?->email ?? '' }}</small>
                        </div>

                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <span class="dash-menu-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            {{ __('ui.nav.profile') }}
                        </a>

                        @if ($isAdmin)
                            <a class="dropdown-item" href="{{ route('dashboard') }}">
                                <span class="dash-menu-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3.5" y="3.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                        <rect x="13.5" y="3.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                        <rect x="3.5" y="13.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                        <rect x="13.5" y="13.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </span>
                                {{ __('ui.nav.dashboard') }}
                            </a>
                        @endif

                        <div class="dropdown-divider"></div>
                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                            class="dash-logout-form"
                            data-confirm
                            data-confirm-title="{{ __('ui.confirm.logout_title') }}"
                            data-confirm-message="{{ __('ui.confirm.logout_message') }}"
                        >
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <span class="dash-menu-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 16l-4-4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 12H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M14 5h4a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                {{ __('ui.nav.logout') }}
                            </button>
                        </form>
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
