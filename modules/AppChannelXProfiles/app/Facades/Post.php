<?php
namespace Modules\AppChannelXProfiles\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannelXProfiles\Classes\XApi;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    private static $xapi;

    /**
     * Initializes the XApi object and sets its token using the provided token value.
     *
     * @param string $token The access token from $post->account->token.
     */
    public static function initXApi($token = null)
    {
        if (!self::$xapi) {
            self::$xapi = new XApi(
                get_option("x_client_id", ""),
                get_option("x_client_secret", "")
            );
        }
        if ($token) {
            self::$xapi->setAccessToken($token);
        }
    }

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    /**
     * Validates the post object.
     *
     * @param object $post The post data object.
     * @return array A list of error messages.
     */
    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, true);
        $medias = $data['medias'] ?? [];

        // Validate allowed post types for X. Supported types: media, link, and text.
        if (!in_array($post->type, ['media', 'link', 'text'])) {
            $errors[] = __("The X API currently supports only posts of type 'media', 'link' or 'text'.");
        }

        // Validate advanced options if provided.
        if (isset($data['advance_options']['x_post_type'])) {
            $postType = $data['advance_options']['x_post_type'];
            switch ($postType) {
                case 'video':
                    if (!empty($medias) && Media::isImg($medias[0])) {
                        $errors[] = __("X API requires a video for the 'video' post type; images are not supported.");
                    }
                    break;
                // Additional validation cases can be added as needed.
                default:
                    break;
            }
        }

        return $errors;
    }

    /**
     * Publishes a post to X using the XApi.
     *
     * This method refreshes the access token before posting.
     * 
     * Supported types:
     * - text: post text only.
     * - link: post text with an appended link.
     * - video: post a single video.
     * - media: post one or more images.
     *
     * @param object $post The post object.
     * @return array The response from the X API.
     */
    protected static function post($post)
    {
        // Refresh the access token before proceeding.
        $tokenInfo = json_decode($post->account->token);

        self::initXApi($tokenInfo->access_token);
        if (isset($tokenInfo->refresh_token) && !empty($tokenInfo->refresh_token)) {
            $refreshed = self::$xapi->refreshToken($tokenInfo->refresh_token);
            if (is_array($refreshed) && isset($refreshed["status"]) && $refreshed["status"] === "0") {
                Accounts::where("id", $post->account->id)->update(["status" => 0]);
                return [
                    "status"  => "error",
                    "message" => __($refreshed["message"]),
                    "type"    => $post->type,
                ];
            }

            self::initXApi($refreshed->access_token);
            Accounts::where("id", $post->account->id)->update(["token" => json_encode($refreshed)]);
        }

        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];
        $caption = spintax($data->caption);
        $comment = $data->advance_options->x_first_comment ?? '';


        // Posting logic based on post type.
        switch ($post->type) {
            case 'text':
                // Text-only post.
                $response = self::$xapi->postTweet($caption, []);
                break;

            case 'link':
                // Append link if available.
                $link = $data->link ?? '';
                if ($link) {
                    $caption .= " " . $link;
                }
                $response = self::$xapi->postTweet($caption, []);
                break;

            case 'media':
                if (count($medias) > 1) {
                    // Multi-image (or multi-media) post.
                    $response = self::handleMultiMediaPost($medias, $caption, $comment, $post);
                } else {
                    // Single media post (image or video).
                    $response = self::handleSingleMediaPost($medias[0] ?? null, $post->type, $caption, $comment, $post);
                }
                break;

            default:
                // Unknown post type, return error.
                return [
                    "status"  => "error",
                    "message" => __("Unsupported post type"),
                    "type"    => $post->type,
                ];
        }

        if (isset($response->status) && $response->status != 200) {
            return self::errorResponse( __($response->detail) , $post->type);
        }

        return [
            "status"  => 1,
            "message" => __('Succeeded'),
            "id"      => $response->data->id,
            "url"     => "https://x.com/" . $response->data->id,
            "type"    => $post->type,
        ];
    }

    /**
     * Handles posting a single-media post.
     *
     * @param string $media      Media URL or file path.
     * @param string $mediaType  The type of media (video or media).
     * @param string $caption    The caption for the post.
     * @param string $comment    The first comment to post as a reply.
     * @param object $post       The post object.
     * @return array Response from X API or error structure if an error occurred.
     */
    protected static function handleSingleMediaPost($media, $mediaType, $caption, $comment, $post)
    {
        $mediaUrl = Media::url($media);

        // Upload the media using XApi and obtain a media ID.
        $mediaId = self::$xapi->uploadMedia($mediaUrl);

        $response = self::$xapi->postTweet($caption, $mediaId ? [$mediaId] : []);

        return $response;
    }

    /**
     * Handles posting a multi-media (multiple images) post.
     *
     * @param array  $medias  Array of media URLs or file paths.
     * @param string $caption The caption for the post.
     * @param string $comment The first comment to post as a reply.
     * @param object $post    The post object.
     * @return array Response from X API or error structure if an error occurred.
     */
    protected static function handleMultiMediaPost($medias, $caption, $comment, $post)
    {
        $mediaIds = [];
        foreach ($medias as $media) {
            $mediaUrl = Media::url($media);
            $mediaId = self::$xapi->uploadMedia($mediaUrl);
            if ($mediaId) {
                $mediaIds[] = $mediaId;
            }
        }
        
        $response = self::$xapi->postTweet($caption, $mediaIds);
        
        if ($comment && isset($response->data->id)) {
            self::postComment($response->data->id, $comment, $post);
        }
        
        return $response;
    }

    /**
     * Posts a comment/reply associated with a published post.
     *
     * @param string $postId The ID of the published post.
     * @param string $comment The comment message.
     * @param object $post    The post object.
     */
    protected static function postComment($postId, $comment, $post)
    {
        if ($comment) {
            try {
                // Uncomment and adjust the line below if the X API supports posting comments/replies.
                // self::$xapi->postComment($postId, $comment);
            } catch (\Exception $e) {
                // Silently ignore comment errors.
            }
        }
    }

    // Return an error response
    private static function errorResponse($message, $type)
    {
        return [
            "status"  => 0,
            "message" => __($message),
            "type"    => $type
        ];
    }
}
