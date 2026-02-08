@extends('layouts.guest')

@section('content')
    <h1>Sign in</h1>
    <p>Đăng nhập để truy cập phần quản trị web.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>

        <div class="row">
            <label>
                <input type="checkbox" name="remember">
                Remember me
            </label>
            <a class="link" href="{{ route('password.request') }}">Quên mật khẩu?</a>
        </div>

        <div class="actions">
            <a class="link" href="{{ route('register') }}">Tạo tài khoản</a>
            <button class="btn" type="submit">Login</button>
        </div>
    </form>
@endsection
