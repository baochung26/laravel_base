@extends('layouts.landing')

@section('title', 'Hồ sơ cá nhân')

@push('styles')
    @vite('resources/css/profile.css')
@endpush

@section('content')
    @php
        $resolvedTab = $activeTab;
        if ($errors->getBag('passwordUpdate')->any()) {
            $resolvedTab = 'password';
        } elseif ($errors->getBag('profileUpdate')->any()) {
            $resolvedTab = 'profile';
        }
    @endphp

    <section class="profile-page py-5">
        <div class="container landing-container">
            <header class="mb-4 mb-lg-5">
                <h1 class="profile-title mb-2">Hồ sơ cá nhân</h1>
                <p class="profile-subtitle mb-0">Quản lý thông tin tài khoản và cài đặt bảo mật</p>
            </header>

            <div class="row g-4 g-lg-4 align-items-start">
                <div class="col-12 col-lg-4">
                    <article class="profile-card profile-summary-card">
                        <div class="profile-avatar-wrap">
                            @if ($user->avatar_url)
                                <img
                                    src="{{ $user->avatar_url }}"
                                    alt="Avatar {{ $user->name }}"
                                    class="profile-avatar-image"
                                >
                            @else
                                <span class="profile-avatar-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                            @endif
                            <span class="profile-avatar-camera" title="Chọn ảnh khi lưu thông tin">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 8a2 2 0 0 1 2-2h2l1-2h6l1 2h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z" stroke="currentColor" stroke-width="1.6"/>
                                    <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                            </span>
                        </div>

                        <h2 class="profile-user-name">{{ $user->name }}</h2>
                        <p class="profile-user-email">{{ $user->email }}</p>

                        <ul class="profile-meta list-unstyled mb-0">
                            <li>
                                <span class="profile-meta-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 7.5h16v9H4v-9Z" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="m4.5 8 7.5 6 7.5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>Email: <strong>{{ $user->email }}</strong></span>
                            </li>
                            <li>
                                <span class="profile-meta-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 4 5 7v5c0 4 3 7 7 8 4-1 7-4 7-8V7l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>Vai trò: <strong>{{ ucfirst($primaryRole ?? 'user') }}</strong></span>
                            </li>
                        </ul>
                    </article>
                </div>

                <div class="col-12 col-lg-8">
                    <ul class="nav profile-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $resolvedTab === 'profile' ? 'active' : '' }}"
                                id="profile-info-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#profile-info-pane"
                                type="button"
                                role="tab"
                                aria-controls="profile-info-pane"
                                aria-selected="{{ $resolvedTab === 'profile' ? 'true' : 'false' }}"
                            >
                                Thông tin cá nhân
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $resolvedTab === 'password' ? 'active' : '' }}"
                                id="profile-password-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#profile-password-pane"
                                type="button"
                                role="tab"
                                aria-controls="profile-password-pane"
                                aria-selected="{{ $resolvedTab === 'password' ? 'true' : 'false' }}"
                            >
                                Đổi mật khẩu
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div
                            class="tab-pane fade {{ $resolvedTab === 'profile' ? 'show active' : '' }}"
                            id="profile-info-pane"
                            role="tabpanel"
                            aria-labelledby="profile-info-tab"
                            tabindex="0"
                        >
                            <article class="profile-card">
                                <h3 class="profile-card-title">Thông tin cá nhân</h3>
                                <p class="profile-card-subtitle">Cập nhật thông tin tài khoản của bạn</p>

                                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-form" data-validate novalidate>
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label profile-label" for="name">Tên</label>
                                            <input
                                                id="name"
                                                name="name"
                                                type="text"
                                                class="form-control profile-input @error('name', 'profileUpdate') is-invalid @enderror"
                                                value="{{ old('name', $user->name) }}"
                                                data-label="Tên"
                                                required
                                            >
                                            @error('name', 'profileUpdate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label profile-label" for="email">Email</label>
                                            <input
                                                id="email"
                                                type="email"
                                                class="form-control profile-input"
                                                value="{{ $user->email }}"
                                                disabled
                                                readonly
                                            >
                                            <p class="profile-help mb-0">Email không thể thay đổi tại đây. Liên hệ admin nếu cần.</p>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label profile-label" for="avatar">Ảnh đại diện</label>
                                            <input
                                                id="avatar"
                                                name="avatar"
                                                type="file"
                                                accept="image/png,image/jpeg,image/webp"
                                                class="form-control profile-input @error('avatar', 'profileUpdate') is-invalid @enderror"
                                            >
                                            @error('avatar', 'profileUpdate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit" class="btn profile-primary-btn mt-4">Lưu thay đổi</button>
                                </form>
                            </article>
                        </div>

                        <div
                            class="tab-pane fade {{ $resolvedTab === 'password' ? 'show active' : '' }}"
                            id="profile-password-pane"
                            role="tabpanel"
                            aria-labelledby="profile-password-tab"
                            tabindex="0"
                        >
                            <article class="profile-card">
                                <h3 class="profile-card-title">Đổi mật khẩu</h3>
                                <p class="profile-card-subtitle">Cập nhật mật khẩu để tăng bảo mật tài khoản</p>

                                <form method="POST" action="{{ route('profile.password.update') }}" class="profile-form" data-validate novalidate>
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label profile-label" for="current_password">Mật khẩu hiện tại</label>
                                            <input
                                                id="current_password"
                                                name="current_password"
                                                type="password"
                                                class="form-control profile-input @error('current_password', 'passwordUpdate') is-invalid @enderror"
                                                data-label="Mật khẩu hiện tại"
                                                data-min-length="8"
                                                required
                                            >
                                            @error('current_password', 'passwordUpdate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label profile-label" for="password">Mật khẩu mới</label>
                                            <input
                                                id="password"
                                                name="password"
                                                type="password"
                                                class="form-control profile-input @error('password', 'passwordUpdate') is-invalid @enderror"
                                                data-label="Mật khẩu mới"
                                                data-min-length="8"
                                                required
                                            >
                                            @error('password', 'passwordUpdate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label profile-label" for="password_confirmation">Xác nhận mật khẩu</label>
                                            <input
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                type="password"
                                                class="form-control profile-input"
                                                data-label="Xác nhận mật khẩu"
                                                data-match="#password"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <button type="submit" class="btn profile-primary-btn mt-4">Đổi mật khẩu</button>
                                </form>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
