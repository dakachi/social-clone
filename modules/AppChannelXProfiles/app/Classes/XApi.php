<?php
namespace Modules\AppChannelXProfiles\Classes;

class XApi {
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $access_token;
    private $params;
    
    /**
     * Constructor: Sets the required OAuth2 parameters.
     */
    public function __construct($client_id = null, $client_secret = null, $redirect_uri = null) {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        // Your callback (redirect_uri) URL
        $this->redirect_uri  = $redirect_uri;
        // Basic OAuth2 parameters
        $this->params = [
            'response_type' => 'code',
            'client_id'     => $client_id,
            'redirect_uri'  => $this->redirect_uri,
            // Adjust the scope as needed: tweet.read, tweet.write, users.read, offline.access, etc.
            'scope'         => 'tweet.read tweet.write users.read offline.access',
        ];
    }
    
    /**
     * Returns the login/authorization URL for X.
     */
    public function loginUrl() {
        return 'https://x.com/i/oauth2/authorize?' . http_build_query($this->params);
    }
    
    /**
     * Retrieves the access token after receiving the code from X.
     *
     * @param string $code The code received after user authorization.
     * @return mixed The access token on success, or an error array on failure.
     */
    public function getAccessToken($code) {
        if ($code) {
            $params = [
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->redirect_uri,
            ];
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => 'https://api.twitter.com/2/oauth2/token?' . http_build_query($params),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($params),
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/x-www-form-urlencoded"
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);
            
            $resp = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($resp);
            
            if (isset($result->access_token)) {
                $this->access_token = $result->access_token;
                return $result->access_token;
            } else {
                return [
                    "status"  => "0",
                    "message" => isset($result->error_description) ? __($result->error_description) : __("Unknown error")
                ];
            }
        } else {
            return [
                "status"  => "0",
                "message" => __("Please enter X code")
            ];
        }
    }
    
    /**
     * Exchanges a refresh token for a new access token.
     *
     * @param string $refresh_token The refresh token.
     * @return mixed The new access token on success, or an error array on failure.
     */
    public function refreshToken($refresh_token) {
        if (!$refresh_token) {
            return [
                "status"  => "0",
                "message" => __("Please provide a valid refresh token")
            ];
        }

        $params = [
            'refresh_token' => $refresh_token,
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->client_id,
        ];

        $basicAuth = base64_encode("{$this->client_id}:{$this->client_secret}");

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.twitter.com/2/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_HTTPHEADER     => [
                "Authorization: Basic {$basicAuth}",
                "Content-Type: application/x-www-form-urlencoded",
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $resp = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($resp);

        if (isset($result->access_token)) {
            $this->access_token = $result->access_token;
            return $result;
        } else {
            return [
                "status"  => "0",
                "message" => $result->error_description ?? __("Unknown error")
            ];
        }
    }
    
    /**
     * Sets the access token for the current session.
     */
    public function setAccessToken($access_token) {
        $this->access_token = $access_token;
    }
    
    /**
     * Retrieves user information from X.
     */
    public function getUserInfo() {
        $url = 'https://api.twitter.com/2/users/me?user.fields=id,name,username,profile_image_url';
        return $this->curlGet($url);
    }
    
    /**
     * Posts a tweet (or X post) with text and optionally media.
     *
     * @param string $tweet_text The content of the post.
     * @param array  $media_ids  (Optional) An array containing media IDs that have been uploaded.
     * @return mixed The response from the X API.
     */
    public function postTweet($tweet_text, $media_ids = []) {
        $data = ['text' => $tweet_text];
        if (!empty($media_ids)) {
            $data['media'] = ['media_ids' => $media_ids];
        }
        $url = 'https://api.twitter.com/2/tweets';
        return $this->curlPost($url, json_encode($data));
    }
    
    /**
     * Uploads a media file (image or video) to X.
     *
     * @param string $file_path The local file path of the media.
     * @return mixed The media_id on success, or false on failure.
     */
    public function uploadMedia($media) {
        $is_url = filter_var($media, FILTER_VALIDATE_URL);
        $tmpfile = null;

        if ($is_url) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                    'allow_self_signed'=> true,
                ]
            ]);
            $file_contents = @file_get_contents($media, false, $context);
            if ($file_contents === false) {
                return false;
            }
            $pathinfo = pathinfo(parse_url($media, PHP_URL_PATH));
            $ext = strtolower($pathinfo['extension'] ?? 'jpg');
            $tmpfile = tempnam(sys_get_temp_dir(), 'xmedia_') . '.' . $ext;
            file_put_contents($tmpfile, $file_contents);
            $file_path = $tmpfile;
        } else {
            if (!file_exists($media)) {
                return false;
            }
            $file_path = realpath($media);
            $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        }

        if (!$file_path || !file_exists($file_path)) {
            if ($tmpfile) @unlink($tmpfile);
            return false;
        }

        $image_exts = ['jpg', 'jpeg', 'png', 'webp', 'bmp'];
        $video_exts = ['mp4', 'mov', 'avi', 'wmv', 'webm', 'mkv', 'm4v'];
        $gif_exts   = ['gif'];

        // Tự detect loại media
        if (in_array($ext, $image_exts)) {
            // Ảnh: upload trực tiếp, dùng API v2 chuẩn hơn
            $result = $this->uploadImageV2($file_path);
            if ($tmpfile) @unlink($tmpfile);
            return $result;
        } elseif (in_array($ext, $video_exts) || in_array($ext, $gif_exts)) {
            // Video hoặc GIF: dùng API v2 resumable
            $result = $this->uploadVideoV2($file_path, $ext);
            if ($tmpfile) @unlink($tmpfile);
            return $result;
        } else {
            if ($tmpfile) @unlink($tmpfile);
            return false;
        }
    }

    public function uploadImageV2($file_path) {
        $cfile = new \CURLFile($file_path);
        $post_fields = [
            'media' => $cfile,
            'media_category' => 'tweet_image'
        ];
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.twitter.com/2/media/upload',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post_fields,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->access_token}"
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $result = json_decode($response);
        return $result->data->id ?? $result->id ?? false;
    }

    public function uploadVideoV2($file_path, $ext) {
        $size = filesize($file_path);
        $media_type = ($ext === 'gif') ? 'image/gif' : 'video/mp4';
        $media_category = ($ext === 'gif') ? 'tweet_gif' : 'tweet_video';

        // Step 1: Initialize
        $post_data = [
            'media_category' => $media_category,
            'media_type' => $media_type,
            'total_bytes' => $size
        ];
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.twitter.com/2/media/upload/initialize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->access_token}",
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
        $media_id = $result->data->id ?? null;
        if (!$media_id) return false;

        // Step 2: Append (chunk upload)
        $file = fopen($file_path, 'rb');
        $segment_index = 0;
        $chunk_size = 5 * 1024 * 1024;
        while (!feof($file)) {
            $chunk = fread($file, $chunk_size);

            $boundary = uniqid();
            $delimiter = '-------------' . $boundary;

            $body = "--$delimiter\r\n"
                . "Content-Disposition: form-data; name=\"media_id\"\r\n\r\n"
                . "$media_id\r\n"
                . "--$delimiter\r\n"
                . "Content-Disposition: form-data; name=\"segment_index\"\r\n\r\n"
                . "$segment_index\r\n"
                . "--$delimiter\r\n"
                . "Content-Disposition: form-data; name=\"media\"; filename=\"file.$ext\"\r\n"
                . "Content-Type: application/octet-stream\r\n\r\n"
                . $chunk . "\r\n"
                . "--$delimiter--\r\n";

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.twitter.com/2/media/upload/append',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$this->access_token}",
                    "Content-Type: multipart/form-data; boundary=$delimiter"
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);
            curl_exec($curl);
            curl_close($curl);

            $segment_index++;
        }
        fclose($file);

        // Step 3: Finalize
        $post_data = [
            'media_id' => $media_id
        ];
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.twitter.com/2/media/upload/finalize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->access_token}",
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);

        // Step 4: Poll status nếu cần
        $state = $result->data->processing_info->state ?? 'succeeded';
        $tries = 0; $maxTries = 30;
        while (($state === 'pending' || $state === 'in_progress') && $tries < $maxTries) {
            sleep(2);
            $status = $this->getMediaStatusV2($media_id);
            $state = $status->data->processing_info->state ?? 'succeeded';
            $tries++;
        }

        return ($state === 'succeeded') ? $media_id : false;
    }

    public function getMediaStatusV2($media_id) {
        $url = "https://api.twitter.com/2/media/upload?media_id=$media_id&command=STATUS";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->access_token}"
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
                        
    /**
     * Executes a GET request using cURL.
     *
     * @param string $url The URL to request.
     * @return mixed JSON-decoded response.
     */
    public function curlGet($url) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->access_token}"
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    
    /**
     * Executes a POST request using cURL.
     *
     * @param string $url        The URL to request.
     * @param string $postFields The POST data (JSON encoded if needed).
     * @return mixed JSON-decoded response.
     */
    public function curlPost($url, $postFields) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->access_token}",
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}
