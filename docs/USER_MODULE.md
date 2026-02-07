# User Module Documentation

Hướng dẫn User Module: CRUD users, Profile, Password, Avatar. Tất cả endpoint dùng prefix **`/api/v1`**.

## Tổng quan

- **User CRUD** – Create, Read, Update, Delete (cần permission `manage users`, thường là admin).
- **Profile** – Xem/sửa profile user đang đăng nhập (không cần permission).
- **Password** – Đổi mật khẩu, forgot/reset (theo từng endpoint).
- **Avatar** – Upload/xóa avatar (user hoặc admin tùy route).

## Permission

| Nhóm | Permission |
|------|------------|
| User CRUD, avatar admin | `manage users` |
| Profile, đổi mật khẩu | Không (chỉ user hiện tại) |

---

## API Endpoints (base: `/api/v1`)

### 1. User CRUD (Admin – permission: `manage users`)

#### Get all users (always paginated)

```http
GET /api/v1/users?per_page=10&page=1&search=john
Authorization: Bearer {token}
```

**Query:** `per_page` (default 15, max 100), `page`, `search` (optional).

**Response (200):**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "meta": {
    "request_id": "...",
    "timestamp": "...",
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "last_page": 10,
    "from": 1,
    "to": 10
  },
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar": "http://localhost:8000/storage/avatars/abc.jpg",
      "roles": ["user"],
      "permissions": ["view content"]
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/users?page=1",
    "last": "http://localhost:8000/api/v1/users?page=10",
    "prev": null,
    "next": "http://localhost:8000/api/v1/users?page=2"
  }
}
```

#### Get user by ID

```http
GET /api/v1/users/{id}
Authorization: Bearer {token}
```

**Response (200):** `success`, `message`, `data` (user object với roles, permissions).

#### Create user

```http
POST /api/v1/users
Authorization: Bearer {token}
Content-Type: multipart/form-data

name=John Doe
email=john@example.com
password=password123
password_confirmation=password123
avatar=<file>   (optional)
```

**Response (201):** `success`, `message`, `data` (user created).

#### Update user

```http
PUT /api/v1/users/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data

name=John Updated
email=john.updated@example.com
avatar=<file>   (optional)
```

**Response (200):** `success`, `message`, `data` (user). Example:
```json
{
  "success": true,
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

#### Delete user

```http
DELETE /api/v1/users/{id}
Authorization: Bearer {token}
```

**Response (200):** `success`, `message`, `data`: null.

---

### 2. Profile (authenticated user – own profile)

#### Get profile

```http
GET /api/v1/profile
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

#### Update profile

```http
PUT /api/v1/profile
Authorization: Bearer {token}
Content-Type: multipart/form-data

name=John Updated
email=john.updated@example.com
avatar=<file>   (optional)
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

#### Upload avatar (own profile)

```http
POST /api/v1/profile/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

avatar=<file>
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

#### Delete avatar (own profile)

```http
DELETE /api/v1/profile/avatar
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
POST /api/v1/users/{id}/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "avatar": <file>
}
```

#### Delete Avatar (Admin - for any user)

```http
DELETE /api/v1/users/{id}/avatar
Authorization: Bearer {token}
```

### 4. Password Management

#### Change Password (Authenticated User)

```http
POST /api/v1/password/change
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
POST /api/v1/password/forgot
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
POST /api/v1/password/reset
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
curl -X GET "http://localhost:8000/api/v1/users" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Create User (Admin)
```bash
curl -X POST "http://localhost:8000/api/v1/users" \
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
curl -X PUT "http://localhost:8000/api/v1/profile" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "name=John Updated" \
  -F "email=john.updated@example.com" \
  -F "avatar=@/path/to/new-avatar.jpg"
```

#### Change Password
```bash
curl -X POST "http://localhost:8000/api/v1/password/change" \
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
curl -X POST "http://localhost:8000/api/v1/password/forgot" \
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
