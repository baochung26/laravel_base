# Database Query Optimization Guide

Hướng dẫn tối ưu hóa database queries trong Laravel để cải thiện performance và tránh N+1 queries.

## 📋 Tổng quan

Query Optimization bao gồm:
- ✅ **Eager Loading** - Tránh N+1 queries
- ✅ **Indexing Strategy** - Tối ưu indexes
- ✅ **Query Analysis** - Phân tích và monitor queries
- ✅ **Query Optimization Techniques** - Các kỹ thuật tối ưu
- ✅ **Best Practices** - Best practices cho query optimization

## 🔍 1. Eager Loading (Tránh N+1 Queries)

### Vấn đề N+1 Queries

**Ví dụ N+1 Problem:**
```php
// ❌ BAD: N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->roles->pluck('name'); // Query executed for each user
}
// Result: 1 query for users + N queries for roles = N+1 queries
```

**Solution với Eager Loading:**
```php
// ✅ GOOD: 2 queries total
$users = User::with('roles')->get();
foreach ($users as $user) {
    echo $user->roles->pluck('name'); // No additional queries
}
// Result: 1 query for users + 1 query for all roles = 2 queries
```

### Eager Loading Methods

#### Basic Eager Loading
```php
// Load single relationship
$users = User::with('roles')->get();

// Load multiple relationships
$users = User::with(['roles', 'permissions'])->get();

// Nested eager loading
$posts = Post::with('user.roles')->get();
```

#### Conditional Eager Loading
```php
// Eager load only when needed
$users = User::with(['roles' => function ($query) {
    $query->where('active', true);
}])->get();
```

#### Lazy Eager Loading
```php
// Load relationships after the fact
$users = User::all();
$users->load('roles');
```

### Eager Loading trong Repository Pattern

**BaseRepository đã hỗ trợ eager loading:**
```php
// In Repository
public function getAllWithRoles()
{
    return $this->with(['roles'])->all();
}

// In Service
public function getAll()
{
    return $this->userRepository->getAllWithRoles();
}
```

### Best Practices

✅ **DO:**
- Always eager load relationships that will be accessed
- Use `with()` in repositories for common queries
- Load relationships at the repository/service level
- Use `loadMissing()` for conditional loading

❌ **DON'T:**
- Don't eager load relationships that won't be used
- Don't load relationships in loops
- Don't forget to eager load in pagination queries
- Don't use `->load()` after query execution (use `with()` instead)

## 📊 2. Indexing Strategy

### Index Review Checklist

#### ✅ Users Table Indexes

**Current Indexes:**
```php
$table->string('email')->unique(); // ✅ Unique index (automatic)
$table->index('email'); // ⚠️ Redundant (unique already creates index)
$table->index('created_at'); // ✅ Good for date range queries
$table->index('deleted_at'); // ✅ Good for soft delete queries
```

**Recommendations:**
- ✅ Email unique index - **GOOD** (automatically indexed)
- ⚠️ Additional email index - **REDUNDANT** (can be removed, unique already indexes)
- ✅ `created_at` index - **GOOD** (for date range queries)
- ✅ `deleted_at` index - **GOOD** (for soft delete queries)
- 💡 Consider: `index('name')` if name searches are frequent

#### ✅ Posts Table Indexes

**Current Indexes:**
```php
$table->string('slug')->unique(); // ✅ Unique index
$table->index('user_id'); // ✅ Foreign key index
$table->index('status'); // ✅ Good for filtering
$table->index('is_featured'); // ✅ Good for featured queries
$table->index('published_at'); // ✅ Good for date queries
$table->index('created_at'); // ✅ Good for date range queries
$table->index('deleted_at'); // ✅ Good for soft delete queries

// Composite indexes
$table->index(['status', 'published_at']); // ✅ Excellent
$table->index(['user_id', 'status']); // ✅ Excellent
$table->index(['is_featured', 'status', 'published_at']); // ✅ Excellent
```

**Recommendations:**
- ✅ All indexes are well-planned
- ✅ Composite indexes cover common query patterns
- 💡 Consider: Full-text index for search if needed

