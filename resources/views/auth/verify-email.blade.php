@extends('layouts.guest')

@section('content')
    <h1>Verify email</h1>
    <p>Kiểm tra email để xác minh tài khoản trước khi truy cập các trang yêu cầu xác minh.</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <div class="actions">
            <a class="link" href="{{ route('welcome') }}">Về trang chủ</a>
            <button class="btn" type="submit">Gửi lại email xác minh</button>
        </div>
    </form>
@endsection
