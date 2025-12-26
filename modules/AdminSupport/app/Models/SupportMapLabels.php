<?php

namespace Modules\AdminSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminSupport\Database\Factories\SupportMapLabelsFactory;

class SupportMapLabels extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): SupportMapLabelsFactory
    // {
    //     // return SupportMapLabelsFactory::new();
    // }
}
