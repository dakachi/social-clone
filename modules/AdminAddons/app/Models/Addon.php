<?php

namespace Modules\AdminAddons\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $table = 'addons';

    protected $guarded = [];

    public $timestamps = false;
}
