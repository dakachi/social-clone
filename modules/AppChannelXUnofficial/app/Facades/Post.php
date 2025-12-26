<?php
namespace Modules\AppChannelXUnofficial\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannels\Models\Accounts;
use Media;

/**
 * Facade for posting to X (Twitter) Unofficial (keep class name Post).
 */
class Post extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    /**
     * Validate post data
     */
    public static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, true);
        $medias = $data['medias'] ?? [];

        if (!in_array($post->type, ['media', 'link', 'text'])) {
            $errors[] = __("X (Unofficial) only supports post types: text, link, or media.");
        }

        if (isset($data['advance_options']['x_post_type'])) {
            $postType = $data['advance_options']['x_post_type'];
            if ($postType == 'video' && !empty($medias) && Media::isImg($medias[0])) {
                $errors[] = __("Only videos are supported for the 'video' type.");
            }
        }
        return $errors;
    }

    /**
     * Publish a post to X (Unofficial)
     */
    public static function post($post)
    {
        $authData = json_decode($post->account->token, true);

        // Set authentication info to the Facade
        \XUnofficial::setCredentials(
            $authData['x_csrf_token'] ?? '',
            $authData['x_auth_token'] ?? '',
            $authData['x_screen_name'] ?? '',
            $authData['proxy'] ?? null
        );

        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];
        $caption = spintax($data->caption ?? '');
        $comment = $data->advance_options->x_first_comment ?? '';

        // Post according to type
        switch ($post->type) {
            case 'text':
                $response = \XUnofficial::createTweet($caption);
                break;
            case 'link':
                $link = $data->link ?? '';
                if ($link) $caption .= " " . $link;
                $response = \XUnofficial::createTweet($caption);
                break;
            case 'media':
                if (count($medias) > 1) {
                    $response = self::handleMultiMediaPost($medias, $caption, $comment, $post);
                } else {
                    $response = self::handleSingleMediaPost($medias[0] ?? null, $post->type, $caption, $comment, $post);
                }
                break;
            default:
                return static::errorResponse(__('Unsupported post type'), $post->type);
        }

        // Format response
        if (empty($response) || $response['status'] !== 1) {
            return static::errorResponse($response['error'] ?? __('Post failed.'), $post->type);
        }

        $tweetData = $response['data'] ?? [];

        $legacy = $tweetData['legacy'] ?? [];
        $userCore = $tweetData['core']['user_results']['result']['legacy'] ?? [];
        $userScreenName = $userCore['screen_name'] ?? null;

        $id = $legacy['id_str'] ?? ($tweetData['id_str'] ?? ($tweetData['id'] ?? null));
        $url = $id ? "https://x.com/$userScreenName/status/$id" : null;

        return [
            "status"    => 1,
            "message"   => __("Succeeded"),
            "id"        => $id,
            "url"       => $url,
            "type"      => $post->type ?? 'tweet'
        ];
    }

    /**
     * Handle posting a single media (image or video)
     */
    protected static function handleSingleMediaPost($media, $mediaType, $caption, $comment, $post)
    {
        if (empty($media)) return static::errorResponse(__('Media is required'), $mediaType);

        $mediaPath = Media::url($media);
        $upload = \XUnofficial::uploadMediaToTwitter($mediaPath);

        if (empty($upload['status']) || empty($upload['media_id'])) {
            return static::errorResponse($upload['error'] ?? __('Media upload failed'), $mediaType);
        }

        $response = \XUnofficial::createTweet($caption, [$upload['media_id']]);
        return $response;
    }

    /**
     * Handle posting multiple media (multi image)
     */
    protected static function handleMultiMediaPost($medias, $caption, $comment, $post)
    {
        $mediaIds = [];
        foreach ($medias as $media) {
            $mediaPath = Media::url($media);
            $upload = \XUnofficial::uploadMediaToTwitter($mediaPath);
            if (!empty($upload['media_id'])) {
                $mediaIds[] = $upload['media_id'];
            }
        }
        if (empty($mediaIds)) {
            return static::errorResponse(__('All media uploads failed'), 'media');
        }
        $response = \XUnofficial::createTweet($caption, $mediaIds);
        // Optionally: post comment (reply) here if needed.
        return $response;
    }

    /**
     * Post a comment/reply (if desired)
     */
    protected static function postComment($tweetId, $comment, $post)
    {
        // Depends on whether XUnofficialService supports this.
        // try {
        //     static::replyTweet($tweetId, $comment);
        // } catch (\Exception $e) {
        //     // Log error but do not break the flow
        // }
    }

    /**
     * Return a standardized error response
     */
    private static function errorResponse($message, $type)
    {
        return [
            "status"  => 0,
            "message" => __($message),
            "type"    => $type
        ];
    }
}
