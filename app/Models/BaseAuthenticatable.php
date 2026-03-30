<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

abstract class BaseAuthenticatable extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Allow mass assignment by default. Override per model if needed.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Default casts for all models.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
