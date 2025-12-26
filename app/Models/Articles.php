<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class Articles extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'articles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(ArticleCategories::class, 'cate_id', 'id');
    }

    public function mapTags()
    {
        return $this->hasMany(\App\Models\ArticleMapTags::class, 'article_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            \App\Models\ArticleTags::class,
            'article_map_tags',
            'article_id',
            'tag_id'
        );
    }

    /**
     * Retrieve a paginated list of articles including joined data from:
     * - article_categories (fields: name)
     * - article_tags (fields: name) via article_map_tags
     *
     * @param array $params Parameters such as start, length, order, cate_id, status, search, etc.
     *
     * @return array The paginated result including total records and data rows.
     */
    public static function getArticlesList(array $params)
    {
        $start    = isset($params['start']) ? (int)$params['start'] : 0;
        $per_page = isset($params['length']) ? (int)$params['length'] : 10;
        $current_page = intval($start / $per_page) + 1;

        $order_field = "a.id";
        $order_sort  = "desc";

        if (isset($params['order']) && is_array($params['order'])) {
            $order_arr = $params['order'][0] ?? null;
            if ($order_arr) {
                $order_index = $order_arr['column'] ?? 0;
                $order_sort  = (($order_arr['dir'] ?? 'asc') === "desc") ? "desc" : "asc";
                $columnsMapping = [
                    0 => 'a.changed',
                    1 => 'a.title',
                    2 => 'c.name',
                ];
                if (isset($columnsMapping[$order_index])) {
                    $order_field = $columnsMapping[$order_index];
                }
            }
        }

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        // Build the main query with optimized joins using the pivot table
        $query = DB::table('articles as a')
            ->leftJoin('article_categories as c', 'a.cate_id', '=', 'c.id')
            ->leftJoin('article_map_tags as amt', 'a.id', '=', 'amt.article_id')
            ->leftJoin('article_tags as t', 'amt.tag_id', '=', 't.id')
            ->select(
                'a.id',
                'a.id_secure',
                'a.title',
                'a.status',
                'a.changed',
                'a.created',
                'a.type',
                'a.slug',
                'a.desc',
                'a.thumbnail',
                'a.custom_1',
                'a.custom_2',
                'a.custom_3',
                'c.name as category_name',
                DB::raw("GROUP_CONCAT(COALESCE(t.name, '') SEPARATOR ',') as tag_names")
            );

        if (isset($params['type'])) {
            $query->where('a.type', '=', $params['type']);
        }

        if (isset($params['cate_id']) && $params['cate_id'] != -1) {
            $query->where('a.cate_id', '=', $params['cate_id']);
        }

        if (isset($params['status']) && $params['status'] != -1) {
            $query->where('a.status', '=', $params['status']);
        }

        if (isset($params['search']) && trim($params['search']) !== "") {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->orWhere('a.title', 'like', "%{$search}%")
                  ->orWhere('a.desc', 'like', "%{$search}%")
                  ->orWhere('c.name', 'like', "%{$search}%");
            });
        }

        $query->groupBy('a.id')->orderBy($order_field, $order_sort);

        $pagination = $query->paginate($per_page);

        $data = $pagination->getCollection()->map(function ($record) {
            $record->tag_names = $record->tag_names ? explode(',', $record->tag_names) : [''];
            return (array)$record;
        })->toArray();

        return [
            'recordsTotal'    => $pagination->total(),
            'recordsFiltered' => $pagination->total(),
            'data'            => $data,
        ];
    }
    
    /**
     * Retrieve detailed information for a specific article.
     *
     * This method fetches article data along with:
     *  - Category details (name) from article_categories.
     *  - Aggregated tag details (name) from article_tags via article_map_tags,
     *    returned as arrays.
     *
     * @param int $articleId The ID of the article for which details are needed.
     *
     * @return object|null The article details object (or null if not found).
     */
    public static function getArticlesDetail($articleId)
    {
        if (!$articleId) return false;

        $tagsSub = DB::table('article_map_tags as amt')
            ->join('article_tags as t', 'amt.tag_id', '=', 't.id')
            ->select(
                'amt.article_id',
                DB::raw("GROUP_CONCAT(DISTINCT t.id ORDER BY t.id SEPARATOR ',')  AS tag_ids"),
                DB::raw("GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ',') AS tag_names")
            )
            ->groupBy('amt.article_id');

        $article = DB::table('articles as a')
            ->leftJoin('article_categories as c', 'a.cate_id', '=', 'c.id')
            ->leftJoinSub($tagsSub, 'tg', function ($join) {
                $join->on('tg.article_id', '=', 'a.id');
            })
            ->select(
                'a.*',
                'c.name as category_name',
                'tg.tag_ids',
                'tg.tag_names'
            )
            ->where('a.id_secure', $articleId) 
            ->first();

        if (!$article) return null;

        $article->tag_ids   = $article->tag_ids ? explode(',', $article->tag_ids) : [''];
        $article->tag_names = $article->tag_names ? explode(',', $article->tag_names) : [''];

        return $article;
    }
}
