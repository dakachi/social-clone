<?php
namespace Modules\AppAIPublishing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Pagination\Paginator;
use Modules\AppPublishing\Models\PostStat;
use Modules\AppChannels\Models\Accounts;

class AIPosts extends Model
{
    use HasFactory;

    protected $fillable = [];
    public $timestamps = false;
    protected $table = 'ai_posts';

    protected $casts = [
        "accounts" => "array",
        "prompts" => "array",
        "data" => "array",
    ];

    public static function getAIPostsList(array $params)
    {
        $search      = $params['keyword'] ?? null;
        $currentPage = $params['page'] ?? 1;
        $perPage     = $params['length'] ?? 30;

        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $postsQuery = self::query()
            ->where("team_id", request()->team_id)
            ->orderByDesc('changed');

        if ($search) {
            $postsQuery->where('name', 'like', '%' . $search . '%');
        }

        $postsPaginator = $postsQuery->paginate($perPage, ['*'], 'page', $currentPage);
        $posts = $postsPaginator->getCollection();

        $ai_post_ids = $posts->pluck('id')->toArray();

        $post_stats = PostStat::where('method', 'ai')
            ->whereIn('query_id', $ai_post_ids)
            ->whereIn('status', [4, 5])
            ->selectRaw('query_id, status, COUNT(*) as total')
            ->groupBy('query_id', 'status')
            ->get();

        $ai_stats = [];
        foreach ($ai_post_ids as $id) {
            $ai_stats[$id] = ['success' => 0, 'failed' => 0];
        }
        foreach ($post_stats as $row) {
            if (!isset($ai_stats[$row->query_id])) continue;
            if ($row->status == 4) $ai_stats[$row->query_id]['success'] = $row->total;
            if ($row->status == 5) $ai_stats[$row->query_id]['failed'] = $row->total;
        }

        $allAccountIds = [];
        $allPromptIds  = [];

        foreach ($posts as $post) {
            $accountIds = is_array($post->accounts) ? $post->accounts : json_decode($post->accounts, true);
            if (!is_array($accountIds)) {
                $accountStr = str_replace(['[', ']'], '', $post->accounts);
                $accountIds = array_filter(explode(',', $accountStr));
            }
            $accountIds = array_map('intval', $accountIds);
            $post->decoded_accounts = $accountIds;
            $allAccountIds = array_merge($allAccountIds, $accountIds);

            $promptIds = is_array($post->prompts) ? $post->prompts : json_decode($post->prompts, true);
            if (!is_array($promptIds)) {
                $promptStr = str_replace(['[', ']'], '', $post->prompts);
                $promptIds = array_filter(explode(',', $promptStr));
            }
            $promptIds = array_map('intval', $promptIds);
            $post->decoded_prompts = $promptIds;
            $allPromptIds = array_merge($allPromptIds, $promptIds);

            $post->success = $ai_stats[$post->id]['success'] ?? 0;
            $post->failed  = $ai_stats[$post->id]['failed'] ?? 0;
        }

        $allAccountIds = array_unique($allAccountIds);
        $allPromptIds  = array_unique($allPromptIds);

        $accountsData = [];
        if (!empty($allAccountIds)) {
            $accounts =  Accounts::whereIn('id', $allAccountIds)
                ->get();
            foreach ($accounts as $account) {
                $accountsData[$account->id] = $account;
            }
        }

        $promptsData = [];
        if (!empty($allPromptIds)) {
            $prompts = \DB::table('ai_prompts')
                ->whereIn('id', $allPromptIds)
                ->get();
            foreach ($prompts as $prompt) {
                $promptsData[$prompt->id] = $prompt;
            }
        }

        $posts->transform(function ($post) use ($accountsData, $promptsData) {
            $accs = [];
            foreach ($post->decoded_accounts as $accountId) {
                if (isset($accountsData[$accountId])) {
                    $accs[] = $accountsData[$accountId];
                }
            }
            $prompts = [];
            foreach ($post->decoded_prompts as $promptId) {
                if (isset($promptsData[$promptId])) {
                    $prompts[] = $promptsData[$promptId];
                }
            }
            $post->setAttribute('accounts_data', $accs);
            $post->setAttribute('prompts_data', $prompts);
            unset($post->decoded_accounts, $post->decoded_prompts);
            return $post;
        });
        
        $postsPaginator->setCollection($posts);

        return $postsPaginator;
    }

    public static function getAIPosts(int $numberRecords)
    {
        $posts = self::query()
            ->where("time_post", "<=", time())
            ->where("status", 1)
            ->orderByDesc('changed')
            ->limit($numberRecords)
            ->get();

        $allAccountIds = [];
        $allPromptIds  = [];

        foreach ($posts as $post) {
            $accountIds = is_array($post->accounts) ? $post->accounts : json_decode($post->accounts, true);
            if (!is_array($accountIds)) {
                $accountStr = str_replace(['[', ']'], '', $post->accounts);
                $accountIds = array_filter(explode(',', $accountStr));
            }
            $accountIds = array_map('intval', $accountIds);
            $post->decoded_accounts = $accountIds;
            $allAccountIds = array_merge($allAccountIds, $accountIds);

            $promptIds = is_array($post->prompts) ? $post->prompts : json_decode($post->prompts, true);
            if (!is_array($promptIds)) {
                $promptStr = str_replace(['[', ']'], '', $post->prompts);
                $promptIds = array_filter(explode(',', $promptStr));
            }
            $promptIds = array_map('intval', $promptIds);
            $post->decoded_prompts = $promptIds;
            $allPromptIds = array_merge($allPromptIds, $promptIds);
        }

        $allAccountIds = array_unique($allAccountIds);
        $allPromptIds  = array_unique($allPromptIds);

        $accountsData = [];
        if (!empty($allAccountIds)) {
            $accounts =  Accounts::where('status', 1)
                ->whereIn('id', $allAccountIds)
                ->get();
            foreach ($accounts as $account) {
                $accountsData[$account->id] = $account;
            }
        }

        $promptsData = [];
        if (!empty($allPromptIds)) {
            $prompts = \DB::table('ai_prompts')
                ->whereIn('id', $allPromptIds)
                ->get();
            foreach ($prompts as $prompt) {
                $promptsData[$prompt->id] = $prompt;
            }
        }

        foreach ($posts as $post) {
            $accs = [];
            foreach ($post->decoded_accounts as $accountId) {
                if (isset($accountsData[$accountId])) {
                    $accs[] = $accountsData[$accountId];
                }
            }

            $prm = [];
            foreach ($post->decoded_prompts as $promptId) {
                if (isset($promptsData[$promptId])) {
                    $prm[] = $promptsData[$promptId];
                }
            }

            $post->setAttribute('accounts_data', $accs);
            $post->setAttribute('prompts_data', $prm);

            unset($post->decoded_accounts, $post->decoded_prompts);
        }

        return $posts;
    }
}
