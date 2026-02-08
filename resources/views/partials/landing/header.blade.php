<header class="landing-header sticky-top">
    <nav class="navbar navbar-expand-lg border-bottom border-light border-opacity-10">
        <div class="container landing-container">
            <a class="navbar-brand fw-bold text-white" href="#top">{{ config('app.name', 'NextApp') }}</a>

            <button class="navbar-toggler border-light border-opacity-25" type="button" data-bs-toggle="collapse" data-bs-target="#landingNavbar" aria-controls="landingNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="landingNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#top">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gioi-thieu">Giới thiệu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tinh-nang">Tính năng</a></li>
                    <li class="nav-item"><a class="nav-link" href="#footer">Liên hệ</a></li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-semibold px-3">Dashboard</a>
                        <div class="landing-user d-none d-lg-flex">
                            <div class="landing-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                            <div class="small">
                                <div class="fw-semibold text-white">{{ auth()->user()->name }}</div>
                                <div class="text-secondary-subtle">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-light btn-sm fw-semibold px-3">Đăng ký</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
