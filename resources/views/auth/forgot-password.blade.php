@extends('layouts.guest')

@section('content')
    <div class="auth-top">
        <h1 class="auth-brand">{{ config('app.name', 'NextApp') }}</h1>
        <a class="auth-back" href="{{ route('welcome') }}">← Về trang chủ</a>
    </div>

    <h2 class="auth-title">Quên mật khẩu</h2>
    <p class="auth-subtitle">Nhập email của bạn để nhận link đặt lại mật khẩu.</p>

    <form method="POST" action="{{ route('password.email') }}" id="forgot-password-form" data-validate novalidate>
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="email@example.com" data-label="Email">
        </div>

        <button class="auth-submit" type="submit">Gửi link đặt lại mật khẩu</button>
    </form>

    <p class="auth-register">
        <a href="{{ route('login') }}">Quay lại đăng nhập</a>
    </p>
@endsection
