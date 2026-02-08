# Web Template Structure

## Stack hiện tại

- Build tool: Vite (Laravel standard)
- CSS framework: Bootstrap 5.3 (npm package, bundled by Vite)
- Template engine: Laravel Blade

## Cấu trúc assets

- CSS:
  - `resources/css/landing.css`
  - `resources/css/auth.css`
  - `resources/css/dashboard.css`
- JS:
  - `resources/js/app.js`
  - `resources/js/pages/auth-login.js`
- Build config:
  - `vite.config.js`
  - `package.json`

## Cấu trúc template landing

- Layout chính: `resources/views/layouts/landing.blade.php`
- Header partial: `resources/views/partials/landing/header.blade.php`
- Footer partial: `resources/views/partials/landing/footer.blade.php`
- Trang home: `resources/views/welcome.blade.php`

## Quy ước mở rộng

- Tất cả trang landing mới nên `@extends('layouts.landing')`.
- Header/Footer chỉ sửa trong partial để áp dụng toàn site.
- CSS/JS dùng qua `@vite(...)` trong layout.
- Không để CSS/JS inline trong các file Blade lớn.
