<?php

namespace Modules\AdminLanguages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminLanguages\Database\Factories\LanguageItemsFactory;

class LanguageItems extends Model
{
    use HasFactory;

    protected $table = 'language_items';

    public $timestamps = false;

    protected $guarded = [];
}
