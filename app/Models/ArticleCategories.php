<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class ArticleCategories extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'article_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public function articles()
    {
        return $this->hasMany(Articles::class, 'cate_id', 'id');
    }
    
}
