@extends('layouts.guest')

@section('content')
    <h1>Reset password</h1>
    <p>Nhập email, hệ thống sẽ gửi link đặt lại mật khẩu.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="actions">
            <a class="link" href="{{ route('login') }}">Quay lại đăng nhập</a>
            <button class="btn" type="submit">Send reset link</button>
        </div>
    </form>
@endsection
