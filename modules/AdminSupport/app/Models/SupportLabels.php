<?php

namespace Modules\AdminSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminSupport\Database\Factories\SupportLabelsFactory;

class SupportLabels extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'support_labels';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): SupportLabelsFactory
    // {
    //     // return SupportLabelsFactory::new();
    // }
}
