@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')
@section('page_title', 'Chỉnh sửa người dùng')

@section('content')
    <section class="dash-users-edit">
        <div class="dash-users-edit-head">
            <h2 class="dash-title">Chỉnh sửa người dùng</h2>
            <a href="{{ route('dashboard.users.index') }}" class="dash-users-edit-back">← Quay lại danh sách</a>
        </div>

        <article class="dash-users-edit-card">
            <form method="POST" action="{{ route('dashboard.users.update', $targetUser) }}" data-validate novalidate>
                @csrf
                @method('PUT')

                <div class="dash-users-edit-grid">
                    <div class="dash-filter-field">
                        <label for="edit_name">Tên</label>
                        <input id="edit_name" name="name" type="text" value="{{ old('name', $targetUser->name) }}" required data-label="Tên">
                    </div>

                    <div class="dash-filter-field">
                        <label for="edit_email">Email</label>
                        <input id="edit_email" name="email" type="email" value="{{ old('email', $targetUser->email) }}" required data-label="Email">
                    </div>

                    <div class="dash-filter-field">
                        <label for="edit_role">Vai trò</label>
                        <select id="edit_role" name="role" data-label="Vai trò">
                            <option value="">Không thay đổi</option>
                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role }}"
                                    @selected(old('role', $targetUser->roles->pluck('name')->first()) === $role)
                                >
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="dash-filter-field">
                        <label>Ngày tạo</label>
                        <input type="text" value="{{ optional($targetUser->created_at)->format('d/m/Y H:i') }}" disabled>
                    </div>
                </div>

                <div class="dash-users-edit-actions">
                    <button type="submit" class="dash-add-user-btn">Lưu thay đổi</button>
                </div>
            </form>
        </article>
    </section>
@endsection

