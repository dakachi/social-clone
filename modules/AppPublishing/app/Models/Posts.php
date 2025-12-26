<?php

namespace Modules\AppPublishing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Posts extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'posts';

    protected $guarded = [];

    protected $casts = [
        'labels' => 'array', 
    ];

    public function account()
    {
        return $this->belongsTo(\Modules\AppChannels\Models\Accounts::class, 'account_id');
    }
}
