<?php

namespace Modules\AppPublishingLabels\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostLabel extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'post_labels';
    protected $guarded = [];

}
