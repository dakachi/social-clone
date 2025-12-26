<?php

namespace Modules\AdminSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminSupport\Database\Factories\SupportCategoriesFactory;

class SupportCategories extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'support_categories';

    protected $fillable = [];

    // protected static function newFactory(): SupportCategoriesFactory
    // {
    //     // return SupportCategoriesFactory::new();
    // }
}
