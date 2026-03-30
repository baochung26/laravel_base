@extends('layouts.guest')

@section('content')
    <div class="auth-top">
        <h1 class="auth-brand">{{ config('app.name', 'NextApp') }}</h1>
        <a class="auth-back" href="{{ route('welcome') }}">← Về trang chủ</a>
    </div>

    <h2 class="auth-title">Đăng ký</h2>
    <p class="auth-subtitle">Tạo tài khoản mới để bắt đầu sử dụng.</p>

    <form method="POST" action="{{ route('register') }}" id="register-form" data-validate novalidate>
        @csrf

        <div class="field">
            <label for="name">Tên</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nguyen Van A" data-label="Tên">
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="admin@example.com" data-label="Email">
        </div>

        <div class="field">
            <label for="password">Mật khẩu</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="••••••••" data-label="Mật khẩu" data-min-length="8">
        </div>

        <div class="field">
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="••••••••" data-label="Xác nhận mật khẩu" data-match="#password">
        </div>

        <button class="auth-submit" type="submit" id="register-submit-btn" data-loading-text="Đang đăng ký...">Đăng ký</button>
    </form>

    <p class="auth-register">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập ngay</a>
    </p>
@endsection

@push('scripts')
    @vite('resources/js/pages/auth.js')
@endpush
