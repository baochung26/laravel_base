# Architecture Guide - Repository Service Controller Pattern

Hướng dẫn về cấu trúc và kiến trúc dự án sử dụng mô hình Repository-Service-Controller.

## 📋 Tổng quan

Dự án sử dụng **Repository-Service-Controller Pattern** để tách biệt các lớp logic, giúp code dễ maintain, test và scale.

## 🏗️ Cấu trúc

```
app/
├── Controllers/          # HTTP Layer - Xử lý requests/responses
├── Services/            # Business Logic Layer - Logic nghiệp vụ
├── Repositories/        # Data Access Layer - Truy cập database
│   ├── Contracts/       # Repository Interfaces
│   └── Eloquent/        # Repository Implementations
├── DTOs/                # Data Transfer Objects - Transfer data giữa các layers
├── Models/              # Eloquent Models
├── Exceptions/          # Custom Exceptions
└── Http/
    ├── Requests/        # Form Request Validation
    └── Middleware/      # HTTP Middleware
```

## 📚 Các thành phần chính

### 1. Repository Layer (Data Access)

**Mục đích:** Tách biệt logic truy cập database khỏi business logic.

**Cấu trúc:**
- `RepositoryInterface` - Base interface cho tất cả repositories
- `BaseRepository` - Base implementation với các method chung
- `{Model}RepositoryInterface` - Interface riêng cho từng model
- `{Model}Repository` - Implementation cụ thể

**Ví dụ:**

```php
// Interface
interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function existsByEmail(string $email): bool;
}

// Implementation
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }
}
```

**Lợi ích:**
- Dễ dàng swap implementation (Eloquent, MongoDB, API, etc.)
- Dễ test bằng cách mock repository
- Tái sử dụng code với BaseRepository

### 2. Service Layer (Business Logic)

**Mục đích:** Chứa tất cả business logic, xử lý nghiệp vụ.

**Cấu trúc:**
- `{Model}Service` - Service cho từng domain
- Inject Repository vào Service để truy cập data

**Ví dụ:**

```php
class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function create(UserDTO $userDTO, ?string $roleName = null): UserDTO
    {
        // Business logic here
        if ($this->userRepository->existsByEmail($userDTO->email)) {
            throw new ValidationException('Email already exists');
        }

        $data = $userDTO->toCreateArray();
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);

        // Assign role if provided
        if ($roleName) {
            $user->assignRole($roleName);
        }

        return UserDTO::fromModel($user->load('roles', 'permissions'));
    }
}
```

**Lợi ích:**
- Tách biệt business logic khỏi HTTP layer
- Dễ test business logic độc lập
- Có thể reuse trong Console Commands, Jobs, etc.

### 3. Controller Layer (HTTP)

**Mục đích:** Xử lý HTTP requests và responses, không chứa business logic.

**Cấu trúc:**
- Inject Service vào Controller
- Chuyển đổi Request → DTO → Service → DTO → Response

**Ví dụ:**

```php
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userDTO = UserDTO::fromArray([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $result = $this->authService->register($userDTO, 'user');

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $result['user']->toArray(),
                'token' => $result['token'],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
```

**Lợi ích:**
- Controller chỉ lo HTTP concerns
- Dễ test HTTP layer
- Dễ maintain và đọc code

### 4. DTOs (Data Transfer Objects)

**Mục đích:** Transfer data giữa các layers một cách type-safe.

**Cấu trúc:**
- Immutable objects với readonly properties
- Factory methods: `fromArray()`, `fromModel()`
- Serialization methods: `toArray()`, `toCreateArray()`

**Ví dụ:**

```php
class UserDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password = null,
        public readonly ?array $roles = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
            roles: $data['roles'] ?? null,
        );
    }

    public static function fromModel($user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            roles: $user->relationLoaded('roles') 
                ? $user->roles->pluck('name')->toArray() 
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles,
        ], fn($value) => $value !== null);
    }
}
```

**Lợi ích:**
- Type-safe data transfer
- Immutable - tránh side effects
- Dễ test và maintain

## 🔄 Flow của Request

```
HTTP Request
    ↓
Controller (chuyển Request → DTO)
    ↓
Service (Business Logic, xử lý DTO)
    ↓
Repository (Data Access, trả về Model)
    ↓
Service (chuyển Model → DTO)
    ↓
Controller (chuyển DTO → Response)
    ↓
HTTP Response
```

**Ví dụ cụ thể:**

```php
// 1. Request đến Controller
POST /api/register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}

// 2. Controller tạo DTO từ Request
$userDTO = UserDTO::fromArray($request->all());

// 3. Service xử lý business logic
$result = $authService->register($userDTO, 'user');

// 4. Repository truy cập database
$user = $userRepository->create($data);

// 5. Service trả về DTO
return UserDTO::fromModel($user);

// 6. Controller trả về JSON Response
return response()->json([
    'user' => $userDTO->toArray(),
    'token' => $token
]);
```

