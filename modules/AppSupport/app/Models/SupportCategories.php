<?php

namespace Modules\AppSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AppSupport\Database\Factories\SupportCategoriesFactory;

class SupportCategories extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): SupportCategoriesFactory
    // {
    //     // return SupportCategoriesFactory::new();
    // }
}
