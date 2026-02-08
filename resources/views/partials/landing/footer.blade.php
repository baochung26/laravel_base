<footer id="footer" class="landing-footer">
    <div class="container landing-container py-5">
        <div class="row g-4 pb-4 border-bottom border-light border-opacity-10">
            <div class="col-12 col-lg-4">
                <h5 class="text-white fw-semibold mb-3">{{ config('app.name', 'NextApp') }}</h5>
                <p class="mb-0 text-secondary">Ứng dụng Laravel hiện đại với authentication đầy đủ và cấu trúc API chuẩn production.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white mb-3">Liên kết nhanh</h6>
                <ul class="list-unstyled m-0 d-grid gap-2">
                    <li><a href="#top">Trang chủ</a></li>
                    <li><a href="#gioi-thieu">Giới thiệu</a></li>
                    <li><a href="#tinh-nang">Tính năng</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="text-white mb-3">Tài khoản</h6>
                <ul class="list-unstyled m-0 d-grid gap-2">
                    <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                    <li><a href="{{ route('register') }}">Đăng ký</a></li>
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                </ul>
            </div>
            <div class="col-12 col-lg-3">
                <h6 class="text-white mb-3">Tài nguyên</h6>
                <ul class="list-unstyled m-0 d-grid gap-2">
                    <li><a href="{{ url('/api/v1/docs') }}">Swagger Docs</a></li>
                    <li><a href="{{ url('/api/v1/openapi.yaml') }}">OpenAPI YAML</a></li>
                    <li><a href="{{ url('/up') }}">Health Check</a></li>
                </ul>
            </div>
        </div>
        <p class="small text-center text-secondary mt-4 mb-0">© {{ now()->year }} {{ config('app.name', 'NextApp') }}. All rights reserved.</p>
    </div>
</footer>
