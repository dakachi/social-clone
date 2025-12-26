<?php
namespace Modules\AppChannelYouTubeChannels\Facades;

use Illuminate\Support\Facades\Facade;
use Google\Client;
use Google\Service\YouTube;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    protected static $client;

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }


    /**
     * Initialize and retrieve the YouTube API instance.
     *
     * @return YouTube
     */
    protected static function initYouTube()
    {
        self::$client = new Client();
        self::$client->setClientId(get_option("youtube_client_id", ""));
        self::$client->setClientSecret(get_option("youtube_client_secret", ""));
        self::$client->setDeveloperKey(get_option("youtube_api_key", ""));
        self::$client->setApplicationName("Youtube");
        self::$client->setApprovalPrompt("force");
        self::$client->setAccessType('offline');
        self::$client->setScopes(
            [
                'https://www.googleapis.com/auth/youtube', 
                'https://www.googleapis.com/auth/userinfo.email'
            ]
        );

        return self::$client;
    }

    /**
     * Validate post data.
     *
     * @param object $post
     * @return array Array of errors if any.
     */
    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, false);
        $medias = $data->medias;
        $options = $data->options;

        // Validate video path and check if it's a valid video
        if (!empty($medias)) {
            $media = Media::url($medias[0]);
            if (!Media::isVideo($media)) {
                $errors[] = __( "YouTube only supports video uploads. Please provide a valid video file.");
            }
        }

        if(!isset($options->youtube_type) || $options->youtube_type == "short"){
            $getID3 = new \getID3;
            $fileInfo = $getID3->analyze( Media::path($medias[0]) );
            if(isset($fileInfo['video']) && isset($fileInfo['playtime_seconds'])){
                $resolution_x = $fileInfo['video']['resolution_x'];
                $resolution_y = $fileInfo['video']['resolution_y'];
                $playtime_seconds = $fileInfo['playtime_seconds'];
                $resolution = $resolution_x/$resolution_y;

                if($resolution < 0.5 || $resolution > 1 || $playtime_seconds > 180){
                    $errors[] = __("YouTube: The video resolution is not suitable for Shorts, or the video exceeds the 3-minute limit.");
                }
            }
        }

        if(!isset($options->youtube_title) || $options->youtube_title == ""){
            $errors[] = __("Youtube: The video must have a title.");
        }

        if(!isset($options->youtube_category) || (int)$options->youtube_category == 0){
            $errors[] = __("Youtube: Please choose a category for your video.");
        }

        return $errors;
    }

    /**
     * Main method to upload a video to YouTube.
     *
     * @param object $post
     * @return array Upload response.
     */
    protected static function post($post)
    {
        self::initYouTube();
        $tokenInfo = json_decode($post->account->token, false);
        self::$client->setAccessToken($post->account->token);
        if (isset($tokenInfo->refresh_token) && !empty($tokenInfo->refresh_token)) {
            $refreshed = self::$client->fetchAccessTokenWithRefreshToken($tokenInfo->refresh_token);
            if (is_array($refreshed) && isset($refreshed["error"])) {

                Accounts::where("id", $post->account->id)->update(["status" => 0]);
                return [
                    "status"  => "error",
                    "message" => __("Access Token Expired"),
                    "type"    => $post->type,
                ];
            }

            Accounts::where("id", $post->account->id)->update(["token" => json_encode($refreshed)]);
        }

        // Decode the JSON data from the post.
        $data = json_decode($post->data, false);
        $options = $data->options;
        $medias = $data->medias ?? [];
        $title = spintax($options->youtube_title);
        $description = spintax($data->caption);
        $categoryId = $options->youtube_category;
        $thumbnail = $options->youtube_thumbnail;
        $tags = $options->youtube_tags ?? "";
        $privary_status = false;

        //Check is Short Video
        if(!isset($options->youtube_type) || $options->youtube_type == "short"){
            $getID3 = new \getID3;
            $fileInfo = $getID3->analyze( Media::getPathFromUrl($medias[0]) );
            if(isset($fileInfo['video']) && isset($fileInfo['playtime_seconds'])){
                $resolution_x = $fileInfo['video']['resolution_x'];
                $resolution_y = $fileInfo['video']['resolution_y'];
                $playtime_seconds = $fileInfo['playtime_seconds'];
                $resolution = $resolution_x/$resolution_y;

                if($resolution < 0.5 || $resolution > 1 || $playtime_seconds > 180){
                    $errors[] = __("YouTube: The video resolution is not suitable for Shorts, or the video exceeds the 3-minute limit.");
                    return [
                        "status"  => "error",
                        "message" => __("YouTube: The video resolution is not suitable for Shorts, or the video exceeds the 3-minute limit."),
                        "type"    => $post->type,
                    ];
                }
            }
        }

        // Upload the video.
        $videoPath = Media::url($medias[0] ?? '');
        if (!$videoPath) {
            return self::errorResponse(__("No media provided for single media post."), $post->type);
        }

        if (!Media::isVideo($videoPath)) {
            return self::errorResponse(__("Unsupported post type"), $post->type);
        }

        try {
            return self::handleVideoUpload(
                $videoPath,
                $title,
                $description,
                $categoryId,
                $privary_status,
                $tags,
                $thumbnail
            );
        } catch (Google\Service\Exception $e) {
            $errors = $e->getErrors();
            if(!empty($errors) && isset($errors[0]["message"])){
                return [
                    "status"  => "error",
                    "message" => __($errors[0]["message"])
                ];
            }else{
                return [
                    "status"  => "error",
                    "message" => $e->getMessage()
                ];
            }
        } catch (Google\Exception $e) {
            $errors = $e->getErrors();
            if(!empty($errors) && isset($errors[0]["message"])){
                return [
                    "status"  => "error",
                    "message" => __($errors[0]["message"])
                ];
            }else{
                return [
                    "status"  => "error",
                    "message" => $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            return [
                "status"  => "error",
                "message" => $e->getMessage()
            ];
        }
    }

    /**
     * Upload the video to YouTube.
     *
     * @param string $videoPath
     * @param string $title
     * @param string $description
     * @param string $categoryId
     * @param bool   $privary_status
     * @param array  $tags
     * @return array Upload response.
     */
    protected static function handleVideoUpload($videoPath, $title, $description, $categoryId, $privary_status, $tags, $thumbnailUrl)
    {
        $youtube = new YouTube(self::$client);

        // Prepare video snippet
        $videoSnippet = new YouTube\VideoSnippet();
        $videoSnippet->setTitle($title);
        $videoSnippet->setDescription($description);
        $videoSnippet->setCategoryId($categoryId);

        if (!empty($tags)) {
            $videoSnippet->setTags($tags);
        }

        // Prepare video status
        $videoStatus = new YouTube\VideoStatus();
        $videoStatus->setPrivacyStatus('public');
        if ($privary_status) {
            $videoStatus->setPrivacyStatus('unlisted'); 
        }

        // Combine snippet and status
        $video = new YouTube\Video();
        $video->setSnippet($videoSnippet);
        $video->setStatus($videoStatus);

        // Upload video to YouTube
        $response = $youtube->videos->insert(
            'snippet,status',
            $video,
            [
                'data' => @file_get_contents($videoPath),
                'mimeType' => 'video/*',
                'uploadType' => 'multipart'
            ]
        );


        if($response->getStatus()->uploadStatus != "uploaded"){
            return self::errorResponse(__("Video upload unsuccessful. Please ensure that the video meets YouTube's upload requirements, including format, size, and duration."), "media");
        }

        ///////////////////////////////////////////////
        // Step 2: Set the thumbnail using a thumbnail URL
        ///////////////////////////////////////////////
        if ($thumbnailUrl) {
            $contextOptions = [
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ];
            $context = stream_context_create($contextOptions);
            $thumbnailContent = @file_get_contents($thumbnailUrl, false, $context);
            if($thumbnailContent){
                try {
                    $youtube->thumbnails->set(  $response->getId() , [
                        'data' => $thumbnailContent,
                    ]);
                } catch (\Exception $e) {}
            }
        }

        return [
            "status" => 1,
            "message" => __('Success'),
            "id" => $response->getId(),
            "url" => "https://www.youtube.com/watch?v=".$response->getId(),
            "type" => "media"
        ]; 
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message The error message.
     * @param string $type    The post type.
     * @return array A standardized error response.
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
