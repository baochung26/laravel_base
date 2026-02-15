@extends('layouts.landing')

@section('title', config('app.name', 'Laravel'))

@section('content')
    <section id="top" class="hero-section border-bottom border-light border-opacity-10">
        <div class="container landing-container py-5">
            <div class="hero-grid py-5 text-center">
                <span class="hero-badge mb-4 d-inline-block">Laravel 12 + Sanctum + Web Auth</span>
                <h1 class="hero-title text-white mb-3">Xây dựng ứng dụng hiện đại với Laravel</h1>
                <p id="gioi-thieu" class="hero-subtitle mx-auto mb-0">Template hoàn chỉnh với API chuẩn, xác thực web bằng session và cấu trúc rõ ràng để phát triển dự án production.</p>

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4 pt-2">
                    @auth
                        <a class="btn btn-light btn-lg px-4 fw-semibold" href="{{ \App\Support\WebRedirect::postAuthRoute(auth()->user()) }}">Đi tới Dashboard</a>
                    @else
                        <a class="btn btn-light btn-lg px-4 fw-semibold" href="{{ route('register') }}">{{ __('ui.nav.register') }}</a>
                        <a class="btn btn-outline-light btn-lg px-4" href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <section id="tinh-nang" class="features-section py-5 border-bottom border-light border-opacity-10">
        <div class="container landing-container py-4 py-lg-5">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold text-white mb-3">Tính năng nổi bật</h2>
                <p class="section-muted mx-auto mb-0">Tập trung vào nền tảng chuẩn để bạn phát triển nhanh business logic thay vì lặp lại phần hạ tầng.</p>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="feature-card h-100 p-4">
                        <span class="feature-icon">A</span>
                        <h3 class="h4 text-white mt-3">Authentication</h3>
                        <p class="feature-copy">Web auth và API auth đầy đủ cho luồng người dùng thực tế, bảo mật và dễ mở rộng.</p>
                        <ul class="feature-list mb-0">
                            <li>Login/Register</li>
                            <li>Forgot/Reset Password</li>
                            <li>Email Verification</li>
                        </ul>
                    </article>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <article class="feature-card h-100 p-4">
                        <span class="feature-icon">U</span>
                        <h3 class="h4 text-white mt-3">UI Layout</h3>
                        <p class="feature-copy">Tách layout và partial rõ ràng giúp bảo trì nhanh, tái sử dụng tốt cho nhiều trang mới.</p>
                        <ul class="feature-list mb-0">
                            <li>Header / Footer riêng</li>
                            <li>Landing layout độc lập</li>
                            <li>Responsive Bootstrap grid</li>
                        </ul>
                    </article>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <article class="feature-card h-100 p-4">
                        <span class="feature-icon">API</span>
                        <h3 class="h4 text-white mt-3">API Integration</h3>
                        <p class="feature-copy">Giữ nguyên API hiện tại với versioning rõ ràng, sẵn sàng tích hợp web/mobile app.</p>
                        <ul class="feature-list mb-0">
                            <li>OpenAPI Docs</li>
                            <li>Standard Error Format</li>
                            <li>Sanctum Token Flow</li>
                        </ul>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section py-5">
        <div class="container landing-container py-4 py-lg-5 text-center">
            <h2 class="display-6 fw-bold text-white mb-3">Sẵn sàng bắt đầu?</h2>
            <p class="section-muted mx-auto mb-0">Tạo tài khoản để trải nghiệm toàn bộ web module và sử dụng song song với hệ thống API.</p>

            <div class="d-flex flex-wrap justify-content-center gap-2 mt-4 pt-2">
                @auth
                    <a class="btn btn-light btn-lg px-4 fw-semibold" href="{{ \App\Support\WebRedirect::postAuthRoute(auth()->user()) }}">Mở Dashboard</a>
                @else
                    <a class="btn btn-light btn-lg px-4 fw-semibold" href="{{ route('register') }}">{{ __('ui.nav.register') }}</a>
                    <a class="btn btn-outline-light btn-lg px-4" href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                @endauth
            </div>
        </div>
    </section>
@endsection
