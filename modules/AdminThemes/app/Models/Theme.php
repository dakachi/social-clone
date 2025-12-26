<?php

namespace Modules\AdminThemes\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name', 'label', 'type', 'description', 'colors', 'dark_mode', 'active', 'uses'
    ];

    protected $casts = [
        'colors' => 'array',
        'uses' => 'array',
        'dark_mode' => 'boolean',
        'active' => 'boolean',
    ];
}
