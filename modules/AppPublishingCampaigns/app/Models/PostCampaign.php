<?php

namespace Modules\AppPublishingCampaigns\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AppPublishingCampaigns\Database\Factories\PostCampaignFactory;

class PostCampaign extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = []; 
}
