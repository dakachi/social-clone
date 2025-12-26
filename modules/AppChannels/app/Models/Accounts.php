<?php

namespace Modules\AppChannels\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accounts extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'accounts';
    protected $guarded = [];
    public function posts()
    {
        return $this->hasMany(\Modules\AppPublishing\Models\Posts::class, 'account_id');
    }

    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }
}