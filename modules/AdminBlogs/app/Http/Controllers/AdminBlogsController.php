<?php

namespace Modules\AdminBlogs\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Articles;
use App\Models\ArticleTags;
use App\Models\ArticleMapTags;
use App\Models\ArticleCategories;
use DB;

class AdminBlogsController extends Controller
{

    public $type = "blog";
    public $table = "articles";
    public $category_table = "article_categories";
    public $modules;
    public $Datatable;

    public function __construct()
    {
        $this->Datatable = [
            "element" => "DataTable",
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["articles.title", "articles.desc", "article_categories.name"],
            "columns" => [
                [
                    "name" => "id_secure",
                    "data"  => "id_secure",
                    "className" => "w-40"
                ],
                [
                    "name" => "title",
                    "data"  => "title",
                    "className" => "max-w-250",
                    "title"     => __('Detail')
                ],
                [
                    "name" => "desc",
                    "data"  => "desc",
                    "title"     => __('Desc')
                ],
                [
                    "name" => "thumbnail",
                    "data"  => "thumbnail",
                    "title"     => __('thumbnail')
                ],
                [
                    "name" => "article_categories.name",
                    "data"  => "article_categories_name",
                    'alias' => 'article_categories_name',
                    "title"     => __('Categories')
                ],
                [
                    'data' => 'created',
                    'name' => 'created',
                    'type' => 'datetime',
                    'title' => __('Created'),
                ],
                [
                    "name" => "status",
                    "data"  => "status",
                    "className" => "w-80",
                    "title"     => __('Status')
                ],
                [
                    "className" => "text-center",
                    "title"     => __('Action')
                ],
            ],

            'status_filter' => [
                ['value' => '-1', 'label' => __('All')],
                ['value' => '1', 'name' => 'enable', 'icon' => 'fa-light fa-eye', 'color' => 'success', 'label' => __('Enable')],
                ['value' => '0', 'name' => 'disable', 'icon' => 'fa-light fa-eye-slash', 'color' => 'light', 'label' => __('Disable')],
            ],

            'actions' => [
                [
                    'url'           => module_url("status/enable"),
                    'icon'          => 'fa-eye',
                    'label'         => __('Enable'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'url'           => module_url("status/disable"),
                    'icon'          => 'fa-light fa-eye-slash',
                    'label'         => __('Disable'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'divider'       => true
                ],
                [
                    'url'           => module_url("destroy"),
                    'icon'          => 'fa-light fa-trash-can-list',
                    'label'         => __('Delete'),
                    'confirm'       => __("Are you sure you want to delete this item?"),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
            ],
        ];
    }

    public function index()
    {
        $total = Articles::where("type", $this->type)->count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,
        ]);
    }

    public function list(Request $request)
    {
        $joins = [
            [
                "table" => "article_categories",
                 "first" => "article_categories.id",
                 "second" => $this->table.".cate_id",
                 "type" => "left"
            ]
        ];

        $whereConditions = [$this->table.'.type' => 'blog'];
        $dataTableService = \DataTable::make(Articles::class, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request, $postId = "")
    {
        $result = Articles::getArticlesDetail($postId);
        $categories = ArticleCategories::get();
        $tags = ArticleTags::get();

        return view(module("key").'::update', [
            "result" => $result,
            "categories" => $categories,
            "tags"      => $tags
        ]);
    }

    public function save(Request $request)
    {
        // Validation rules
        $rules = [
            'title'     => 'required|string|max:255',
            'desc'      => 'required|string|max:255',
            'status'    => 'required|boolean',
            'cate_id'   => 'required|integer|min:1',
            'content'   => 'required|string',
            'thumbnail' => 'required|string',
        ];

        $error_message = [
            "cate_id.min" => __("A category selection is required.")
        ];

        // Prepare data for saving
        $data = [
            'id_secure' => rand_string(),
            'cate_id'   => $request->input('cate_id'),
            'title'     => $request->input('title'),
            'slug'      => Str::slug($request->input('title')),
            'desc'      => $request->input('desc'),
            'content'   => $request->input('content'),
            'thumbnail' => \Media::getPathFromUrl($request->input('thumbnail')),
            'status'    => (int)$request->input('status'),
            'type'      => "blog",
            'changed'   => time(),
            'created'   => time(),
        ];

        if ($request->has('id')) {
            $data['id_secure'] = $request->input('id');
        }

        // Save or update the main article
        $response = \DBHelper::saveData($this->table, $rules, $data, ['id_secure', 'created', 'type'], $error_message);

        if( $response['status'] == 1){
            $articleId = $response['id'];

            //Handle tags if provided
            if ($request->has('tags') && is_array($request->input('tags'))) {
                ArticleMapTags::where('article_id', $articleId)->delete();
                foreach ($request->input('tags') as $tagId) {
                    ArticleMapTags::insert([
                        'article_id' => $articleId,
                        'tag_id'     => $tagId,
                    ]);
                }
            }
        }

        return response()->json($response);
    }

    public function status(Request $request, $status = "enable")
    {
        $status_update = $status;
        if(isset($this->Datatable['status_filter'])){
            foreach ($this->Datatable['status_filter'] as $value) {
                if (isset($value['name']) && $value['value'] != -1 && $value['name'] == $status) {
                    $status_update = $value['value'];
                    break;
                }
            }
        }
        $response = \DBHelper::updateField($this->table, $request->input('id'), 'status', $status_update);
        return response()->json($response);
    }


    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy($this->table, $request->input('id'));
        return response()->json($response);
    }

}
