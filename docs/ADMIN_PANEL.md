# Admin Panel Documentation - Filament

Hướng dẫn về Admin Panel sử dụng Filament để quản lý Users, Roles, và Permissions.

## 📋 Tổng quan

Admin Panel được xây dựng với **Filament 3.x**, một admin panel framework hiện đại, miễn phí cho Laravel.

### Tính năng

- ✅ **User Management** - Quản lý users với CRUD đầy đủ
- ✅ **Role Management** - Quản lý roles và permissions
- ✅ **Permission Management** - Quản lý permissions
- ✅ **Dashboard** - Statistics overview
- ✅ **Tích hợp Spatie Permission** - Full integration với RBAC
- ✅ **Soft Deletes Support** - Hỗ trợ soft deletes cho users
- ✅ **Avatar Upload** - Upload và quản lý avatar

## 🚀 Cài đặt

### Bước 1: Install Filament

```bash
composer require filament/filament:"^3.2"
```

### Bước 2: Publish Filament Assets

```bash
php artisan filament:install --panels
```

### Bước 3: Tạo Admin User

```bash
php artisan make:filament-user
```

Hoặc sử dụng seeder để tạo admin user:

```bash
php artisan db:seed --class=DemoSeeder
```

### Bước 4: Truy cập Admin Panel

Sau khi cài đặt, truy cập:
- **Admin Panel:** `http://localhost:8000/admin`
- **Login:** Sử dụng email/password của admin user

## 📁 Cấu trúc

```
app/
├── Filament/
│   ├── Pages/
│   │   └── Dashboard.php
│   ├── Resources/
│   │   ├── UserResource.php
│   │   │   └── Pages/
│   │   │       ├── ListUsers.php
│   │   │       ├── CreateUser.php
│   │   │       ├── EditUser.php
│   │   │       └── ViewUser.php
│   │   ├── RoleResource.php
│   │   │   └── Pages/
│   │   │       ├── ListRoles.php
│   │   │       ├── CreateRole.php
│   │   │       └── EditRole.php
│   │   └── PermissionResource.php
│   │       └── Pages/
│   │           ├── ListPermissions.php
│   │           ├── CreatePermission.php
│   │           └── EditPermission.php
│   └── Widgets/
│       └── StatsOverview.php
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php
```

## 🎨 Resources

### UserResource

**Features:**
- ✅ List users với search, filters, sorting
- ✅ Create/Edit/View/Delete users
- ✅ Avatar upload với image editor
- ✅ Role assignment
- ✅ Soft deletes support (restore, force delete)
- ✅ Email verification status

**Form Fields:**
- Name (required)
- Email (required, unique)
- Password (required on create, optional on edit)
- Avatar (image upload, max 2MB)
- Roles (multiple select)

**Table Columns:**
- Avatar (circular image)
- Name (searchable, sortable)
- Email (searchable, sortable, copyable)
- Roles (badges)
- Email Verified (icon)
- Created/Updated dates

**Filters:**
- Trashed (soft deleted)
- Roles filter
- Verified filter

### RoleResource

**Features:**
- ✅ List roles với search, filters
- ✅ Create/Edit/Delete roles
- ✅ Permission assignment
- ✅ User count display

**Form Fields:**
- Name (required, unique, alpha-dash)
- Guard Name (default: web)
- Permissions (checkbox list, searchable)

**Table Columns:**
- Name (badge)
- Guard Name (badge)
- Permissions Count (badge)
- Users Count (badge)
- Created date

**Filters:**
- Guard Name filter

### PermissionResource

**Features:**
- ✅ List permissions với search, filters
- ✅ Create/Edit/Delete permissions
- ✅ Role count display

**Form Fields:**
- Name (required, unique, alpha-dash)
- Guard Name (default: web)

**Table Columns:**
- Name (badge)
- Guard Name (badge)
- Roles Count (badge)
- Created date

**Filters:**
- Guard Name filter

## 📊 Dashboard

Dashboard hiển thị statistics overview:
- **Total Users** - Tổng số users
- **Total Roles** - Tổng số roles
- **Total Permissions** - Tổng số permissions

## 🔐 Authentication

Filament sử dụng Laravel's default authentication:
- Login form tại `/admin/login`
- Logout button trong user menu
- Password reset (nếu cần)

## 🎯 Customization

### Thay đổi Brand Name

Trong `AdminPanelProvider.php`:

```php
->brandName('Your Brand Name')
```

### Thay đổi Logo

```php
->brandLogo(asset('images/logo.svg'))
->favicon(asset('images/favicon.ico'))
```

### Thay đổi Colors

```php
->colors([
    'primary' => Color::Blue,
    'danger' => Color::Red,
    'success' => Color::Green,
    'warning' => Color::Orange,
])
```

### Thêm Navigation Groups

Trong Resource:

```php
protected static ?string $navigationGroup = 'Settings';
```

### Thay đổi Navigation Sort

```php
protected static ?int $navigationSort = 1;
```

## 🔒 Authorization

### Role-based Access

Để restrict access theo role, thêm vào Resource:

```php
public static function canViewAny(): bool
{
    return auth()->user()->hasRole('admin');
}
```

### Permission-based Access

```php
public static function canViewAny(): bool
{
    return auth()->user()->can('manage users');
}
```

## 📝 Best Practices

### 1. Resource Organization

✅ **DO:**
- Group related resources với navigation groups
- Use consistent naming conventions
- Add proper form validation
- Include search và filters

❌ **DON'T:**
- Don't expose sensitive data
- Don't allow mass assignment without validation
- Don't forget to handle soft deletes

### 2. Form Validation

```php
Forms\Components\TextInput::make('email')
    ->email()
    ->required()
    ->unique(ignoreRecord: true)
    ->maxLength(255)
```

### 3. Table Optimization

```php
Tables\Columns\TextColumn::make('email')
    ->searchable()
    ->sortable()
    ->copyable()
```

## 🛠️ Commands

### Create New Resource

```bash
php artisan make:filament-resource ModelName
```

### Create New Page

```bash
php artisan make:filament-page PageName
```

### Create New Widget

```bash
php artisan make:filament-widget WidgetName
```

## 📚 Resources

- [Filament Documentation](https://filamentphp.com/docs)
- [Filament Forms](https://filamentphp.com/docs/forms)
- [Filament Tables](https://filamentphp.com/docs/tables)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)

## 🎯 Next Steps

Có thể mở rộng Admin Panel với:
- Custom pages
- More widgets
- Reports
- Activity logs
- File management
- Settings page
