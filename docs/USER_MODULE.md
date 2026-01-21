# User Module Documentation

Tài liệu hướng dẫn sử dụng User Module với đầy đủ các tính năng CRUD, Profile Management, Password Management và Avatar Upload.

## 📋 Tổng quan

User Module bao gồm:
- ✅ **CRUD Operations** - Create, Read, Update, Delete users
- ✅ **Profile Management** - Quản lý profile của user đã đăng nhập
- ✅ **Password Management** - Đổi mật khẩu và reset password
- ✅ **Avatar Upload** - Upload và quản lý avatar của user

## 🔐 Authentication & Authorization

### Permission Required

- **User Management:** Yêu cầu permission `manage users` (chỉ admin)
- **Profile Management:** Không cần permission (user có thể quản lý profile của chính họ)
- **Password Management:** Không cần permission (user có thể đổi mật khẩu của chính họ)

## 📦 API Endpoints

### 1. User CRUD Operations (Admin only)

#### Get All Users

```http
GET /api/users
Authorization: Bearer {token}
```

**Query Parameters:**
- `per_page` (optional): Số lượng users mỗi trang (default: 15)
- `search` (optional): Tìm kiếm theo name hoặc email

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "avatar": "avatars/abc123.jpg",
            "roles": ["user"],
            "permissions": ["view content"]
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
}
```

#### Get User by ID

```http
GET /api/users/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "avatars/abc123.jpg",
        "roles": ["user"],
        "permissions": ["view content"]
    }
}
```

#### Create User

```http
POST /api/users
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "avatar": <file> // optional
}
```

**Response:**
```json
{
    "message": "User created successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "avatars/abc123.jpg",
        "roles": [],
        "permissions": []
    }
}
```

#### Update User

```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "avatar": <file> // optional
}
```

**Response:**
```json
{
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "name": "John Updated",
        "email": "john.updated@example.com",
        "avatar": "avatars/new-avatar.jpg",
        "roles": ["user"],
        "permissions": ["view content"]
    }
}
```

#### Delete User

```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "User deleted successfully"
}
```

### 2. Profile Management (Authenticated User)

#### Get Profile

```http
GET /api/profile
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "avatars/abc123.jpg",
        "roles": ["user"],
        "permissions": ["view content"]
    }
}
```

#### Update Profile

```http
PUT /api/profile
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "avatar": <file> // optional
}
```

**Response:**
```json
{
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "name": "John Updated",
        "email": "john.updated@example.com",
        "avatar": "avatars/new-avatar.jpg",
        "roles": ["user"],
        "permissions": ["view content"]
    }
}
```

### 3. Avatar Management

#### Upload Avatar (Profile)

```http
POST /api/profile/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "avatar": <file>
}
```

**File Requirements:**
- Type: jpeg, png, jpg, gif
- Max size: 2MB

**Response:**
```json
{
    "message": "Avatar uploaded successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "avatars/new-avatar.jpg",
        "roles": ["user"],
        "permissions": ["view content"]
    }
}
```

#### Delete Avatar (Profile)

```http
DELETE /api/profile/avatar
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "Avatar deleted successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": null,
        "roles": ["user"],
        "permissions": ["view content"]
    }
}
```

#### Upload Avatar (Admin - for any user)

```http
POST /api/users/{id}/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "avatar": <file>
}
```

#### Delete Avatar (Admin - for any user)

```http
DELETE /api/users/{id}/avatar
Authorization: Bearer {token}
```

### 4. Password Management

#### Change Password (Authenticated User)

```http
POST /api/password/change
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "oldpassword123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
    "message": "Password changed successfully"
}
```

#### Forgot Password (Public)

```http
POST /api/password/forgot
Content-Type: application/json

{
    "email": "user@example.com"
}
```

**Response:**
```json
{
    "message": "Password reset link sent to your email."
}
```

**Note:** Email sẽ được gửi với password reset link (cần cấu hình mail trong `.env`).

#### Reset Password (Public)

```http
POST /api/password/reset
Content-Type: application/json

{
    "email": "user@example.com",
    "token": "reset-token-from-email",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
    "message": "Password reset successfully. You can now login with your new password."
}
```

## 🗄️ Database Schema

### Users Table

```sql
users
- id (bigint, primary key)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- password (string)
- avatar (string, nullable) -- NEW
- remember_token (string, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

## 📁 File Storage

### Avatar Storage

Avatars được lưu trong:
- **Path:** `storage/app/public/avatars/`
- **Public URL:** `http://localhost:8000/storage/avatars/{filename}`

### Storage Link

Để truy cập public, cần tạo symbolic link:

```bash
docker-compose exec app php artisan storage:link
```

## 🔧 Cấu hình Mail (cho Password Reset)

Để sử dụng tính năng reset password, cần cấu hình mail trong `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 📝 Validation Rules

### User Creation/Update
- `name`: required, string, max 255
- `email`: required, email, unique (except current user for update)
- `password`: required (create only), confirmed, min length
- `avatar`: nullable, image, mimes:jpeg,png,jpg,gif, max:2048KB

### Password Change
- `current_password`: required, string
- `password`: required, confirmed, min length

### Password Reset
- `email`: required, email, exists in users table
- `token`: required, string (from email)
- `password`: required, confirmed, min length

## 🧪 Testing Examples

### cURL Examples

#### Get All Users (Admin)
```bash
curl -X GET "http://localhost:8000/api/users" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Create User (Admin)
```bash
curl -X POST "http://localhost:8000/api/users" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "name=John Doe" \
  -F "email=john@example.com" \
  -F "password=password123" \
  -F "password_confirmation=password123" \
  -F "avatar=@/path/to/avatar.jpg"
```

#### Update Profile
```bash
curl -X PUT "http://localhost:8000/api/profile" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "name=John Updated" \
  -F "email=john.updated@example.com" \
  -F "avatar=@/path/to/new-avatar.jpg"
```

#### Change Password
```bash
curl -X POST "http://localhost:8000/api/password/change" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "oldpassword",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

#### Forgot Password
```bash
curl -X POST "http://localhost:8000/api/password/forgot" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

## 🎯 Summary

User Module đã được implement đầy đủ với:
- ✅ CRUD operations cho users (Admin only)
- ✅ Profile management cho authenticated user
- ✅ Avatar upload/delete
- ✅ Password change
- ✅ Password reset (forgot/reset)
- ✅ File validation và storage handling
- ✅ Proper authorization với permissions

Tất cả các tính năng đã sẵn sàng để sử dụng!
