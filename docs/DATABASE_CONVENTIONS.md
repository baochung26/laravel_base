# Database Conventions Documentation

Tài liệu về Database Conventions, Migration Standards, Seeding, Soft Deletes, Timestamps, và Indexing Guidelines.

## 📋 Tổng quan

Database Conventions bao gồm:
- ✅ **Migration Standards** - Chuẩn migration với conventions
- ✅ **Soft Deletes** - Soft deletes convention
- ✅ **Timestamps** - Timestamps convention
- ✅ **Indexing Guidelines** - Quy tắc đặt index
- ✅ **Seeder Examples** - Ví dụ seeder

## 📝 1. Migration Conventions

### Migration Naming

**Format:** `{YYYY_MM_DD_HHMMSS}_{action}_{table_name}_table.php`

**Examples:**
- `2024_01_01_000001_create_users_table.php`
- `2024_01_01_000002_add_avatar_to_users_table.php`
- `2024_01_01_000003_create_posts_table.php`

### Migration Structure

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Columns
            $table->string('name');
            $table->string('email')->unique();
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes
            $table->softDeletes();
            
            // Indexes
            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

### Column Types

**Common Types:**
- `id()` - Auto-incrementing primary key
- `string($name, $length = 255)` - VARCHAR
- `text($name)` - TEXT
- `integer($name)` - INT
- `bigInteger($name)` - BIGINT
- `boolean($name)` - TINYINT(1)
- `enum($name, $values)` - ENUM
- `json($name)` - JSON
- `timestamp($name)` - TIMESTAMP
- `date($name)` - DATE
- `decimal($name, $precision, $scale)` - DECIMAL

## ⏰ 2. Timestamps Convention

### Standard Timestamps

**Always include:**
```php
$table->timestamps(); // Creates created_at and updated_at
```

**Usage:**
- `created_at` - Record creation time
- `updated_at` - Last update time (auto-updated)

**Custom Timestamps:**
```php
$table->timestamp('published_at')->nullable();
$table->timestamp('deleted_at')->nullable(); // For soft deletes
$table->timestamp('archived_at')->nullable();
```

### Timestamp Indexes

**Always index timestamps used in queries:**
```php
$table->index('created_at'); // For date range queries
$table->index('published_at'); // For published date queries
$table->index('deleted_at'); // For soft delete queries
```

## 🗑️ 3. Soft Deletes Convention

### Implementation

**Add to migration:**
```php
$table->softDeletes(); // Creates deleted_at column
```

**Add to Model:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}
```

### Soft Delete Behavior

**When soft deleted:**
- Record is not physically deleted
- `deleted_at` is set to current timestamp
- Record is excluded from normal queries
- Can be restored or permanently deleted

**Query Behavior:**
```php
// Normal query (excludes soft deleted)
User::all(); // Only non-deleted users

// Include soft deleted
User::withTrashed()->get();

// Only soft deleted
User::onlyTrashed()->get();

// Restore
$user->restore();

// Permanently delete
$user->forceDelete();
```

### Soft Delete Index

**Always index deleted_at:**
```php
$table->index('deleted_at');
```

## 🔍 4. Indexing Guidelines

### When to Add Indexes

**✅ Add indexes for:**
- Foreign keys (automatically indexed by Laravel)
- Columns used in WHERE clauses
- Columns used in JOIN operations
- Columns used in ORDER BY
- Columns used in GROUP BY
- Unique columns (use `unique()`)
- Frequently queried columns
- Composite indexes for multi-column queries

**❌ Don't add indexes for:**
- Rarely queried columns
- Columns with very low cardinality (few unique values)
- Columns that are frequently updated (balance needed)
- Very small tables (< 1000 rows)

### Index Types

#### Single Column Index

```php
// Basic index
$table->index('email');

// Unique index
$table->unique('email');

// Primary key (auto-indexed)
$table->id();
```

#### Composite Index

```php
// Composite index for multi-column queries
$table->index(['status', 'published_at']);
$table->index(['user_id', 'status']);

// Order matters! Put most selective column first
$table->index(['status', 'created_at']); // status is more selective
```

#### Foreign Key Index

```php
// Foreign key automatically creates index
$table->foreignId('user_id')->constrained('users');

// Explicit index (optional, already indexed)
$table->index('user_id');
```

### Index Examples

**Example 1: Users Table**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique(); // Unique index
    $table->string('name');
    $table->timestamps();
    $table->softDeletes();
    
    // Additional indexes
    $table->index('name'); // For name searches
    $table->index('created_at'); // For date range queries
    $table->index('deleted_at'); // For soft delete queries
});
```

