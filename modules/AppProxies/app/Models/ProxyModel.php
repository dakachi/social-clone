<?php

namespace Modules\AppProxies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProxyModel extends Model
{
    use HasFactory;

    protected $table = 'proxies';

    public $timestamps = false;
    protected $guarded = [];

    public function toCurlProxy(): string
    {
        return 'http://' . $this->proxy;
    }

    public function isSystem(): bool
    {
        return $this->is_system == 1;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function accounts()
    {
        return $this->hasMany(\Modules\AppChannels\Models\Accounts::class, 'proxy', 'id');
    }
}
