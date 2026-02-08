<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Main Sidebar Menu
    |--------------------------------------------------------------------------
    |
    | - route: route name (preferred)
    | - url: fallback URL when route is not available yet
    | - active: route patterns for active state
    | - roles: visible only for users with any of these roles
    | - permissions: visible only for users with any of these permissions
    |
    */
    'menu' => [
        [
            'key' => 'overview',
            'label' => 'Tổng quan',
            'icon' => 'grid',
            'route' => 'dashboard',
            'active' => ['dashboard'],
        ],
        [
            'key' => 'users',
            'label' => 'Người dùng',
            'icon' => 'users',
            'url' => '#',
            'roles' => ['admin'],
            'permissions' => ['manage users'],
        ],
        [
            'key' => 'products',
            'label' => 'Sản phẩm',
            'icon' => 'box',
            'url' => '#',
            'roles' => ['admin'],
        ],
        [
            'key' => 'orders',
            'label' => 'Đơn hàng',
            'icon' => 'cart',
            'url' => '#',
            'roles' => ['admin', 'manager'],
        ],
        [
            'key' => 'reports',
            'label' => 'Báo cáo',
            'icon' => 'chart',
            'url' => '#',
            'roles' => ['admin', 'manager'],
        ],
        [
            'key' => 'documents',
            'label' => 'Tài liệu',
            'icon' => 'file',
            'url' => '#',
        ],
        [
            'key' => 'settings',
            'label' => 'Cài đặt',
            'icon' => 'settings',
            'url' => '#',
            'roles' => ['admin'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar Footer Links
    |--------------------------------------------------------------------------
    */
    'footer' => [
        [
            'key' => 'home',
            'label' => 'Về trang chủ',
            'icon' => 'arrow-left',
            'route' => 'welcome',
        ],
    ],
];
