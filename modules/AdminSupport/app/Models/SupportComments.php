<?php

namespace Modules\AdminSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportComments extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'support_comments';

    // Allow mass assignment for the specified attributes
    protected $fillable = [
        'id',
        'id_secure',
        'ticket_id',
        'user_id',
        'content',
    ];

    // protected static function newFactory(): SupportCommentsFactory
    // {
    //     // return SupportCommentsFactory::new();
    // }
}
