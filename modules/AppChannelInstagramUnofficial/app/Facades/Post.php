<?php
namespace Modules\AppChannelInstagramUnofficial\Facades;

use Illuminate\Support\Facades\Facade;

class Post extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    protected static function validator($post)
    { 
        $errors = [];
        return $errors;
        $data = is_string($post->data) ? json_decode($post->data, false) : $post->data;
        $medias = $data->medias ?? [];

        if (!in_array($post->type, ['media', 'video'])) {
            $errors[] = __("The Instagram API currently supports posts with the 'Photo' or 'Video' type only.");
        } else {
            if (empty($medias) || (!\Media::isImg($medias[0]) && !\Media::isVideo($medias[0]))) {
                $errors[] = __("Please select at least one valid image or video file.");
            }
        }

        if (isset($data->options->ig_type)) {
            $postType = $data->options->ig_type;

            switch ($postType) {
                case 'reels':
                    if (empty($medias) || !\Media::isVideo($medias[0])) {
                        $errors[] = __("Instagram Reels only supports videos (3s to 15m). Images are not accepted.");
                    }
                    break;
                case 'stories':
                    if (empty($medias) || (!\Media::isImg($medias[0]) && !\Media::isVideo($medias[0]))) {
                        $errors[] = __("Instagram Stories only supports images or videos.");
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Post to Instagram: photo, video, carousel, story, reels
     * @param object|array $post
     * @return array
     */
    public static function post($post)
    {
        if (is_array($post)) $post = (object) $post;

        // Decode post data from JSON
        $data = is_string($post->data) ? json_decode($post->data, false) : $post->data;
        $medias = $data->medias ?? [];
        $caption = $data->caption ?? '';
        $comment = $data->options->ig_comment ?? '';
        $ig_type = $data->options->ig_type ?? 'feed'; // feed, stories, reels
        $ig_comment = $data->options->ig_comment ?? '';
        $ig_pin = $data->options->ig_pin ?? '';

        // Get authData from account token (JSON string)
        $authData = [];
        if (!empty($post->account->token)) {
            $authData = json_decode($post->account->token, true) ?: [];
        }

        if (empty($authData)) {
            return [
                "status" => false,
                "message" => __("You have not authorized your Instagram account yet. Please re-login and try again"),
                "type" => $post->type
            ];
        }

        $proxy = \Proxy::getProxyById($post->account->proxy);

        // Set session for the current post
        \IGUnofficial::setAuthData($authData);

        if ($proxy) {
            \IGUnofficial::setProxy($proxy->proxy);
        }

        $options = [
            'pin' => $ig_pin,
        ];

        if ($ig_type === 'stories' || $ig_type === 'story') {
            if (\Media::isImg($medias[0])) {
                $result = \IGUnofficial::postStoryPhoto($medias[0], $caption, $options);
            } else {
                $result = \IGUnofficial::postStoryVideo($medias[0], $caption, $options);
            }
        } else if (count($medias) > 1) {
            // Carousel
            $result = \IGUnofficial::postCarousel($medias, $caption, $options);
        } else {
            $mainMedia = $medias[0];
            if (\Media::isVideo($mainMedia)) {
                $result = \IGUnofficial::postReel($mainMedia, $caption, $options);
            } else {
                $result = \IGUnofficial::postPhoto($mainMedia, $caption, $options);
            }
        }

        // Add comment after posting, if provided and post was successful
        if (
            ($result['status'] ?? false) === true
            && !empty($comment)
            && !empty($result['data']['media_id'] ?? null)
        ) {
            $cmt = \IGUnofficial::commentOnMedia($comment, $result['data']['media_id']);
            $result['comment'] = $cmt;
        }

        // Return a unified result format
        return [
            "status"  => $result['status'] ? 1 : 0,
            "message" => $result['status'] ? __('Success') : ($result['error'] ?? 'Failed'),
            "id"      => $result['data']['media_id'] ?? null,
            "url"     => $result['data']['url'] ?? null,
            "type"    => $ig_type,
            "comment" => $result['comment'] ?? null,
        ];
    }


}
