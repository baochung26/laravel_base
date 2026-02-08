<header class="landing-header sticky-top">
    <nav class="landing-navbar border-bottom border-light border-opacity-10">
        <div class="container landing-container landing-header-inner">
            <div class="landing-brand-wrap">
                <a class="landing-brand" href="{{ route('welcome') }}">{{ config('app.name', 'NextApp') }}</a>
            </div>

            <button
                class="navbar-toggler landing-toggler d-lg-none"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#landingNavbarMobile"
                aria-controls="landingNavbarMobile"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <nav class="landing-center-nav d-none d-lg-flex" aria-label="Main navigation">
                <ul class="landing-nav mb-0">
                    <li><a class="nav-link" href="{{ route('welcome') }}#top">Trang chủ</a></li>
                    <li><a class="nav-link" href="{{ route('welcome') }}#gioi-thieu">Giới thiệu</a></li>
                    <li><a class="nav-link" href="{{ route('welcome') }}#tinh-nang">Tính năng</a></li>
                </ul>
            </nav>

            <div class="landing-actions">
                <button
                    type="button"
                    class="theme-toggle"
                    data-theme-toggle
                    aria-label="Chuyển giao diện sáng tối"
                    title="Chuyển giao diện"
                >
                    <span class="theme-icon theme-icon-moon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 12.6A8 8 0 1 1 11.4 4a6.5 6.5 0 1 0 8.6 8.6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="theme-icon theme-icon-sun" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 2v2.2M12 19.8V22M4.93 4.93l1.56 1.56M17.51 17.51l1.56 1.56M2 12h2.2M19.8 12H22M4.93 19.07l1.56-1.56M17.51 6.49l1.56-1.56" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                </button>

                @auth
                    @php
                        $nameParts = preg_split('/\s+/', trim(auth()->user()->name ?? 'U')) ?: ['U'];
                        $initials = strtoupper(
                            collect($nameParts)
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => mb_substr($part, 0, 1))
                                ->implode('')
                        );
                        $user = auth()->user();
                        $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
                    @endphp
                    <div class="dropdown">
                        <button
                            class="landing-user-summary dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                        >
                            <span class="landing-user-avatar">{{ $initials ?: 'U' }}</span>
                            <span class="landing-user-meta">
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->email }}</small>
                            </span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end landing-user-menu">
                            <div class="landing-user-menu-head">
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->email }}</small>
                            </div>

                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                <span class="landing-menu-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                Hồ sơ
                            </a>

                            @if ($isAdmin)
                                <a class="dropdown-item" href="{{ route('dashboard') }}">
                                    <span class="landing-menu-icon">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3.5" y="3.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                            <rect x="13.5" y="3.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                            <rect x="3.5" y="13.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                            <rect x="13.5" y="13.5" width="7" height="7" stroke="currentColor" stroke-width="1.8"/>
                                        </svg>
                                    </span>
                                    Dashboard
                                </a>
                            @endif

                            <div class="dropdown-divider"></div>
                            <form
                                action="{{ route('logout') }}"
                                method="POST"
                                class="landing-logout-form"
                                data-confirm
                                data-confirm-title="Đăng xuất"
                                data-confirm-message="Bạn có chắc chắn muốn đăng xuất?"
                            >
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <span class="landing-menu-icon">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 16l-4-4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M20 12H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M14 5h4a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-link landing-btn-login">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn landing-btn-register">Đăng ký</a>
                @endauth
            </div>

            <div class="collapse landing-mobile-menu d-lg-none" id="landingNavbarMobile">
                <ul class="landing-mobile-nav mb-3">
                    <li><a class="nav-link" href="{{ route('welcome') }}#top">Trang chủ</a></li>
                    <li><a class="nav-link" href="{{ route('welcome') }}#gioi-thieu">Giới thiệu</a></li>
                    <li><a class="nav-link" href="{{ route('welcome') }}#tinh-nang">Tính năng</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