### Index Types và Use Cases

#### Single Column Index
```php
// For WHERE clauses
$table->index('email'); // WHERE email = '...'
$table->index('status'); // WHERE status = '...'

// For ORDER BY
$table->index('created_at'); // ORDER BY created_at DESC

// For JOIN operations
$table->index('user_id'); // JOIN on user_id
```

#### Composite Index
```php
// Order matters! Most selective column first
$table->index(['status', 'published_at']); 
// Covers: WHERE status = ? AND published_at > ?
// Covers: WHERE status = ? (leftmost prefix)

// Multi-column queries
$table->index(['user_id', 'status', 'published_at']);
// Covers complex WHERE clauses with multiple conditions
```

**Composite Index Rules:**
1. **Leftmost Prefix Rule**: Index can be used for queries on leftmost columns
   - Index `['a', 'b', 'c']` covers: `WHERE a = ?`, `WHERE a = ? AND b = ?`, `WHERE a = ? AND b = ? AND c = ?`
   - Does NOT cover: `WHERE b = ?` or `WHERE c = ?`
2. **Selectivity**: Put most selective column first
3. **Query Patterns**: Match your actual query patterns

#### Unique Index
```php
$table->unique('email'); // Prevents duplicates + creates index
$table->unique(['user_id', 'slug']); // Composite unique
```

### Index Performance Impact

**Benefits:**
- ✅ Faster WHERE clause filtering
- ✅ Faster JOIN operations
- ✅ Faster ORDER BY operations
- ✅ Faster GROUP BY operations

**Costs:**
- ⚠️ Slower INSERT/UPDATE operations (indexes must be updated)
- ⚠️ Additional storage space
- ⚠️ Maintenance overhead

**Balance:**
- Index frequently queried columns
- Don't over-index (especially on frequently updated columns)
- Monitor query performance to identify missing indexes

## 🔬 3. Query Analysis & Monitoring

### Enable Query Logging

**In `.env`:**
```env
LOG_ENABLE_QUERY_LOG=true
LOG_SLOW_QUERY_THRESHOLD=1000  # Log queries > 1000ms
```

**In `config/logging.php`:**
```php
'query' => [
    'driver' => 'single',
    'path' => storage_path('logs/query.log'),
    'level' => 'debug',
    'formatter' => \App\Logging\QueryFormatter::class,
],
```

### Laravel Debugbar

**Install:**
```bash
composer require barryvdh/laravel-debugbar --dev
```

**Features:**
- Query count and execution time
- Query details (SQL, bindings, time)
- N+1 query detection
- Memory usage

### Laravel Telescope (Optional)

**Install:**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
```

**Features:**
- Query monitoring
- Request/Response tracking
- Exception tracking
- Performance metrics

### Manual Query Analysis

**Enable Query Log:**
```php
DB::enableQueryLog();

// Your queries here
$users = User::with('roles')->get();

