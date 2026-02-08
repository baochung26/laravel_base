@extends('layouts.guest')

@section('content')
    <div class="auth-top">
        <h1 class="auth-brand">{{ config('app.name', 'NextApp') }}</h1>
        <a class="auth-back" href="{{ route('welcome') }}">← Về trang chủ</a>
    </div>

    <h2 class="auth-title">Đăng nhập</h2>
    <p class="auth-subtitle">Nhập thông tin của bạn để đăng nhập vào tài khoản.</p>

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="admin@example.com">
        </div>

        <div class="field">
            <label for="password">Mật khẩu</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••">
        </div>

        <div class="auth-row">
            <label style="display:flex;align-items:center;gap:8px;color:#9db0c9;">
                <input type="checkbox" name="remember" style="width:16px;height:16px;">
                Ghi nhớ đăng nhập
            </label>
            <a class="auth-forgot" href="{{ route('password.request') }}">Quên mật khẩu?</a>
        </div>

        <button class="auth-submit" type="submit" id="submit-btn" data-loading-text="Đang đăng nhập...">Đăng nhập</button>
    </form>

    <div class="auth-divider">HOẶC TIẾP TỤC VỚI</div>

    <div class="google-wrap">
        @if (filled(config('services.google.client_id')))
            <div id="g_id_onload"
                 data-client_id="{{ config('services.google.client_id') }}"
                 data-callback="onGoogleSignInCallback">
            </div>

            <div class="g_id_signin"
                 data-type="standard"
                 data-theme="outline"
                 data-size="large"
                 data-shape="rectangular"
                 data-text="signin_with"
                 data-logo_alignment="left"
                 data-width="100%">
            </div>

            <form id="google-login-form" method="POST" action="{{ route('login.google') }}" style="display:none;">
                @csrf
                <input type="hidden" name="id_token" id="google-id-token">
            </form>
        @else
            <button class="auth-submit" type="button" disabled>Đăng nhập với Google</button>
            <p class="google-login-note">Thiếu cấu hình `GOOGLE_CLIENT_ID` trong file `.env`.</p>
        @endif
    </div>

    <p class="auth-register">
        Chưa có tài khoản?
        <a href="{{ route('register') }}">Đăng ký ngay</a>
    </p>

@endsection

@push('scripts')
    @vite('resources/js/pages/auth-login.js')
    @if (filled(config('services.google.client_id')))
        <script src="https://accounts.google.com/gsi/client" async defer></script>
    @endif
@endpush
