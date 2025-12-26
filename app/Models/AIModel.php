<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AIModel extends Model
{
    protected $table = 'ai_models';

    protected $fillable = [
        'id_secure',
        'provider',
        'model_key',
        'name',
        'category',
        'type',
        'is_active',
        'api_type',
        'api_params',
        'meta',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'api_params' => 'array',
        'meta'       => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function displayName(): string
    {
        return $this->name ?: $this->model_key;
    }

    public function getMetaValue(string $key, $default = null)
    {
        return $this->meta[$key] ?? $default;
    }

    public function getApiParam(string $key, $default = null)
    {
        return $this->api_params[$key] ?? $default;
    }
}
