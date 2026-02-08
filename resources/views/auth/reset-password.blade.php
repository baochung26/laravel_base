@extends('layouts.guest')

@section('content')
    <h1>New password</h1>
    <p>Đặt mật khẩu mới cho tài khoản của bạn.</p>

    <form method="POST" action="{{ route('password.update') }}" data-validate novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus data-label="Email">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required data-label="Mật khẩu mới" data-min-length="8">
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required data-label="Xác nhận mật khẩu" data-match="#password">
        </div>

        <div class="actions">
            <a class="link" href="{{ route('login') }}">Quay lại đăng nhập</a>
            <button class="btn" type="submit">Reset password</button>
        </div>
    </form>
@endsection
