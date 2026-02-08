@extends('layouts.app')

@section('title', 'Dashboard Users')
@section('page_title', 'Người dùng')

@section('content')
    <section class="dash-users-header">
        <div>
            <h2 class="dash-title">Quản lý người dùng</h2>
            <p class="dash-subtitle">Quản lý và theo dõi tất cả người dùng trong hệ thống</p>
        </div>
        <a href="{{ route('dashboard.users.create') }}" class="dash-add-user-btn">
            + Thêm người dùng
        </a>
    </section>

    <section class="dash-users-stats">
        <article class="dash-users-stat-card">
            <h3>Tổng người dùng</h3>
            <p>{{ number_format($stats['total']) }}</p>
            <span>Tổng số kết quả</span>
        </article>
        <article class="dash-users-stat-card">
            <h3>Đang hoạt động</h3>
            <p>{{ number_format($stats['active']) }}</p>
            <span>{{ $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100) : 0 }}% trong danh sách</span>
        </article>
        <article class="dash-users-stat-card">
            <h3>Không hoạt động</h3>
            <p>{{ number_format($stats['inactive']) }}</p>
            <span>Trong danh sách</span>
        </article>
        <article class="dash-users-stat-card">
            <h3>Admin</h3>
            <p>{{ number_format($stats['admin']) }}</p>
            <span>Trong danh sách</span>
        </article>
    </section>

    <section class="dash-users-panel">
        <div class="dash-users-panel-head">
            <div>
                <h3>Danh sách người dùng</h3>
                <p>Tất cả người dùng đã đăng ký trong hệ thống</p>
            </div>

            <form method="GET" action="{{ route('dashboard.users.index') }}" class="dash-users-filters" data-loading data-auto-submit>
                <div class="dash-filter-field dash-filter-search">
                    <input
                        id="user_q"
                        type="text"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="Tìm kiếm email, tên..."
                    >
                </div>

                <div class="dash-filter-field">
                    <select name="role" aria-label="Lọc theo vai trò">
                        <option value="">Tất cả vai trò</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="dash-filter-field">
                    <select name="status" aria-label="Lọc theo trạng thái">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" @selected($filters['status'] === 'active')>Hoạt động</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Không hoạt động</option>
                    </select>
                </div>

                <div class="dash-filter-field">
                    <select name="sort_by" aria-label="Sắp xếp theo trường">
                        <option value="created_at" @selected($filters['sort_by'] === 'created_at')>Ngày tạo</option>
                        <option value="name" @selected($filters['sort_by'] === 'name')>Tên</option>
                        <option value="email" @selected($filters['sort_by'] === 'email')>Email</option>
                    </select>
                </div>

                <div class="dash-filter-field">
                    <select name="sort_dir" aria-label="Chiều sắp xếp">
                        <option value="desc" @selected($filters['sort_dir'] === 'desc')>Giảm dần</option>
                        <option value="asc" @selected($filters['sort_dir'] === 'asc')>Tăng dần</option>
                    </select>
                </div>

                <a href="{{ route('dashboard.users.index') }}" class="dash-filter-reset">Reset</a>
            </form>
        </div>

        <div class="dash-users-table-wrap">
            <table class="dash-users-table">
                <thead>
                <tr>
                    <th>Người dùng</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $row)
                    @php
                        $isActive = ! is_null($row->email_verified_at);
                        $firstRole = $row->roles->pluck('name')->first() ?? 'user';
                    @endphp
                    <tr>
                        <td>
                            <div class="dash-users-user-cell">
                                <span class="dash-users-avatar">{{ strtoupper(substr($row->name ?? 'U', 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $row->name }}</strong>
                                    <small>{{ $row->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $row->email }}</td>
                        <td>
                            <span class="dash-users-role">{{ ucfirst($firstRole) }}</span>
                        </td>
                        <td>
                            <span class="dash-users-status {{ $isActive ? 'active' : 'inactive' }}">
                                {{ $isActive ? 'Hoạt động' : 'Không hoạt động' }}
                            </span>
                        </td>
                        <td>{{ optional($row->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <div class="dropdown dash-users-actions-dropdown">
                                <button
                                    type="button"
                                    class="dash-users-action dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    ⋮
                                </button>

                                <div class="dropdown-menu dropdown-menu-end dash-users-action-menu">
                                    <a class="dropdown-item" href="{{ route('dashboard.users.edit', $row) }}">
                                        <span>✎</span>
                                        Chỉnh sửa
                                    </a>

                                    <form method="POST" action="{{ route('dashboard.users.status.update', $row) }}" data-loading>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $isActive ? 'inactive' : 'active' }}">
                                        <button type="submit" class="dropdown-item">
                                            <span>{{ $isActive ? '⊗' : '✓' }}</span>
                                            {{ $isActive ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('dashboard.users.destroy', $row) }}"
                                        data-confirm
                                        data-confirm-title="Xóa người dùng"
                                        data-confirm-message="Bạn có chắc chắn muốn xóa người dùng này?"
                                        data-loading
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item danger">
                                            <span>🗑</span>
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="dash-users-empty">Không có dữ liệu người dùng phù hợp bộ lọc.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="dash-users-pagination">
                <span>Hiển thị {{ $users->firstItem() }}-{{ $users->lastItem() }} / {{ $users->total() }}</span>
                <div class="dash-users-pagination-right">
                    <div class="dash-users-pagination-links">
                        @if ($users->currentPage() > 1)
                            <a href="{{ $users->url(1) }}" aria-label="Trang đầu">«</a>
                        @else
                            <span class="disabled" aria-label="Trang đầu">«</span>
                        @endif

                        @if ($users->onFirstPage())
                            <span class="disabled" aria-label="Trang trước">‹</span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" aria-label="Trang trước">‹</a>
                        @endif

                        @foreach ($users->getUrlRange(max(1, $users->currentPage() - 1), min($users->lastPage(), $users->currentPage() + 1)) as $page => $url)
                            @if ($page === $users->currentPage())
                                <span class="active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" aria-label="Trang sau">›</a>
                        @else
                            <span class="disabled" aria-label="Trang sau">›</span>
                        @endif

                        @if ($users->currentPage() < $users->lastPage())
                            <a href="{{ $users->url($users->lastPage()) }}" aria-label="Trang cuối">»</a>
                        @else
                            <span class="disabled" aria-label="Trang cuối">»</span>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('dashboard.users.index') }}" class="dash-users-go-page" data-loading>
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                        <input type="hidden" name="role" value="{{ $filters['role'] }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] }}">
                        <input type="hidden" name="sort_by" value="{{ $filters['sort_by'] }}">
                        <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}">

                        <label for="go_page" class="dash-users-go-label">Trang</label>
                        <input
                            id="go_page"
                            type="number"
                            name="page"
                            min="1"
                            max="{{ $users->lastPage() }}"
                            value="{{ $users->currentPage() }}"
                        >
                        <button type="submit" aria-label="Đi đến trang">Đi</button>
                    </form>
                </div>
            </div>
        @endif
    </section>
@endsection
