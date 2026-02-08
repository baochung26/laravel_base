@extends('layouts.app')

@section('content')
    <section class="panel">
        <h1 style="margin-top:0;">Dashboard</h1>
        <p class="muted">Web module đã hoạt động với session auth, email verification và password reset.</p>
        <hr style="border:0;border-top:1px solid #e2e8f0;margin:18px 0;">
        <p><strong>User:</strong> {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
        <p><strong>Email verified:</strong> {{ auth()->user()->hasVerifiedEmail() ? 'Yes' : 'No' }}</p>
    </section>
@endsection
