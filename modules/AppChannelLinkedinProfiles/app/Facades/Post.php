<?php
namespace Modules\AppChannelLinkedinProfiles\Facades;

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
                "",
                "",
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
        if (!in_array($post->type, ['media', 'link', 'text'])) {
            $errors[] = __("LinkedIn API currently supports only 'media', 'link', or 'text' post types.");
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
     * Publishes a post to LinkedIn using the LinkedinAPI.
     *
     * Supported types:
     * - text: text-only post.
     * - link: post text with an appended link.
     * - media: post one or more images.
     *
     * Note: Video posts are not supported in this example.
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

        // Retrieve the person ID from LinkedIn.
        $person_id = $linkedin->getPersonID($accessToken);

        $data    = json_decode($post->data, false);
        $medias  = $data->medias ?? [];
        $caption = spintax($data->caption);
        // Optionally, get a first comment from advanced options.
        $comment = $data->advance_options->linkedin_first_comment ?? '';
        // For link posts.
        $link       = $data->link ?? '';
        $link_title = $data->advance_options->link_title ?? '';
        $link_desc  = $data->advance_options->link_description ?? '';
        $visibility = "PUBLIC";  // Default visibility.


        // Posting logic based on the post type.
        switch ($post->type) {
            case 'text':
                // Text-only post.
                $response = $linkedin->linkedInTextPost($accessToken, $person_id, $caption, $visibility);
                break;

            case 'link':
                // Post with a link.
                $response = $linkedin->linkedInLinkPost($accessToken, $person_id, $caption, $link_title, $link_desc, $link, $visibility);
                break;

            case 'media':
                if (count($medias) > 1) {
                    // Multi-media post (multiple images).
                    $images = [];
                    foreach ($medias as $media) {
                        $img_arr['image_path'] = watermark($media, $post->account->team_id, $post->account->id);
                        $img_arr['desc']       = $caption;
                        // You can customize the title; here we use a substring of the caption.
                        $img_arr['title']      = substr($caption, 0, 200);
                        $images[] = $img_arr;
                    }
                    $response = $linkedin->linkedInMultiplePhotosPost($accessToken, $person_id, $caption, $images, $visibility);
                } else {
                    // Single media post (image or video).
                    $media = $medias[0] ?? null;
                    $media = Media::url($medias[0]);
                    if (!$media) {
                        return self::errorResponse(__("No media provided for single media post."), $post->type);
                    }
                    if (Media::isVideo($media)) {
                        //return self::errorResponse(__("LinkedIn video posts are not supported."), $post->type);
                        $response   = $linkedin->linkedInVideoPost($accessToken, $person_id, $caption, $media, substr($caption, 0, 200), substr($caption, 0, 200), $visibility);
                    } elseif (Media::isImg($media)) {
                        // For a single image, apply watermark and post.
                        $image_path = watermark($media, $post->account->team_id, $post->account->id);
                        $response   = $linkedin->linkedInPhotoPost($accessToken, $person_id, $caption, $image_path, substr($caption, 0, 200), substr($caption, 0, 200), $visibility);
                    } else {
                        return self::errorResponse(__("Unsupported media type."), $post->type);
                    }
                }
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