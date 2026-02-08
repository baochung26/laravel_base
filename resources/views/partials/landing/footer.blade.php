<footer id="footer" class="landing-footer">
    <div class="container landing-container py-5">
        <div class="row landing-footer-grid g-4 pb-4 border-bottom border-light border-opacity-10">
            <div class="col-12 col-md-6 col-xl-3">
                <h5 class="landing-footer-title">{{ config('app.name', 'NextApp') }}</h5>
                <p class="landing-footer-about mb-0">Ứng dụng Laravel hiện đại với authentication đầy đủ và cấu trúc API chuẩn production.</p>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <h6 class="landing-footer-heading">Liên kết nhanh</h6>
                <ul class="list-unstyled m-0 d-grid gap-2 landing-footer-links">
                    <li><a href="{{ route('welcome') }}#top">Trang chủ</a></li>
                    <li><a href="{{ route('welcome') }}#gioi-thieu">Giới thiệu</a></li>
                    <li><a href="{{ route('welcome') }}#tinh-nang">Tính năng</a></li>
                </ul>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <h6 class="landing-footer-heading">Tài khoản</h6>
                <ul class="list-unstyled m-0 d-grid gap-2 landing-footer-links">
                    @auth
                        <li><a href="{{ route('profile.show') }}">Hồ sơ</a></li>
                        @if (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin'))
                            <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        @endif
                        <li>
                            <form
                                action="{{ route('logout') }}"
                                method="POST"
                                data-confirm
                                data-confirm-title="Đăng xuất"
                                data-confirm-message="Bạn có chắc chắn muốn đăng xuất?"
                            >
                                @csrf
                                <button class="landing-footer-link-btn" type="submit">Đăng xuất</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                        <li><a href="{{ route('register') }}">Đăng ký</a></li>
                    @endauth
                </ul>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <h6 class="landing-footer-heading">Tài nguyên</h6>
                <ul class="list-unstyled m-0 d-grid gap-2 landing-footer-links">
                    <li><a href="{{ url('/api/v1/docs') }}">Swagger Docs</a></li>
                    <li><a href="{{ url('/api/v1/openapi.yaml') }}">OpenAPI YAML</a></li>
                    <li><a href="{{ url('/up') }}">Health Check</a></li>
                </ul>
            </div>
        </div>
        <p class="small text-center text-secondary mt-4 mb-0">© {{ now()->year }} {{ config('app.name', 'NextApp') }}. All rights reserved.</p>
    </div>
</footer>
