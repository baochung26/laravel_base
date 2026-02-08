@extends('layouts.app')

@section('title', 'Thêm người dùng')
@section('page_title', 'Thêm người dùng')

@section('content')
    <section class="dash-users-edit">
        <div class="dash-users-edit-head">
            <h2 class="dash-title">Thêm người dùng</h2>
            <a href="{{ route('dashboard.users.index') }}" class="dash-users-edit-back">← Quay lại danh sách</a>
        </div>

        <article class="dash-users-edit-card">
            <form method="POST" action="{{ route('dashboard.users.store') }}" data-validate novalidate autocomplete="off">
                @csrf

                <div class="dash-users-edit-grid">
                    <div class="dash-filter-field">
                        <label for="create_name">Tên</label>
                        <input id="create_name" name="name" type="text" value="{{ old('name') }}" required data-label="Tên" autocomplete="off">
                    </div>

                    <div class="dash-filter-field">
                        <label for="create_email">Email</label>
                        <input id="create_email" name="email" type="email" value="{{ old('email') }}" required data-label="Email" autocomplete="new-email">
                    </div>

                    <div class="dash-filter-field">
                        <label for="create_password">Mật khẩu</label>
                        <input id="create_password" name="password" type="password" required data-label="Mật khẩu" data-min-length="8" autocomplete="new-password">
                    </div>

                    <div class="dash-filter-field">
                        <label for="create_password_confirmation">Xác nhận mật khẩu</label>
                        <input
                            id="create_password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            data-label="Xác nhận mật khẩu"
                            data-match="#create_password"
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="dash-filter-field">
                        <label for="create_role">Vai trò</label>
                        <select id="create_role" name="role" data-label="Vai trò" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role', 'user') === $role)>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="dash-filter-field">
                        <label for="create_status">Trạng thái</label>
                        <select id="create_status" name="status" data-label="Trạng thái" required>
                            <option value="active" @selected(old('status', 'active') === 'active')>Hoạt động</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Không hoạt động</option>
                        </select>
                    </div>
                </div>

                <div class="dash-users-edit-actions">
                    <button type="submit" class="dash-add-user-btn">+ Tạo người dùng</button>
                </div>
            </form>
        </article>
    </section>
@endsection
