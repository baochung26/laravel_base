<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Example migration demonstrating database conventions:
     * - Timestamps (created_at, updated_at)
     * - Soft deletes (deleted_at)
     * - Indexes for frequently queried columns
     * - Foreign keys with proper indexing
     * - Composite indexes for complex queries
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            
            // Foreign key with index
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Content fields
            $table->string('title');
            $table->string('slug')->unique(); // Unique index
            $table->text('content')->nullable();
            $table->text('excerpt')->nullable();
            
            // Status fields
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            
            // Metadata
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamp('published_at')->nullable();
            
            // Timestamps convention
            $table->timestamps();
            
            // Soft deletes convention
            $table->softDeletes();
            
            // Indexes for frequently queried columns
            $table->index('user_id'); // Foreign key already indexed, but explicit for clarity
            $table->index('status'); // For filtering by status
            $table->index('is_featured'); // For featured posts
            $table->index('published_at'); // For published date queries
            $table->index('created_at'); // For date range queries
            $table->index('deleted_at'); // For soft delete queries
            
            // Composite indexes for complex queries
            $table->index(['status', 'published_at']); // For published posts by date
            $table->index(['user_id', 'status']); // For user's posts by status
            $table->index(['is_featured', 'status', 'published_at']); // For featured published posts
            
            // Full-text index (MySQL 5.7.6+)
            // $table->fullText(['title', 'content']); // For search queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
