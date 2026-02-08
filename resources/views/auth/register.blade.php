@extends('layouts.guest')

@section('content')
    <h1>Create account</h1>
    <p>Tạo tài khoản để sử dụng web module.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <div class="actions">
            <a class="link" href="{{ route('login') }}">Đã có tài khoản?</a>
            <button class="btn" type="submit">Register</button>
        </div>
    </form>
@endsection
