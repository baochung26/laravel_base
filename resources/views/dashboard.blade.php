@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Tổng quan')

@section('content')
    <h2 class="dash-title">Chào mừng trở lại, {{ auth()->user()->name }}!</h2>
    <p class="dash-subtitle">Đây là tổng quan về hệ thống của bạn</p>

    <section class="stats-grid">
        <article class="stat-card">
            <div class="stat-top">
                <span>Tổng người dùng</span>
                <svg class="dash-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
            </div>
            <p class="stat-value">12,345</p>
            <div class="stat-change"><strong>↗ +20.1%</strong> so với tháng trước</div>
        </article>

        <article class="stat-card">
            <div class="stat-top">
                <span>Sản phẩm</span>
                <svg class="dash-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg>
            </div>
            <p class="stat-value">8,234</p>
            <div class="stat-change"><strong>↗ +12.5%</strong> so với tháng trước</div>
        </article>

        <article class="stat-card">
            <div class="stat-top">
                <span>Đơn hàng</span>
                <svg class="dash-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 2-1.7L22 6H6"/></svg>
            </div>
            <p class="stat-value">5,678</p>
            <div class="stat-change"><strong>↗ +8.2%</strong> so với tháng trước</div>
        </article>

        <article class="stat-card">
            <div class="stat-top">
                <span>Doanh thu</span>
                <svg class="dash-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <p class="stat-value">$124,567</p>
            <div class="stat-change"><strong>↗ +15.3%</strong> so với tháng trước</div>
        </article>
    </section>

    <section class="dash-grid">
        <article class="panel">
            <h2>Hoạt động gần đây</h2>
            <p class="panel-subtitle">Các hoạt động mới nhất trong hệ thống</p>

            <div class="activity-item">
                <div class="activity-main">
                    <span class="dot"></span>
                    <div>
                        <strong>Người dùng mới đăng ký</strong>
                        <span>user@example.com</span>
                    </div>
                </div>
                <span class="activity-time">5 phút trước</span>
            </div>

            <div class="activity-item">
                <div class="activity-main">
                    <span class="dot"></span>
                    <div>
                        <strong>Đơn hàng mới</strong>
                        <span>Order #1234</span>
                    </div>
                </div>
                <span class="activity-time">15 phút trước</span>
            </div>

            <div class="activity-item">
                <div class="activity-main">
                    <span class="dot"></span>
                    <div>
                        <strong>Sản phẩm được cập nhật</strong>
                        <span>Product #567</span>
                    </div>
                </div>
                <span class="activity-time">30 phút trước</span>
            </div>

            <div class="activity-item">
                <div class="activity-main">
                    <span class="dot"></span>
                    <div>
                        <strong>Người dùng mới đăng ký</strong>
                        <span>user2@example.com</span>
                    </div>
                </div>
                <span class="activity-time">1 giờ trước</span>
            </div>
        </article>

        <article class="panel">
            <h2>Thống kê nhanh</h2>
            <p class="panel-subtitle">Tổng quan hệ thống</p>

            <div class="quick-list">
                <div class="quick-item">
                    <span>Hoạt động hôm nay</span>
                    <strong>1,234</strong>
                </div>
                <div class="quick-item">
                    <span>Tăng trưởng</span>
                    <strong class="positive">+15.2%</strong>
                </div>
                <div class="quick-item">
                    <span>Người dùng online</span>
                    <strong>456</strong>
                </div>
                <div class="quick-item">
                    <span>Đơn hàng hôm nay</span>
                    <strong>89</strong>
                </div>
            </div>
        </article>
    </section>
@endsection
