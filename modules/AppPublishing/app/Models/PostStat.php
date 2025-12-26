<?php

namespace Modules\AppPublishing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostStat extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'post_stats';

    protected $guarded = [];
}
