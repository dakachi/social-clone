<?php

namespace Modules\AdminLanguages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Languages extends Model
{
    use HasFactory;

    protected $table = 'languages';

    public $timestamps = false;

    protected $guarded = [];
}
