<?php

namespace Modules\AdminSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminSupport\Database\Factories\SupportTypesFactory;

class SupportTypes extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'support_types';

    protected $fillable = [];

    // protected static function newFactory(): SupportTypesFactory
    // {
    //     // return SupportTypesFactory::new();
    // }
}