**Example 2: Posts Table**
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained(); // Auto-indexed
    $table->string('slug')->unique(); // Unique index
    $table->string('title');
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Single column indexes
    $table->index('status');
    $table->index('published_at');
    $table->index('created_at');
    $table->index('deleted_at');
    
    // Composite indexes
    $table->index(['status', 'published_at']); // For published posts
    $table->index(['user_id', 'status']); // For user's posts by status
});
```

### Index Best Practices

**✅ DO:**
- Index foreign keys (automatic)
- Index frequently queried columns
- Use composite indexes for multi-column queries
- Put most selective column first in composite index
- Index timestamps used in queries
- Index soft delete columns

**❌ DON'T:**
- Don't over-index (slows down writes)
- Don't index rarely queried columns
- Don't index columns with very low cardinality
- Don't create redundant indexes
- Don't forget to index soft delete columns

## 🌱 5. Seeder Conventions

### Seeder Structure

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ExampleSeeder extends Seeder
{
    public function run(): void
    {
        // Seeding logic here
    }
}
```

### Seeder Best Practices

**✅ DO:**
- Use factories for bulk data
- Create relationships properly
- Use transactions for data integrity
- Clear existing data if needed
- Provide informative output
- Seed in correct order (dependencies)

**❌ DON'T:**
- Don't seed production data
- Don't hardcode sensitive data
- Don't forget to handle relationships
- Don't seed without clearing old data (if needed)

### Seeder Examples

**Example 1: Basic Seeder**
```php
public function run(): void
{
    User::factory()->count(10)->create();
}
```

**Example 2: Seeder with Relationships**
```php
public function run(): void
{
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);
    $admin->assignRole('admin');
    
    User::factory()->count(10)->create()->each(function ($user) {
        $user->assignRole('user');
    });
}
```

**Example 3: Seeder with Transactions**
```php
public function run(): void
{
    DB::transaction(function () {
        // Seed data
        User::factory()->count(10)->create();
    });
}
```

## 📊 6. Migration Examples

### Example 1: Users Table (Standard)

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('avatar')->nullable();
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('email');
    $table->index('created_at');
    $table->index('deleted_at');
});
```

### Example 2: Posts Table (With Relationships)

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content')->nullable();
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('user_id');
    $table->index('status');
    $table->index('published_at');
    $table->index('created_at');
    $table->index('deleted_at');
    
    // Composite indexes
    $table->index(['status', 'published_at']);
    $table->index(['user_id', 'status']);
});
```

### Example 3: Pivot Table

```php
Schema::create('post_tag', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('tag_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    
    // Composite unique index
    $table->unique(['post_id', 'tag_id']);
    
    // Individual indexes
    $table->index('post_id');
    $table->index('tag_id');
});
```

## 🔧 7. Model Conventions

### Soft Deletes in Model

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
    
    // Or in Laravel 8+
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
```

### Timestamps in Model

```php
class User extends Model
{
    // Timestamps are enabled by default
    public $timestamps = true;
    
    // Customize timestamp column names
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    // Disable timestamps
    public $timestamps = false;
}
```

## 📚 8. Best Practices

### Migration Best Practices

✅ **DO:**
- Use descriptive migration names
- Include timestamps in all tables
- Use soft deletes for important data
- Add indexes for frequently queried columns
- Use foreign keys with proper constraints
- Test migrations up and down

❌ **DON'T:**
- Don't modify existing migrations
- Don't forget to add indexes
- Don't create tables without timestamps
- Don't forget foreign key constraints
- Don't skip down() method

### Indexing Best Practices

✅ **DO:**
- Index foreign keys (automatic)
- Index columns used in WHERE clauses
- Use composite indexes for multi-column queries
- Index timestamps used in queries
- Monitor query performance

❌ **DON'T:**
- Don't over-index
- Don't index rarely queried columns
- Don't create redundant indexes
- Don't forget to index soft delete columns

### Seeding Best Practices

✅ **DO:**
- Use factories for bulk data
- Create relationships properly
- Provide informative output
- Seed in correct order

❌ **DON'T:**
- Don't seed production data
- Don't hardcode sensitive data
- Don't forget to handle relationships

## 🧪 9. Testing Migrations

### Test Migration Up/Down

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_migration(): void
    {
        // Test migration up
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'email'));
        
        // Test indexes
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes('users');
        
        $this->assertArrayHasKey('users_email_unique', $indexes);
    }
}
```

## 📝 10. Common Patterns

### Pattern 1: Standard Table

```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('name');
    $table->index('created_at');
    $table->index('deleted_at');
});
```

### Pattern 2: Table with Foreign Key

```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('user_id');
    $table->index('created_at');
    $table->index('deleted_at');
});
```

### Pattern 3: Table with Status

```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('status');
    $table->index('created_at');
    $table->index('deleted_at');
    $table->index(['status', 'created_at']);
});
```

## 🔗 11. Related Documentation

- [Laravel Migrations](https://laravel.com/docs/migrations)
- [Laravel Seeding](https://laravel.com/docs/seeding)
- [Database Indexing](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)

## 📚 Tài liệu tham khảo

- [Laravel Migrations](https://laravel.com/docs/migrations)
- [Laravel Seeding](https://laravel.com/docs/seeding)
- [MySQL Indexing](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- [Database Design Best Practices](https://www.postgresql.org/docs/current/ddl-best-practices.html)