## 📝 Best Practices

### 1. Repository Pattern

✅ **DO:**
- Sử dụng interface cho repositories
- Tạo BaseRepository cho code chung
- Method names rõ ràng, descriptive
- Chỉ chứa data access logic

❌ **DON'T:**
- Không chứa business logic trong Repository
- Không return DTOs, return Models
- Không inject Services vào Repository

### 2. Service Layer

✅ **DO:**
- Chứa tất cả business logic
- Inject Repositories, không inject Models trực tiếp
- Return DTOs thay vì Models
- Throw custom exceptions cho business errors

❌ **DON'T:**
- Không xử lý HTTP concerns (requests, responses)
- Không truy cập database trực tiếp
- Không chứa validation logic (dùng Form Requests)

### 3. Controller Layer

✅ **DO:**
- Chỉ xử lý HTTP requests/responses
- Inject Services, không inject Repositories
- Chuyển đổi Request ↔ DTO
- Handle exceptions và return appropriate responses

❌ **DON'T:**
- Không chứa business logic
- Không truy cập database trực tiếp
- Không chứa validation logic (dùng Form Requests)

### 4. DTOs

✅ **DO:**
- Sử dụng readonly properties
- Tạo factory methods: `fromArray()`, `fromModel()`
- Tạo serialization methods: `toArray()`
- Keep DTOs simple, chỉ chứa data

❌ **DON'T:**
- Không chứa business logic
- Không chứa database operations
- Không mutable (không thay đổi sau khi tạo)

## 🧪 Testing

### Testing Repository

```php
public function test_user_repository_can_find_by_email()
{
    // Arrange
    $user = User::factory()->create(['email' => 'test@example.com']);
    
    // Act
    $found = $this->userRepository->findByEmail('test@example.com');
    
    // Assert
    $this->assertNotNull($found);
    $this->assertEquals($user->id, $found->id);
}
```

### Testing Service

```php
public function test_user_service_can_create_user()
{
    // Arrange
    $userDTO = UserDTO::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);
    
    // Act
    $result = $this->userService->create($userDTO);
    
    // Assert
    $this->assertInstanceOf(UserDTO::class, $result);
    $this->assertEquals('john@example.com', $result->email);
}
```

### Testing Controller

```php
public function test_auth_controller_can_register_user()
{
    // Arrange
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];
    
    // Act
    $response = $this->postJson('/api/register', $payload);
    
    // Assert
    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user',
            'token'
        ]);
}
```

## 🔧 Dependency Injection

### Binding Repositories

Trong `AppServiceProvider`:

```php
public function register(): void
{
    $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
}
```

### Injecting vào Services

```php
class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected EmailService $emailService
    ) {}
}
```

### Injecting vào Controllers

```php
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}
}
```

## 📦 Tạo mới một Domain/Feature

Để tạo một feature mới (ví dụ: Product):

### 1. Tạo Repository Interface

```php
// app/Repositories/Contracts/ProductRepositoryInterface.php
interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug): ?Product;
    public function search(string $keyword, int $perPage = 15);
}
```

### 2. Tạo Repository Implementation

```php
// app/Repositories/Eloquent/ProductRepository.php
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected function model(): string
    {
        return Product::class;
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->findBy('slug', $slug);
    }
}
```

### 3. Tạo DTO

```php
// app/DTOs/ProductDTO.php
class ProductDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name,
        public readonly string $slug,
        public readonly float $price,
    ) {}
    
    // Factory methods...
}
```

### 4. Tạo Service

```php
// app/Services/ProductService.php
class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}
    
    public function create(ProductDTO $productDTO): ProductDTO
    {
        // Business logic here
        $product = $this->productRepository->create($productDTO->toCreateArray());
        return ProductDTO::fromModel($product);
    }
}
```

### 5. Tạo Controller

```php
// app/Http/Controllers/ProductController.php
class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}
    
    public function store(StoreProductRequest $request): JsonResponse
    {
        $productDTO = ProductDTO::fromArray($request->validated());
        $result = $this->productService->create($productDTO);
        
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $result->toArray(),
        ], 201);
    }
}
```

### 6. Bind Repository trong AppServiceProvider

```php
public function register(): void
{
    $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
}
```

## 🎯 Tóm tắt

- **Repository:** Data Access Layer - Chỉ truy cập database
- **Service:** Business Logic Layer - Xử lý nghiệp vụ
- **Controller:** HTTP Layer - Xử lý requests/responses
- **DTO:** Data Transfer - Transfer data type-safe giữa các layers

Pattern này giúp:
- ✅ Code dễ maintain và test
- ✅ Tách biệt concerns rõ ràng
- ✅ Dễ scale và extend
- ✅ Dễ reuse code
- ✅ Type-safe với DTOs
