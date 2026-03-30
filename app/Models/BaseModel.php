<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
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
