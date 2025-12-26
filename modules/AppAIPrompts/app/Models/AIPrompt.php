<?php

namespace Modules\AppAIPrompts\Models;

use Illuminate\Database\Eloquent\Model;

class AIPrompt extends Model
{
    protected $table = 'ai_prompts';
    protected $guarded = [];
    public $timestamps = false;
}