// Get queries
$queries = DB::getQueryLog();
dd($queries);
```

**Count Queries:**
```php
DB::enableQueryLog();
$users = User::all();
$count = count(DB::getQueryLog());
echo "Total queries: {$count}";
```

### MySQL EXPLAIN

**Use EXPLAIN to analyze queries:**
```php
$explain = DB::select('EXPLAIN SELECT * FROM users WHERE email = ?', ['test@example.com']);
```

**Key Metrics:**
- `type`: Access method (ALL, index, range, ref, eq_ref, const)
- `key`: Index used
- `rows`: Estimated rows examined
- `Extra`: Additional information

**Good Signs:**
- `type` = `ref`, `eq_ref`, or `const`
- `key` shows index is used
- `rows` is low
- `Extra` = `Using index` (index-only scan)

**Bad Signs:**
- `type` = `ALL` (full table scan)
- `key` = `NULL` (no index used)
- `rows` is very high
- `Extra` = `Using filesort` or `Using temporary`

## ⚡ 4. Query Optimization Techniques

### 1. Select Only Needed Columns

**❌ BAD:**
```php
$users = User::all(); // Selects all columns
```

**✅ GOOD:**
```php
$users = User::select('id', 'name', 'email')->get();
```

### 2. Use Chunking for Large Datasets

**❌ BAD:**
```php
$users = User::all(); // Loads all into memory
foreach ($users as $user) {
    // Process
}
```

**✅ GOOD:**
```php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process
    }
});
```

### 3. Use Cursor for Large Datasets

**✅ GOOD:**
```php
foreach (User::cursor() as $user) {
    // Process one at a time
}
```

### 4. Avoid SELECT * in Joins

**❌ BAD:**
```php
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')->get();
```

**✅ GOOD:**
```php
$posts = Post::select('posts.*', 'users.name as user_name')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->get();
```

### 5. Use Database Functions Wisely

**❌ BAD:**
```php
$users = User::all()->filter(function ($user) {
    return str_contains($user->email, '@gmail.com');
});
```

**✅ GOOD:**
```php
$users = User::where('email', 'like', '%@gmail.com')->get();
```

### 6. Use Aggregates in Database

**❌ BAD:**
```php
$users = User::all();
$count = $users->count(); // Loads all into memory
```

**✅ GOOD:**
```php
$count = User::count(); // Database aggregate
```

### 7. Use EXISTS Instead of COUNT

**❌ BAD:**
```php
if (User::where('email', $email)->count() > 0) {
    // Exists
}
```

**✅ GOOD:**
```php
if (User::where('email', $email)->exists()) {
    // Exists
}
```

### 8. Use LIMIT When Possible

**❌ BAD:**
```php
$users = User::orderBy('created_at', 'desc')->get();
$latest = $users->first();
```

**✅ GOOD:**
```php
$latest = User::orderBy('created_at', 'desc')->first();
```

### 9. Avoid N+1 in Relationships

**❌ BAD:**
```php
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name; // N+1 queries
}
```

**✅ GOOD:**
```php
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name; // No additional queries
}
```

### 10. Use Database Transactions Wisely

**✅ GOOD:**
```php
DB::transaction(function () {
    $user = User::create([...]);
    $user->roles()->attach([...]);
});
```

## 📈 5. Performance Monitoring

### Slow Query Logging

**Already configured in project:**
- `LogQueryMiddleware` logs queries > threshold
- Configurable via `LOG_SLOW_QUERY_THRESHOLD`
- Logs to `storage/logs/query.log`

**Review slow queries:**
```bash
tail -f storage/logs/query.log
```

### Query Performance Metrics

**Track:**
- Query count per request
- Average query time
- Slow queries (> threshold)
- N+1 query patterns

### Index Usage Analysis

**Check index usage in MySQL:**
```sql
SHOW INDEX FROM users;
EXPLAIN SELECT * FROM users WHERE email = 'test@example.com';
```

**Monitor unused indexes:**
```sql
-- MySQL 5.7+
SELECT * FROM sys.schema_unused_indexes;
```

## 🎯 6. Common Optimization Patterns

### Pattern 1: Pagination với Eager Loading

**✅ GOOD:**
```php
$users = User::with('roles')
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

### Pattern 2: Search với Indexes

**✅ GOOD:**
```php
// Uses index on email
$users = User::where('email', 'like', "%{$keyword}%")
    ->orWhere('name', 'like', "%{$keyword}%")
    ->get();

// Better: Full-text search if available
$posts = Post::whereFullText(['title', 'content'], $keyword)->get();
```

### Pattern 3: Filtering với Composite Indexes

**✅ GOOD:**
```php
// Uses composite index ['status', 'published_at']
$posts = Post::where('status', 'published')
    ->where('published_at', '<=', now())
    ->orderBy('published_at', 'desc')
    ->get();
```

### Pattern 4: Counting với Conditions

**✅ GOOD:**
```php
// Uses index on status
$count = Post::where('status', 'published')->count();
```

## 📝 7. Migration Index Review

### Current Migration Analysis

