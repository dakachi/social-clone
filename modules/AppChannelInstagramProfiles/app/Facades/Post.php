<?php
namespace Modules\AppChannelInstagramProfiles\Facades;

use Illuminate\Support\Facades\Facade;
use JanuSoftware\Facebook\Facebook;
use Media;

class Post extends Facade
{
    private static $fb;

    // Initialize the Facebook object using a static method
    public static function initFacebook()
    {
        if (!self::$fb) {
            self::$fb = new Facebook([
                'app_id' => get_option("instagram_app_id", ""),
                'app_secret' => get_option("instagram_app_secret", ""),
                'default_graph_version' => get_option("instagram_graph_version", "v21.0"),
            ]);
        }
    }

    protected static function getFacadeAccessor()
    { 
        return ex_str(__NAMESPACE__);
    }

    protected static function validator($post)
    { 
        $errors = [];
        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];

        // Validate post type
        if (!in_array($post->type, ['media', 'link'])) {
             $errors[] = __("The Instagram API currently supports posts with the 'Photo' type only.");
        }else{
            if (empty($medias) || (!Media::isImg($medias[0]) && !Media::isVideo($medias[0])) ) {
                $errors[] = __("The Instagram API currently supports posts with the 'Photo' type only.");
            }
        }

        // Validate advanced options (e.g., reels, stories)
        if (isset($data->options->ig_type)) {
            $postType = $data->options->ig_type;

            switch ($postType) {
                case 'reels':
                    if (empty($medias) || !Media::isVideo($medias[0])) {
                        $errors[] = __("Instagram Reels only supports videos. Please ensure your video is between 3 seconds and 15 minutes long, as images are not accepted.");
                    }
                    break;
            }
        }

        return $errors;
    }

    // The static method to handle post publishing
    protected static function post($post)
    {
        self::initFacebook(); // Ensure Facebook SDK is initialized
        $data = json_decode($post->data, false);
        $medias = $data->medias;

        $caption = spintax($data->caption);
        $post_type = $data->options->ig_type ?? 'media';
        $comment = $data->options->ig_comment ?? '';
        $endpoint = "/" . $post->account->pid . "/media_publish";
        $upload_endpoint = "/" . $post->account->pid . "/media";

        try {
            switch ($post_type) {
                case 'stories':
                case 'reels':
                    return self::handleSingleMediaPost($medias[0], $post_type, $upload_endpoint, $endpoint, $caption, $comment, $post);
                default:
                    return count($medias) === 1
                        ? self::handleSingleMediaPost($medias[0], "media", $upload_endpoint, $endpoint, $caption, $comment, $post)
                        : self::handleCarouselPost($medias, $upload_endpoint, $endpoint, $caption, $comment, $post);
            }
        } catch (\Exception $e) {
            unlink_watermark($medias);
            return [
                "status" => "error",
                "message" => __($e->getMessage()),
                "type" => $post->type
            ];
        }
    }

    protected static function handleSingleMediaPost($media, $media_type, $upload_endpoint, $endpoint, $caption, $comment, $post)
    {
        $media = Media::url($media);
        switch ($media_type) {
            case 'stories':
                $media_type = "STORIES";
                break;

             case 'reels':
                $media_type = "REELS";
                break;
            
            default:
                $media_type = Media::isImg($media)?"IMAGE":"REELS";
                break;
        }

        $upload_params = self::getMediaUploadParams($media, $caption, $media_type, $post);
        $upload_response = self::$fb->post($upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
        return self::publishPost($upload_response, $endpoint, $comment, $post, $media_type === "stories" ? "stories" : "p");
    }

    protected static function handleCarouselPost($medias, $upload_endpoint, $endpoint, $caption, $comment, $post)
    {
        $media_ids = [];
        foreach ($medias as $key => $media) {
            $upload_params = self::getMediaUploadParams($media, $caption, Media::isImg($media) ? "IMAGE" : "VIDEO", $post, true);
            $upload_response = self::$fb->post($upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
            $media_ids[] = $upload_response['id'];
        }

        $upload_params = [
            'media_type' => 'CAROUSEL',
            'children' => $media_ids,
            'caption' => $caption
        ];
        $upload_response = self::$fb->post($upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();

        return self::publishPost($upload_response, $endpoint, $comment, $post, "p");
    }

    protected static function getMediaUploadParams($media, $caption, $media_type, $post, $is_carousel_item = false)
    {
       
        if (!Media::isImg($media) && !Media::isVideo($media) ) {
            throw new \Exception( __("Currently, Instagram only supports posting with videos or images.") );
        }

        $params = Media::isImg($media) 
            ? [
                'media_type' => $media_type,
                'image_url' => Media::url(watermark($media, $post->account->team_id, $post->account->id)),
                'caption' => $caption
              ]
            : [
                'media_type' => $media_type,
                'video_url' => Media::url($media),
                'caption' => $caption
              ];

        if ($is_carousel_item) {
            $params['is_carousel_item'] = true;
        }

        return $params;
    }

    protected static function publishPost($upload_response, $endpoint, $comment, $post, $url_type)
    {
        $attempts = 0;
        do {
            $attempts++;
            sleep(2);
            try {

                $params = ['creation_id' => $upload_response['id']];
                $response = self::$fb->post($endpoint, $params, $post->account->token)->getDecodedBody();

                if (isset($response["id"])) {
                    self::postComment($response["id"], $comment, $post);
                    $media_response = self::$fb->get("/" . $response["id"] . "?fields=shortcode", $post->account->token)->getDecodedBody();

                    return [
                        "status" => 1,
                        "message" => __('Succesed'),
                        "id" => $response["id"],
                        "url" => "https://www.instagram.com/{$url_type}/" . $media_response['shortcode'],
                        "type" => $post->type
                    ];
                }
            } catch (\Exception $e) {
                if ($attempts >= 30) throw $e;
            }
        } while ($attempts <= 30);

        return [
            "status" => 0,
            "message" => __('The media is not ready for publishing, please wait for a moment'),
        ];
    }

    protected static function postComment($post_id, $comment, $post)
    {
        if ($comment) {
            try {
                self::$fb->post("/" . $post_id . "/comments", [
                    "message" => $comment
                ], $post->account->token)->getDecodedBody();
            } catch (\Exception $e) {
                // Silently ignore comment errors
            }
        }
    }

}


