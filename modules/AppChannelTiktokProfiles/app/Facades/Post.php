<?php
namespace Modules\AppChannelTiktokProfiles\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannels\Models\Accounts;
use Media;
use gimucco\TikTokLoginKit;
use gimucco\TikTokLoginKit\Connector;
use gimucco\TikTokLoginKit\response\PublishStatus;
use gimucco\TikTokLoginKit\uploads\VideoFromUrl;
use getID3;

class Post extends Facade
{
    protected static $tiktok;

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    protected static function initTikTok($account)
    {
        $app_id = get_option("tiktok_app_id", "");
        $app_secret = get_option("tiktok_app_secret", "");
        $callback_url = module_url();

        self::$tiktok = new TikTokLoginKit\Connector($app_id, $app_secret, $callback_url);

        if ($account && $account->token) {
            $token = json_decode($account->token, true);
            if (!empty($token['access_token'])) {
                self::$tiktok->setToken($token['access_token']);
            }
        }
    }

    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];
        $options = $data->options ?? (object)[];

        if (empty($medias)) {
            $errors[] = __("TikTok: Please select a video.");
        } else {
            $media = Media::url($medias[0]);
            if (!Media::isVideo($media)) {
                $errors[] = __("TikTok only supports video uploads.");
            } else {
                $getID3 = new getID3;
                $fileInfo = $getID3->analyze(Media::path($medias[0]));
                if (isset($fileInfo['video'])) {
                    $w = $fileInfo['video']['resolution_x'] ?? 0;
                    $h = $fileInfo['video']['resolution_y'] ?? 0;
                    $duration = $fileInfo['playtime_seconds'] ?? 0;
                    if ($duration > 180) {
                        $errors[] = __("TikTok: Video must be no longer than 3 minutes.");
                    }
                    if ($w && $h && $w / $h > 1) {
                        $errors[] = __("TikTok: Video must be vertical.");
                    }
                    if ($w < 540 || $h < 960) {
                        //$errors[] = __("TikTok: Minimum video size is 540x960.");
                    }
                }
            }
        }

        if (empty($data->caption)) {
            $errors[] = __("TikTok: Please enter a caption for the video.");
        }

        return $errors;
    }

    protected static function post($post)
    {
        $account = $post->account;
        self::initTikTok($account);

        $tokenInfo = json_decode($account->token, true);
        if (!empty($tokenInfo['refresh_token'])) {
            try {
                $newToken = self::$tiktok->refreshToken($tokenInfo['refresh_token']);
                if ($newToken && $newToken->getAccessToken()) {
                    $account->token = json_encode([
                        "access_token" => $newToken->getAccessToken(),
                        "refresh_token" => $newToken->getRefreshToken(),
                        "expires_in" => $newToken->getExpiresIn(),
                        "refresh_expires_in" => $newToken->getRefreshExpiresIn(),
                        "scope" => implode(",", $newToken->getScope()),
                        "token_type" => $newToken->getTokenType(),
                    ]);
                    $account->save();
                    self::$tiktok->setToken($newToken->getAccessToken());
                }
            } catch (\Exception $e) {
                Accounts::where("id", $account->id)->update(["status" => 0]);
                return [
                    "status"  => "error",
                    "message" => __("TikTok session expired"),
                    "type"    => $post->type,
                ];
            }
        }

        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];
        $caption = $data->caption ?? '';
        $videoPath = Media::url($medias[0] ?? '');

        $errors = self::validator($post);
        if ($errors) {
            return [
                "status"  => "error",
                "message" => implode(', ', $errors),
                "type"    => $post->type,
            ];
        }

        try {
            $uploadResult = self::uploadVideo($videoPath, $caption);
            if ($uploadResult['status'] != 1) {
                return $uploadResult;
            }
            return [
                "status" => 1,
                "message" => __("Successfully posted to TikTok"),
                "id" => $uploadResult['id'],
                "url" => $uploadResult['url'],
                "type" => "media"
            ];
        } catch (\Exception $e) {
            return [
                "status" => 0,
                "message" => __("TikTok error: ") . $e->getMessage(),
                "type" => $post->type,
            ];
        }
    }

    protected static function uploadVideo($videoPath, $caption)
    {
        try {

            if(!get_option("tiktok_mode", 0)){
                $privacy = Connector::PRIVACY_PRIVATE;
                $comments_off  = true;
                $duet_off = true;
                $stitch_off = true;
            }else{
                $privacy = Connector::PRIVACY_PUBLIC;
                $comments_off  = false;
                $duet_off = false;
                $stitch_off = false;
            }


            $video = new VideoFromUrl($videoPath, $caption, $privacy, $comments_off, $duet_off, $stitch_off);
            $publishInfo = $video->publish(self::$tiktok);

            if (!$publishInfo || !$publishInfo->getPublishID()) {
                return [
                    "status" => 0,
                    "message" => "Failed to start video upload: " .
                        (method_exists($publishInfo, 'getErrorMessage') ? $publishInfo->getErrorCode(). " - " .$publishInfo->getErrorMessage() : 'Unknown error'),
                ];
            }

            $publishStatus = self::$tiktok->waitUntilPublished($publishInfo->getPublishID());

            if ($publishStatus->getStatus() == PublishStatus::PUBLISH_COMPLETE) {
                return [
                    "status" => 1,
                    "id" => $publishStatus->getPublicPostID(),
                    "url" => $publishStatus->getPublicPostID() ? "https://www.tiktok.com/@video/video/" . $publishStatus->getPublicPostID() : "https://www.tiktok.com/",
                ];
            } else {
                return [
                    "status" => 0,
                    "message" => $publishStatus->getErrorCode() . ": " . $publishStatus->getErrorMessage(),
                ];
            }
        } catch (\Exception $e) {
            return [
                "status" => 0,
                "message" => $e->getMessage(),
            ];
        }
    }
}