#### ✅ Users Table
- **Email**: Unique index ✅ (automatic)
- **Created_at**: Index ✅ (for date queries)
- **Deleted_at**: Index ✅ (for soft deletes)
- **Recommendation**: Remove redundant `index('email')` (unique already indexes)

#### ✅ Posts Table
- **Slug**: Unique index ✅
- **User_id**: Index ✅ (foreign key)
- **Status**: Index ✅
- **Is_featured**: Index ✅
- **Published_at**: Index ✅
- **Created_at**: Index ✅
- **Deleted_at**: Index ✅
- **Composite indexes**: Well-designed ✅
- **Recommendation**: Excellent indexing strategy

### Index Optimization Recommendations

#### 1. Remove Redundant Indexes

**Users table:**
```php
// Current (redundant)
$table->string('email')->unique(); // Creates index automatically
$table->index('email'); // ❌ REDUNDANT - can be removed
```

**Optimized:**
```php
$table->string('email')->unique(); // ✅ Unique index is sufficient
// Remove: $table->index('email');
```

#### 2. Add Missing Indexes (if needed)

**If name searches are frequent:**
```php
$table->index('name'); // For name searches
```

**If avatar lookups are needed:**
```php
$table->index('avatar'); // Only if frequently queried
```

#### 3. Composite Index Optimization

**Current composite indexes are well-designed:**
- `['status', 'published_at']` - Covers status filtering + date sorting
- `['user_id', 'status']` - Covers user's posts by status
- `['is_featured', 'status', 'published_at']` - Covers featured published posts

**Order is correct** (most selective first in most cases)

## 🛠️ 8. Tools & Commands

### Laravel Commands

**Check query count:**
```bash
php artisan tinker
DB::enableQueryLog();
User::with('roles')->get();
count(DB::getQueryLog());
```

**Analyze slow queries:**
```bash
tail -f storage/logs/query.log
```

### MySQL Commands

**Check indexes:**
```sql
SHOW INDEX FROM users;
SHOW INDEX FROM posts;
```

**Analyze query:**
```sql
EXPLAIN SELECT * FROM users WHERE email = 'test@example.com';
```

**Check table size:**
```sql
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'laravel_db'
ORDER BY size_mb DESC;
```

## ✅ 9. Checklist

### Query Optimization Checklist

- [ ] All relationships are eager loaded when needed
- [ ] No N+1 queries in controllers/services
- [ ] Indexes exist for frequently queried columns
- [ ] Composite indexes match query patterns
- [ ] Slow query logging is enabled
- [ ] Query performance is monitored
- [ ] Redundant indexes are removed
- [ ] Database functions are used instead of PHP filtering
- [ ] Chunking/cursor is used for large datasets
- [ ] Only needed columns are selected

### Index Review Checklist

- [ ] Foreign keys are indexed (automatic)
- [ ] WHERE clause columns are indexed
- [ ] ORDER BY columns are indexed
- [ ] JOIN columns are indexed
- [ ] Composite indexes match query patterns
- [ ] Unique constraints are indexed (automatic)
- [ ] Timestamps used in queries are indexed
- [ ] Soft delete columns are indexed
- [ ] No redundant indexes
- [ ] Index order matches query selectivity

## 📚 10. Resources

- [Laravel Query Optimization](https://laravel.com/docs/queries#database-query-logging)
- [MySQL Index Optimization](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [Laravel Telescope](https://laravel.com/docs/telescope)

## 🎯 Summary

**Key Takeaways:**
1. ✅ Always eager load relationships that will be accessed
2. ✅ Index frequently queried columns
3. ✅ Use composite indexes for multi-column queries
4. ✅ Monitor query performance regularly
5. ✅ Remove redundant indexes
6. ✅ Use database functions instead of PHP filtering
7. ✅ Select only needed columns
8. ✅ Use chunking/cursor for large datasets

**Current Project Status:**
- ✅ Eager loading implemented in repositories
- ✅ Indexes well-planned in migrations
- ✅ Slow query logging configured
- ⚠️ Minor: Remove redundant email index in users table
