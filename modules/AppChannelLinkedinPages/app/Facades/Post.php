<?php
namespace Modules\AppChannelLinkedinPages\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannelLinkedinProfiles\Classes\LinkedinAPI;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    private static $linkedin;

    /**
     * Initializes the LinkedinAPI object and optionally loads the token.
     *
     * @param string|null $token The LinkedIn access token.
     */
    public static function initLinkedin($token = null)
    {
        if (!self::$linkedin) {
            // Initialize LinkedinAPI with app settings (replace get_option() with your config mechanism)
            self::$linkedin = new LinkedinAPI(
                get_option("linkedin_app_id", ""),
                get_option("linkedin_app_secret", ""),
                "", // callback URL if needed
                "", // scopes
                false
            );
        }
        // Note: The LinkedinAPI class does not include a setAccessToken() method.
        // We'll pass the access token into each method call.
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

        // Validate allowed post types for LinkedIn.
        if (!in_array($post->type, ['media', 'link', 'text', 'video'])) {
            $errors[] = __("LinkedIn API currently supports only 'media', 'link', 'text' or 'video' post types.");
        }

        // Validate advanced options if provided.
        if (isset($data['advance_options']['linkedin_post_type'])) {
            $postType = $data['advance_options']['linkedin_post_type'];
            switch ($postType) {
                case 'video':
                    if (!empty($medias) && Media::isImg($medias[0])) {
                        $errors[] = __("LinkedIn requires a video for the 'video' post type; images are not supported.");
                    }
                    break;
                default:
                    break;
            }
        }

        return $errors;
    }

    /**
     * Publishes a post to LinkedIn using LinkedinAPI.
     *
     * Supported types:
     * - text: text-only post.
     * - link: post text with an appended link.
     * - media: post one or more images.
     * - video: post a video.
     *
     * Additionally, if the account category is "page", the post is published on a company page.
     *
     * @param object $post The post object.
     * @return array The standardized response from the LinkedIn posting.
     */
    protected static function post($post)
    {
        $accessToken = $post->account->token;

        // Initialize LinkedinAPI.
        self::initLinkedin($accessToken);
        $linkedin = self::$linkedin;

        // Check if posting to a company page; if so override the author type.
        if (isset($post->account->category) && $post->account->category === "page") {
            // For company page posting, set the type to organization.
            $linkedin->setType("urn:li:organization:");
            // For company pages, we assume the company id is stored in $post->account->pid.
            $authorId = $post->account->pid; 
        } else {
            // For personal posts, use the person's id from LinkedIn.
            $authorId = $linkedin->getPersonID($accessToken); 
        }

        $data    = json_decode($post->data, false);
        $medias  = $data->medias ?? [];
        $caption = spintax($data->caption);
        $visibility = "PUBLIC";  // Default visibility.
        // For link posts.
        $link       = $data->link ?? '';
        $link_title = $data->advance_options->link_title ?? '';
        $link_desc  = $data->advance_options->link_description ?? '';
        // Optionally, get a first comment (not implemented in this snippet).
        $comment = $data->advance_options->linkedin_first_comment ?? '';

        switch ($post->type) {
            case 'text':
                $response = $linkedin->linkedInTextPost($accessToken, $authorId, $caption, $visibility);
                break;

            case 'link':
                $response = $linkedin->linkedInLinkPost($accessToken, $authorId, $caption, $link_title, $link_desc, $link, $visibility);
                break;

            case 'media':
                if (count($medias) > 1) {
                    // Multi-image post.
                    $images = [];
                    foreach ($medias as $media) {
                        $img_arr['image_path'] = Media::url($media);
                        $img_arr['desc']       = $caption;
                        $img_arr['title']      = substr($caption, 0, 200);
                        $images[] = $img_arr;
                    }
                    $response = $linkedin->linkedInMultiplePhotosPost($accessToken, $authorId, $caption, $images, $visibility);
                } else {
                    // Single media post.
                    $media_url = Media::url($medias[0] ?? '');
                    if (!$media_url) {
                        return self::errorResponse(__("No media provided for single media post."), $post->type);
                    }
                    if (Media::isVideo($media_url)) {
                        //return self::errorResponse(__("For video posts, please use the 'video' type."), $post->type);
                        $response   = $linkedin->linkedInVideoPost($accessToken, $authorId, $caption, $media_url, substr($caption, 0, 200), substr($caption, 0, 200), $visibility);
                        // Alternatively, you could call linkedInVideoPost() if supported.
                    } elseif (Media::isImg($media_url)) {
                        $image_path = $media_url;
                        $response   = $linkedin->linkedInPhotoPost($accessToken, $authorId, $caption, $image_path, substr($caption, 0, 200), substr($caption, 0, 200), $visibility);
                    } else {
                        return self::errorResponse(__("Unsupported media type."), $post->type);
                    }
                }
                break;

            case 'video':
                $media_url = Media::url($medias[0] ?? '');
                if (!$media_url) {
                    return self::errorResponse(__("No media provided for video post."), $post->type);
                }
                if (!Media::isVideo($media_url)) {
                    return self::errorResponse(__("Provided media is not a video."), $post->type);
                }
                $video_path = $media_url;
                $response   = $linkedin->linkedInVideoPost($accessToken, $authorId, $caption, $video_path, substr($caption, 0, 200), substr($caption, 0, 200), $visibility);
                break;

            default:
                return self::errorResponse(__("Unsupported post type"), $post->type);
        }

        $responseObj = json_decode($response);
        if (isset($responseObj->message)) {
            return self::errorResponse(__($responseObj->message), $post->type);
        }

        return [
            "status"  => 1,
            "message" => __('Succeeded'),
            "id"      => $responseObj->id ?? '',
            "url"     => "https://www.linkedin.com/feed/update/" . ($responseObj->id ?? ''),
            "type"    => $post->type,
        ];
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message The error message.
     * @param string $type    The post type.
     * @return array The error response.
     */
    protected static function errorResponse($message, $type)
    {
        return [
            "status"  => 0,
            "message" => __($message),
            "type"    => $type,
        ];
    }
}