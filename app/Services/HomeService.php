<?php

namespace App\Services;

use App\Models\Articles;
use App\Models\ArticleCategories;
use App\Models\ArticleTags;
use App\Models\ArticleMapTags;

class HomeService
{

    public function countPostBlog()
    {
        return Articles::with(['category', 'tags'])->count();
    }

    public function getBlogs()
    {
        $perPage = request()->input('per_page', 12);
        $page = request()->input('page', 1);
        $keyword = request()->input('keyword');
        $segments = request()->segments();
        $cate_slug = null;
        $tag_slug = null;

        if (isset($segments[1]) && $segments[1] === 'tag' && isset($segments[2])) {
            $tag_slug = $segments[2];
        } elseif (isset($segments[1]) && $segments[1] !== 'tag') {
            $cate_slug = $segments[1];
        }

        $query = Articles::with(['category', 'tags'])
            ->where('status', 1)
            ->where('type', 'blog');

        if ($cate_slug) {
            $query->whereHas('category', function ($q) use ($cate_slug) {
                $q->where('slug', $cate_slug);
            });
        }

        if ($tag_slug) {
            $query->whereHas('tags', function ($q) use ($tag_slug) {
                $q->where('slug', $tag_slug);
            });
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                    ->orWhere('desc', 'like', "%$keyword%")
                    ->orWhere('content', 'like', "%$keyword%");
            });
        }

        $articles = $query->orderByDesc('created')
            ->paginate($perPage, ['*'], 'page', $page);

        return $articles;
    }

    public function getBlogCategories()
    {
        return ArticleCategories::select('id', 'name', 'slug', 'icon', 'color', 'desc')
            ->withCount(['articles' => function ($q) {
                $q->where('status', 1)->where('type', 'blog');
            }])
            ->orderByDesc('articles_count')
            ->get();
    }

    public function getBlogTags($limit = 10)
    {
        return ArticleTags::select('id', 'name', 'slug', 'icon', 'color', 'desc')
            ->withCount(['articles' => function ($q) {
                $q->where('status', 1)->where('type', 'blog');
            }])
            ->orderByDesc('articles_count')
            ->limit($limit)
            ->get();
    }

    public function getBlogDetail($slug = null)
    {
        // Nếu chưa truyền vào thì tự lấy từ segment(2)
        if (!$slug) {
            $slug = request()->segment(2);
        }

        return Articles::with(['category', 'tags'])
            ->where('status', 1)
            ->where('type', 'blog')
            ->where('slug', $slug)
            ->first();
    }

    public function getRecentBlogs($currentSlug = null, $limit = 5)
    {
        if (!$currentSlug) {
            $currentSlug = request()->segment(2);
        }

        return Articles::with(['category', 'tags'])
            ->where('status', 1)
            ->where('type', 'blog')
            ->when($currentSlug, function ($query) use ($currentSlug) {
                $query->where('slug', '!=', $currentSlug);
            })
            ->orderByDesc('created')
            ->limit($limit)
            ->get();
    }

    public function getFaqs($limit = 20)
    {
        return Articles::with('category')
            ->where('type', 'faqs')
            ->where('status', 1) 
            ->orderByDesc('created')
            ->limit($limit)
            ->get();
    }
}
