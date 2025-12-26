<?php
namespace Modules\AppChannelInstagramUnofficial\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Modules\AppChannelInstagramUnofficial\Constants\InstagramEndpoints;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\RSA;
use FFMpeg\FFMpeg;

class InstagramUnofficialService
{
    protected array $device;
    protected ?string $userAgent;
    protected ?string $proxy = null;
    protected ?array $authData = null;

    public function __construct()
    {
        $this->device = $this->generateFakeDevice();
        $this->userAgent = $this->generateInstagramUserAgent($this->device);
    }

    /**
     * Set proxy for all HTTP requests
     */
    public function setProxy(?string $proxy): static
    {
        $this->proxy = $proxy;
        return $this;
    }

    protected function http($default = true): \Illuminate\Http\Client\PendingRequest
    {
        $http = \Http::timeout(60);

        if ($default) {
            $http = $http->withHeaders($this->getDefaultHeaders())->asForm();
        }

        if ($this->proxy) {
            $http = $http->withOptions([
                'proxy' => $this->proxy,
            ]);
        }

        return $http;
    }

    public function authenticate(string $username, string $password): array
    {
        try {
            $this->setAuthData([
                'username' => $username,
                'password' => $password
            ]);
            $this->prefill();
            $key = $this->sync();

            if ($key === false || empty($key['key_id']) || empty($key['pub_key'])) {
                return [
                    'status' => false,
                    'error' => __('Unable to retrieve public key for password encryption. Please try again later or contact support.')
                ];
            }

            $encPass = $this->encPass($password, $key['key_id'], $key['pub_key']);
            $countryCodes = [
                'country_code' => '1',
                'source' => ['default']
            ];
            $data = [
                'jazoest' => '22578',
                'country_codes' => [json_encode($countryCodes)],
                'phone_id' => $this->authData['phone_id'],
                'enc_password' => $encPass,
                'username' => $username,
                'adid' => $this->generateUuid(),
                'guid' => $this->authData['device_id'],
                'device_id' => $this->authData['android_device_id'],
                'google_tokens' => '[]',
                'login_attempt_count' => 0,
            ];

            $response = $this->http()->post(InstagramEndpoints::LOGIN, [
                'signed_body' => 'SIGNATURE.' . json_encode($data)
            ]);

            $respArr = $response->json();

            $authorization = $response->header('ig-set-authorization');
            $uid = $respArr['logged_in_user']['pk'] ?? $respArr['logged_in_user']['pk_id'] ?? null;

            if ($uid && !empty($authorization)) {
                $this->authData['authorization'] = is_array($authorization) ? ($authorization[0] ?? '') : $authorization;
                $this->authData['user_id'] = $uid;

                $profile_pic_url = $respArr['logged_in_user']['profile_pic_url'] ?? '';
                $profile_pic = '';
                if ($profile_pic_url) {
                    try {
                        $profile_pic = \UploadFile::storeSingleFileFromURL($profile_pic_url, 'tmp', 'ig_' . $username);
                    } catch (\Exception $e) {
                        $profile_pic = '';
                    }
                }

                return [
                    'status' => true,
                    'data' => [
                        'needs_challenge' => false,
                        'name' => $respArr['logged_in_user']['full_name'] ?? $username,
                        'username' => $username,
                        'profile_id' => $respArr['logged_in_user']['pk_id'],
                        'profile_pic' => $profile_pic,
                        'options' => [
                            'auth_data' => $this->authData
                        ]
                    ]
                ];
            }

            if (($respArr['message'] ?? '') === 'challenge_required' && !empty($respArr['challenge']['api_path'])) {

                return [
                    'status' => false,
                    'error' => __('We detected a security challenge on Instagram. Please confirm the login attempt in your Instagram app or email, then try again.')
                ];
                
                $apiPath = $respArr['challenge']['api_path'] ?? null;
                $ctx     = $respArr['challenge']['challenge_context'] ?? null;

                $choice  = 0;
                //$res = $this->sendSecurityCode($apiPath, $choice);

                $verification_method = '2';

                return [
                    'status' => true,
                    'data'   => [
                        'needs_challenge' => true,
                        'options' => [
                            'auth_data'           => $this->authData,
                            'verification_method' => $verification_method, // '1' hoặc '2'
                            'api_path'            => $apiPath,
                            'url'                 => $respArr['challenge']['url'] ?? null,
                            'native'              => (bool)($respArr['challenge']['native_flow'] ?? false),
                            'flow_nonce'          => $respArr['challenge']['challenge_context'] ?? null,
                            'challenge_auto'      => [
                                'status'   => (bool)($auto['status'] ?? false),
                                'selected' => $auto['selected'] ?? null,
                                'attempts' => $auto['attempts'] ?? null,
                                'error'    => $auto['error']    ?? null,
                            ],
                        ],
                    ],
                ];
            }

            if (isset($respArr['two_factor_info'])) {
                $this->authData['user_id'] = $respArr['two_factor_info']['pk'];
                $verification_method = '1';
                if (!empty($respArr['two_factor_info']['whatsapp_two_factor_on'])) $verification_method = '6';
                if (!empty($respArr['two_factor_info']['totp_two_factor_on'])) $verification_method = '3';
                return [
                    'status' => true,
                    'data' => [
                        'needs_challenge' => true,
                        'options' => [
                            'auth_data' => $this->authData,
                            'verification_method' => $verification_method,
                            'two_factor_identifier' => $respArr['two_factor_info']['two_factor_identifier'],
                            'obfuscated_phone_number' => $respArr['two_factor_info']['obfuscated_phone_number'] ?? ''
                        ]
                    ]
                ];
            }

            return [
                'status' => false,
                'error' => $this->parseInstagramError($respArr, __('Instagram login failed. Please try again or verify your credentials.')),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'error' => __('An unexpected error occurred during Instagram login: :msg', ['msg' => $e->getMessage()])
            ];
        }
    }

    public function verifyTwoFactorCode(string $two_factor_identifier, string $code, string $verification_method = '1'): array
    {
        try {
            $code = preg_replace('/\s+/', '', $code);
            $data = [
                "verification_code"      => $code,
                "phone_id"               => $this->authData['phone_id'],
                "_csrftoken"             => $this->generateToken(64),
                "two_factor_identifier"  => $two_factor_identifier,
                "username"               => $this->authData['username'],
                "trust_this_device"      => "0",
                "guid"                   => $this->authData['device_id'],
                "device_id"              => $this->authData['android_device_id'],
                "waterfall_id"           => $this->generateUuid(),
                "verification_method"    => $verification_method
            ];

            $response = $this->http()->post(InstagramEndpoints::TWO_FACTOR_LOGIN, [
                'signed_body' => 'SIGNATURE.' . json_encode($data)
            ]);

            if (!$response->successful()) {
                return [
                    'status' => false,
                    'error'  => $this->parseInstagramError($response, __('Two-factor authentication failed. Please check your code or try again.')),
                ];
            }

            $auth = $response->header('ig-set-authorization');
            $body = $response->json();
            $uid = $body['logged_in_user']['pk'] ?? $body['logged_in_user']['pk_id'] ?? null;

            if (empty($auth) || empty($uid)) {
                return [
                    'status' => false,
                    'error'  => $this->parseInstagramError($body, __('Two-factor authentication failed. Please check your code or try again.')),
                ];
            }

            $this->authData['authorization'] = $auth;
            $this->authData['user_id'] = $uid;
            return [
                'status' => true,
                'data'   => [
                    'name'       => $body['logged_in_user']['full_name'] ?? $this->authData['username'],
                    'username'   => $this->authData['username'],
                    'profile_id' => $uid,
                    'profile_pic'=> $body['logged_in_user']['profile_pic_url'] ?? '',
                    'options'    => [
                        'auth_data' => $this->authData
                    ]
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'error'  => __('An unexpected error occurred during two-factor authentication: :msg', ['msg' => $e->getMessage()])
            ];
        }
    }

    public function sendSecurityCode(string $apiPath, int $choice = 1): array
    {
        if (empty($apiPath)) {
            return [
                'status' => false,
                'error'  => 'Missing API path',
            ];
        }

        $apiPath = '/' . ltrim($apiPath, '/');
        $endpoint = "https://i.instagram.com/api/v1" . $apiPath;

        try {
            $data = [
                'choice' => $choice, // 0 = phone, 1 = email
                '_uuid'  => $this->authData['device_id'] ?? $this->generateUuid(),
                'guid'   => $this->authData['device_id'] ?? $this->generateUuid(),
                'device_id' => $this->authData['android_device_id'] ?? '',
                'login_attempt_count' => 0,
            ];

            $response = $this->http()
                ->asForm()
                ->post($endpoint, [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]);

            if (!$response->successful()) {
                return [
                    'status' => false,
                    'error'  => $this->parseInstagramError($response, 'Failed to send security code.'),
                    'http'   => $response->status(),
                    'body'   => $response->body(),
                ];
            }

            $json = $response->json();

            return [
                'status' => ($json['status'] ?? '') === 'ok',
                'data'   => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function submitSecurityCode(string $apiPath, string $securityCode): array
    {
        if (empty($apiPath) || empty($securityCode)) {
            return [
                'status' => false,
                'error'  => 'Missing API path or code',
            ];
        }

        $apiPath = '/' . ltrim($apiPath, '/');
        $endpoint = "https://i.instagram.com/api/v1" . $apiPath;

        try {
            $data = [
                'security_code' => $securityCode,
                '_uuid'         => $this->authData['device_id'] ?? $this->generateUuid(),
                'guid'          => $this->authData['device_id'] ?? $this->generateUuid(),
                'device_id'     => $this->authData['android_device_id'] ?? '',
                'login_attempt_count' => 0,
            ];

            $response = $this->http()
                ->asForm()
                ->post($endpoint, [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]);

            $json = $response->json();

            // Nếu thành công → IG sẽ trả logged_in_user + auth
            if (!empty($json['status']) && $json['status'] === 'ok' && !empty($json['logged_in_user'])) {
                $this->authData['user_id'] = $json['logged_in_user']['pk'];
                $this->authData['authorization'] = $response->header('ig-set-authorization') ?? '';
                return [
                    'status' => true,
                    'data'   => [
                        'user'  => $json['logged_in_user'],
                        'auth'  => $this->authData,
                    ]
                ];
            }

            return [
                'status' => false,
                'error'  => $json['message'] ?? 'Failed to submit security code',
                'data'   => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'error'  => $e->getMessage(),
            ];
        }
    }

    // ===== 2. Profile Info =====
    public function getProfile(): array
    {
        try {
            $response = $this->http()
                ->get(InstagramEndpoints::CURRENT_USER);

            if (!$response->successful()) {
                return [
                    'status' => false,
                    'error' => __('Failed to fetch Instagram profile info. Please try again.') .
                        ($response->body() ? ' ' . $response->body() : '')
                ];
            }

            $userBio = $response->json();
            if (empty($userBio['user'])) {
                return [
                    'status' => false,
                    'error' => __('Instagram user info not found. Please check if the session is valid.')
                ];
            }
            return [
                'status' => true,
                'data' => [
                    'id' => $this->authData['user_id'],
                    'name' => $userBio['user']['full_name'] ?? $this->authData['username'],
                    'profile_picture_url' => $userBio['user']['profile_pic_url'] ?? '',
                    'username' => $this->authData['username'],
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'error' => __('An error occurred while fetching Instagram profile: :msg', ['msg' => $e->getMessage()])
            ];
        }
    }

    // ===== 3. Post Photo =====
    public function postPhoto(string $filePathOrUrl, string $caption = '', $options = []): array
    {
        [$filePath, $filePathRelative, $isTemp] = $this->getLocalMediaPath($filePathOrUrl, 'jpg');
        try {
            if (!file_exists($filePath) || !is_readable($filePath)) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => false, 'error' => __('Photo file does not exist or is not readable.')];
            }
            $uploadId = $this->generateUploadId();
            $imageSize = @getimagesize($filePath);
            if (empty($imageSize[0]) || empty($imageSize[1])) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => false, 'error' => __('Failed to read image dimensions. Please use a valid photo.')];
            }
            $photo = [
                'path' => $filePath,
                'width' => $imageSize[0],
                'height' => $imageSize[1],
            ];
            $uploadResult = $this->uploadMediaPhoto($uploadId, $photo);
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            if ($uploadResult !== true) {
                return ['status' => false, 'error' => $uploadResult];
            }
            $data = [
                '_uuid' => $this->authData['device_id'],
                'device_id' => $this->authData['android_device_id'],
                'upload_id' => $uploadId,
                'caption' => $caption,
                'source_type' => '4',
                'edits' => [
                    'crop_original_size' => [(float)$photo['width'], (float)$photo['height']],
                    'crop_zoom' => 1.0,
                    'crop_center' => [0.0, -0.0]
                ],
                'extra' => [
                    'source_width' => (float)$photo['width'],
                    'source_height' => (float)$photo['height'],
                ],
                'device' => $this->device,
            ];
            $resp = $this->http()
                ->timeout(60)
                ->post(InstagramEndpoints::MEDIA_CONFIGURE, [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]);
            $result = json_decode($resp->body(), true);
            if (empty($result['media']['id'])) {
                return [
                    'status' => false,
                    'error' => __('Photo uploaded, but Instagram did not return a media ID.')
                ];
            }
            $mediaId = $result['media']['id'] ?? null;
            if (!empty($options['pin']) && $mediaId) $this->pinPost($mediaId);
            if (!empty($options['first_comment']) && $mediaId) $this->commentOnMedia($options['first_comment'], $mediaId);

            return [
                'status' => true,
                'data' => [
                    'media_id' =>  $mediaId ?? null,
                    'media_code' => $result['media']['code'] ?? null,
                    'media_pk' => $result['media']['pk'] ?? null,
                    'url' => !empty($result['media']['code']) ? 'https://instagram.com/p/' . $result['media']['code'] : null,
                ]
            ];
        } catch (\Throwable $e) {
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            return [
                'status' => false,
                'error' => __('Failed to upload photo to Instagram: :msg', ['msg' => $e->getMessage()])
            ];
        }
    }

    public function postStoryPhoto(string $photoPathOrUrl, string $caption = '', array $options = []): array
    {
        [$filePath, $filePathRelative, $isTemp] = $this->getLocalMediaPath($photoPathOrUrl, 'jpg');
        try {
            // Check if file exists and is valid
            if (!file_exists($filePath) || !is_readable($filePath)) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => false, 'error' => __('Photo file does not exist or is not readable. Please upload a valid image file.')];
            }

            // Get image size information
            $imageSize = @getimagesize($filePath);
            if (empty($imageSize[0]) || empty($imageSize[1])) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => false, 'error' => __('Failed to read image dimensions. Please use a valid photo.')];
            }
            $width  = (float) $imageSize[0];
            $height = (float) $imageSize[1];

            // Upload photo to Instagram server
            $uploadId = $this->generateUploadId();
            $photo = [
                'path'   => $filePath,
                'width'  => $width,
                'height' => $height,
            ];
            $uploadResult = $this->uploadMediaPhoto($uploadId, $photo);
            if ($uploadResult !== true) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => false, 'error' => $uploadResult];
            }

            // Build request payload
            $data = [
                '_uuid'        => $this->authData['device_id'],
                'device_id'    => $this->authData['android_device_id'],
                'upload_id'    => $uploadId,
                'source_type'  => '4',
                'caption'      => $caption,
                'edits'        => [
                    'crop_original_size' => [$width, $height],
                    'crop_zoom'          => 1.0,
                    'crop_center'        => [0.0, 0.0],
                    'filter_type'        => 0,
                    'filter_strength'    => 1.0,
                ],
                'extra'        => [
                    'source_width'  => $width,
                    'source_height' => $height,
                ],
                'client_timestamp'              => time(),
                'story_media_creation_date'     => time(),
                'supported_capabilities_new'    => [
                    [
                        "name" => "SUPPORTED_SDK_VERSIONS",
                        "value" => "108.0,109.0,110.0,111.0,112.0,113.0,114.0,115.0,116.0,117.0,118.0,119.0,120.0,121.0,122.0,123.0,124.0,125.0,126.0,127.0"
                    ],
                    [
                        "name" => "FACE_TRACKER_VERSION",
                        "value" => "14"
                    ],
                    [
                        "name" => "segmentation",
                        "value" => "segmentation_enabled"
                    ],
                    [
                        "name" => "COMPRESSION",
                        "value" => "ETC2_COMPRESSION"
                    ],
                    [
                        "name" => "world_tracker",
                        "value" => "world_tracker_enabled"
                    ],
                    [
                        "name" => "gyroscope",
                        "value" => "gyroscope_enabled"
                    ]
                ],
                'has_original_sound'         => "1",
                'camera_session_id'          => $options['camera_session_id'] ?? $this->generateUuid(),
                'timezone_offset'            => (string)(date('Z')),
                'media_folder'               => "Camera",
                'configure_mode'             => "1",
                'creation_surface'           => "camera",
                'capture_type'               => "normal",
                'rich_text_format_types'     => ["default"],
                '_uid'                       => $this->authData['user_id'] ?? 49154269846,
                'composition_id'             => $options['composition_id'] ?? $this->generateUuid(),
                'original_media_type'        => "photo",
                'camera_entry_point'         => "121",
            ];

            // Merge tap_models if present
            if (!empty($options['tap_models'])) {
                $data['tap_models'] = $options['tap_models'];
            }

            // Send configure_to_story request USING $this->http()!
            $resp = $this->http()->post(InstagramEndpoints::MEDIA_CONFIGURE_TO_STORY, [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]);

            $result = json_decode($resp->body(), true);

            if (empty($result['media']['id'])) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return [
                    'status' => false,
                    'error' => $result['message'] ?? __('Photo uploaded, but Instagram did not return a media ID. Please check your content or try again later.')
                ];
            }
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            return [
                'status' => true,
                'data' => [
                    'media_id'   => $result['media']['id'] ?? null,
                    'media_code' => $result['media']['code'] ?? null,
                    'media_pk'   => $result['media']['pk'] ?? null,
                    'url'        => !empty($result['media']['code']) ? 'https://instagram.com/stories/' . $result['media']['code'] : null,
                ]
            ];
        } catch (\Throwable $e) {
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            return [
                'status' => false,
                'error' => __('Failed to upload story photo to Instagram: :msg', ['msg' => $e->getMessage()])
            ];
        }
    }

    public function postReel($videoPath, $caption = '', $options = [])
    {
        return $this->postVideo($videoPath, $caption, $options);
    }

    public function postCarousel(array $mediaPaths, string $caption = '', $options = []): array
    {
        $children_metadata = [];
        foreach ($mediaPaths as $mediaPath) {
            $upload = $this->uploadCarouselItem($mediaPath);
            if ($upload['status'] !== 'ok') {
                return $upload;
            }
            $children_metadata[] = [
                'upload_id' => $upload['upload_id']
            ];
        }
        if (count($children_metadata) === 0) {
            return ['status' => 'error', 'message' => __('No valid media in carousel')];
        }
        $body = [
            'caption' => $caption,
            'children_metadata' => $children_metadata,
            'client_sidecar_id' => $this->generateUploadId(),
            'disable_comments' => '0',
            'like_and_view_counts_disabled' => false,
            'source_type' => 'library'
        ];
        try {
            // ĐÃ SỬA: Dùng $this->http()
            $resp = $this->http()->post(InstagramEndpoints::MEDIA_CONFIGURE_SIDE_CAR, [
                    'signed_body' => 'SIGNATURE.' . json_encode($body)
                ]);
            $result = json_decode($resp->body(), true);
            if (isset($result['status']) && $result['status'] === 'fail') {
                return [
                    'status' => 'error',
                    'message' => !empty($result['message']) ? $result['message'] : __('Error')
                ];
            }
            $mediaId = $result['media']['id'] ?? null;
            if (!empty($options['pin']) && $mediaId) $this->pinPost($mediaId);
            if (!empty($options['first_comment']) && $mediaId) $this->commentOnMedia($options['first_comment'], $mediaId);

            return [
                'status' => true,
                'data' => [
                    'id' => $mediaId,
                    'code' => $result['media']['code'] ?? null,
                    'url' => isset($result['media']['code']) ? 'https://www.instagram.com/p/' . $result['media']['code'] : null,
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    protected function uploadCarouselItem($filePathOrUrl)
    {
        [$filePath, $filePathRelative, $isTemp] = $this->getLocalMediaPath($filePathOrUrl);
        if (!file_exists($filePath) || !is_readable($filePath)) {
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            return ['status' => 'error', 'message' => __('Media file does not exist or is not readable.')];
        }
        $uploadId = $this->generateUploadId();
        $result = null;
        if (\Media::isImg($filePath)) {
            $imageSize = @getimagesize($filePath);
            if (empty($imageSize[0]) || empty($imageSize[1])) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => 'error', 'message' => __('Failed to read image dimensions. Please use a valid photo.')];
            }
            $photo = [
                'path'   => $filePath,
                'width'  => $imageSize[0],
                'height' => $imageSize[1],
            ];
            $result = $this->uploadMediaPhoto($uploadId, $photo);
        } else if (\Media::isVideo($filePath)) {
            $video = [
                'path'     => $filePath,
                'width'    => 720,
                'height'   => 1280,
                'duration' => 15,
                'thumbnail' => [
                    'path' => $filePath,
                    'width' => 720,
                    'height' => 1280,
                ],
                'audio_codec' => 'aac'
            ];
            $result = $this->uploadMediaVideo($uploadId, $video);
        } else {
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            return ['status' => 'error', 'message' => __('Unsupported media type.')];
        }
        if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
        if ($result !== true) {
            return ['status' => 'error', 'message' => $result];
        }
        return [
            'status' => 'ok',
            'upload_id' => $uploadId
        ];
    }

    /**
     * Post a video to Instagram, supports local path or URL.
     * Automatically creates a JPEG thumbnail from the video (using createVideoThumbnail).
     */
    public function postVideo(string $videoPathOrUrl, string $caption = '', $options = []): array
    {
        [$filePath, $filePathRelative, $isTemp] = $this->getLocalMediaPath($videoPathOrUrl, 'mp4');
        $thumbPath = null;
        $thumbPathRelative = null;
        try {
            if (!file_exists($filePath) || !is_readable($filePath)) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => false, 'error' => __('Video file does not exist or is not readable. Please upload a valid video file.')];
            }
            $uploadId = $this->generateUploadId();
            $video = [
                'path' => $filePath,
                'width' => 720,
                'height' => 1280,
                'duration' => 15,
                'audio_codec' => 'aac'
            ];

            // Auto generate thumbnail
            $thumbPathRelative = preg_replace('/\.(mp4|mov|avi|mkv)$/i', '.jpg', $filePathRelative);
            $thumbPath = \Media::path($thumbPathRelative);
            $ok = $this->createVideoThumbnail($filePath, $thumbPath, 1);
            if (!$ok) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                return ['status' => false, 'error' => __('Failed to generate thumbnail for the video.')];
            }
            $imageSize = @getimagesize($thumbPath);
            $thumbnail = [
                'path' => $thumbPath,
                'width' => $imageSize[0] ?? 720,
                'height' => $imageSize[1] ?? 1280,
            ];

            // upload video
            $uploadResult = $this->uploadMediaVideo($uploadId, $video);
            if ($uploadResult !== true) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                if ($thumbPath && file_exists($thumbPath)) @unlink($thumbPath);
                return ['status' => false, 'error' => $uploadResult];
            }
            // upload thumbnail
            $uploadThumbResult = $this->uploadMediaPhoto($uploadId, $thumbnail);
            if ($uploadThumbResult !== true) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                if ($thumbPath && file_exists($thumbPath)) @unlink($thumbPath);
                return ['status' => false, 'error' => $uploadThumbResult];
            }

            $data = [
                '_uuid' => $this->authData['device_id'],
                'device_id' => $this->authData['android_device_id'],
                'upload_id' => $uploadId,
                'caption' => $caption,
                'source_type' => '4',
                'length' => $video['duration'],
                'filter_type' => 0,
                'device' => $this->device,
                'extra' => [
                    'source_width' => $video['width'],
                    'source_height' => $video['height'],
                ],
                'audio_muted' => false,
                'poster_frame_index' => 0,
                'video_result' => ''
            ];

            $resp = $this->http()->post(InstagramEndpoints::MEDIA_CONFIGURE_VIDEO, [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]);
            $result = json_decode($resp->body(), true);
            if (empty($result['media']['id'])) {
                if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
                if ($thumbPath && file_exists($thumbPath)) @unlink($thumbPath);
                return [
                    'status' => false,
                    'error' => __('Video uploaded, but Instagram did not return a media ID. Please check your content or try again later.')
                ];
            }
            $mediaId = $result['media']['id'] ?? null;
            if (!empty($options['pin']) && $mediaId) $this->pinPost($mediaId);
            if (!empty($options['first_comment']) && $mediaId) $this->commentOnMedia($options['first_comment'], $mediaId);

            // Clean up temp video file and temp thumbnail
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            if ($thumbPath && file_exists($thumbPath)) @unlink($thumbPath);

            return [
                'status' => true,
                'data' => [
                    'media_id' => $mediaId,
                    'media_code' => $result['media']['code'] ?? null,
                    'media_pk' => $result['media']['pk'] ?? null,
                    'url' => !empty($result['media']['code']) ? 'https://instagram.com/p/' . $result['media']['code'] : null,
                ]
            ];
        } catch (\Throwable $e) {
            if ($isTemp && $filePathRelative) $this->deleteTempFile($filePathRelative);
            if ($thumbPath && file_exists($thumbPath)) @unlink($thumbPath);
            return [
                'status' => false,
                'error' => __('Failed to upload video to Instagram: :msg', ['msg' => $e->getMessage()])
            ];
        }
    }

    /**
     * Post a comment to a media/post.
     */
    public function commentOnMedia(string $comment, string $mediaId): array
    {
        if (empty($comment) || empty($mediaId)) {
            return ['status' => false, 'error' => __('Comment text and media ID are required.')];
        }
        $data = [
            "_uuid" => $this->authData['device_id'],
            "device_id" => $this->authData['android_device_id'],
            "comment_text" => $comment,
            'idempotence_token' => $this->generateUuid()
        ];
        $endpoint = sprintf(InstagramEndpoints::COMMENT, $mediaId);

        try {
            $response = $this->http()->post($endpoint, [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ]);
            $result = $response->json();
            if (!isset($result['comment']['pk'])) {
                return [
                    'status' => false,
                    'error' => $result['message'] ?? __('Failed to comment on Instagram post. Please try again.')
                ];
            }
            return [
                'status' => true,
                'data' => [
                    'comment_id' => $result['comment']['pk']
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'error' => __('An error occurred while commenting on Instagram: :msg', ['msg' => $e->getMessage()])
            ];
        }
    }

    protected function uploadMediaPhoto($uploadId, $photo)
    {
        if (!file_exists($photo['path']) || !is_readable($photo['path'])) {
            return __('Photo file does not exist or is not readable. Please check the file path.');
        }

        $params = [
            'media_type' => '1',
            'upload_media_height' => (string)$photo['height'],
            'upload_media_width' => (string)$photo['width'],
            'upload_id' => $uploadId,
            'image_compression' => '{"lib_name":"moz","lib_version":"3.1.m","quality":"87"}',
            'xsharing_user_ids' => '[]',
            'retry_context' => json_encode([
                'num_step_auto_retry' => 0,
                'num_reupload' => 0,
                'num_step_manual_retry' => 0
            ]),
        ];
        $entity_name = sprintf('%s_%d_%d', $uploadId, 0, crc32(basename($photo['path'])));
        $endpoint = InstagramEndpoints::UPLOAD_PHOTO . $entity_name;

        $headers = array_merge($this->getDefaultHeaders(), [
            'X_FB_PHOTO_WATERFALL_ID' => $this->generateUuid(),
            'X-Instagram-Rupload-Params' => json_encode($params),
            'X-Entity-Type' => 'image/jpeg',
            'X-Entity-Name' => $entity_name,
            'X-Entity-Length' => filesize($photo['path']),
            'Offset' => '0',
            'Content-Type' => 'application/octet-stream'
        ]);

        try {
            $fileStream = fopen($photo['path'], 'r');
            $response = $this->http(false)
                ->withHeaders($headers)
                ->withBody($fileStream, 'application/octet-stream')
                ->post($endpoint);

            fclose($fileStream);

            if (!$response->successful()) {
                return __('Failed to upload photo to Instagram server. Please try again.') .
                    ($response->body() ? ' ' . $response->body() : '');
            }
            return true;
        } catch (\Throwable $e) {
            return __('Upload failed!') . ' ' . $e->getMessage();
        }
    }

    public function pinPost($postID)
    {
        $mediaId = explode('_', $postID)[0];
        $data = [
            'post_id'    => $mediaId,
            '_uuid'      => $this->authData['device_id'],
            'device_id'  => $this->authData['android_device_id'],
            'radio_type' => 'wifi_none'
        ];

        try {
            $response = $this->http()
                ->post(InstagramEndpoints::PIN_POST, [
                    'signed_body' => 'SIGNATURE.' . json_encode($data),
                ]);

            $result = $response->json();

            if (!empty($result['status']) && $result['status'] === 'ok') {
                return [
                    'status' => true,
                    'message' => __('Pinned successfully!')
                ];
            } else {
                return [
                    'status' => false,
                    'message' => $result['message'] ?? __('Failed to pin post.')
                ];
            }
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('Exception: ') . $e->getMessage()
            ];
        }
    }

    public function createLive($message = "Hey!", $previewWidth = 1080, $previewHeight = 2076)
    {
        $data = [
            "_uuid"             => $this->authData['device_id'] ?? $this->generateUuid(),
            "preview_height"    => $previewHeight,
            "preview_width"     => $previewWidth,
            "broadcast_message" => $message,
            "broadcast_type"    => 'RTMP_SWAP_ENABLED',
        ];

        try {
            $response = $this->http()
                ->post(InstagramEndpoints::LIVE_CREATE, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            $result = [];
        }

        if (!empty($result['broadcast_id'])) {
            return [
                'status'  => true,
                'message' => __('Live stream created!'),
                'data'    => [
                    'broadcast_id' => $result['broadcast_id'],
                    'stream_url'   => $result['broadcast']['rtmp_stream_url'] ?? null,
                    'stream_key'   => $result['broadcast']['rtmp_stream_key'] ?? null,
                    // any other info...
                ]
            ];
        } else {
            return [
                'status'  => false,
                'message' => $result['message'] ?? __('Failed to create live stream.'),
                'data'    => []
            ];
        }
    }

    public function startLive($broadcastId, $latitude = null, $longitude = null)
    {
        $data = [
            "_uuid" => $this->authData['device_id'] ?? $this->generateUuid(),
        ];
        if ($latitude !== null && $longitude !== null) {
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
        }

        $endpoint = sprintf(InstagramEndpoints::LIVE_START, $broadcastId);

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            $result = [];
        }

        if (!empty($result['status']) && $result['status'] === 'ok') {
            return [
                'status'  => true,
                'message' => __('Live stream started!'),
                'data'    => [
                    'broadcast_id'    => $result['broadcast_id'] ?? $broadcastId,
                    'start_time'      => $result['start_time'] ?? null,
                    'viewer_count'    => $result['viewer_count'] ?? null,
                ]
            ];
        } else {
            return [
                'status'  => false,
                'message' => $result['message'] ?? __('Failed to start live stream.'),
                'data'    => []
            ];
        }
    }

    public function endLive($broadcastId)
    {
        $endpoint = sprintf(InstagramEndpoints::LIVE_END, $broadcastId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => $result['status'] === 'ok' ? __('Live ended successfully!') : ($result['message'] ?? __('Failed to end live.')),
            'data'    => $result,
        ];
    }

    public function liveComment($broadcastId, $text)
    {
        $endpoint = sprintf(InstagramEndpoints::LIVE_COMMENT, $broadcastId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
            'comment_text' => $text,
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        // Usually, the returned data will contain 'comment' with 'pk' as comment id.
        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => $result['status'] === 'ok' ? __('Comment sent!') : ($result['message'] ?? __('Failed to send live comment.')),
            'data'    => [
                'comment' => $result['comment'] ?? null,
            ]
        ];
    }

    public function pinLiveComment($broadcastId, $commentId)
    {
        $endpoint = sprintf(InstagramEndpoints::LIVE_PIN_COMMENT, $broadcastId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
            'comment_id' => $commentId,
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => $result['status'] === 'ok' ? __('Comment pinned!') : ($result['message'] ?? __('Failed to pin comment.')),
            'data'    => [
                'comment_id' => $commentId,
            ]
        ];
    }

    public function likeMedia($mediaId)
    {
        $endpoint = sprintf(InstagramEndpoints::MEDIA_LIKE, $mediaId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => $result['status'] === 'ok' ? __('Media liked!') : ($result['message'] ?? __('Failed to like media.')),
            'data'    => [
                'media_id' => $mediaId,
            ]
        ];
    }

    public function followUser($userId)
    {
        $endpoint = sprintf(InstagramEndpoints::USER_FOLLOW, $userId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['result']) && $result['result'] === 'following',
            'message' => (!empty($result['result']) && $result['result'] === 'following')
                            ? __('Now following user!')
                            : ($result['message'] ?? __('Failed to follow user.')),
            'data'    => [
                'user_id' => $userId,
            ]
        ];
    }

    public function unfollowUser($userId)
    {
        $endpoint = sprintf(InstagramEndpoints::USER_UNFOLLOW, $userId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['result']) && $result['result'] === 'unfollowed',
            'message' => (!empty($result['result']) && $result['result'] === 'unfollowed')
                            ? __('User unfollowed!')
                            : ($result['message'] ?? __('Failed to unfollow user.')),
            'data'    => [
                'user_id' => $userId,
            ]
        ];
    }

    public function sendDirectMessage($recipientUserId, $text)
    {
        $data = [
            '_uuid'         => $this->authData['device_id'],
            '_uid'          => $this->authData['user_id'],
            '_csrftoken'    => $this->authData['csrftoken'] ?? $this->generateToken(32),
            'recipient_users' => json_encode([[$recipientUserId]]),
            'action'        => 'send_item',
            'text'          => $text,
        ];

        try {
            $response = $this->http()
                ->post(InstagramEndpoints::DIRECT_SEND, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => (!empty($result['status']) && $result['status'] === 'ok')
                            ? __('Message sent!')
                            : ($result['message'] ?? __('Failed to send message.')),
            'data'    => [
                'recipient_id' => $recipientUserId,
            ]
        ];
    }

    public function getUserMedias($userId, $maxId = null, $count = 12)
    {
        $endpoint = sprintf(InstagramEndpoints::USER_FEED, $userId);

        $query = [
            'count'   => $count,
            // Add 'max_id' for pagination if provided
        ];
        if ($maxId) {
            $query['max_id'] = $maxId;
        }

        try {
            $response = $this->http()
                ->get($endpoint, $query);

            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        if (!empty($result['items'])) {
            return [
                'status'  => true,
                'message' => __('Fetched user medias successfully.'),
                'data'    => [
                    'medias'         => $result['items'],
                    'more_available' => $result['more_available'] ?? false,
                    'next_max_id'    => $result['next_max_id'] ?? null
                ]
            ];
        } else {
            return [
                'status'  => false,
                'message' => $result['message'] ?? __('No media found or failed to fetch.'),
                'data'    => []
            ];
        }
    }


    public function getUserIdByUsername($username)
    {
        $endpoint = sprintf(InstagramEndpoints::USER_LOOKUP, $username);

        try {
            $response = $this->http()->get($endpoint);
            $result = $response->json();
            $id = $result['data']['user']['id'] ?? null;

            if ($id) {
                return [
                    'status' => true,
                    'user_id' => $id,
                ];
            }
            return [
                'status' => false,
                'message' => __('User not found!'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function likeComment($mediaId, $commentId)
    {
        $endpoint = sprintf(InstagramEndpoints::COMMENT_LIKE, $mediaId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
            'comment_id' => $commentId,
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => $result['status'] === 'ok' ? __('Comment liked!') : ($result['message'] ?? __('Failed to like comment.')),
            'data'    => [
                'media_id'   => $mediaId,
                'comment_id' => $commentId,
            ]
        ];
    }

    public function unlikeComment($mediaId, $commentId)
    {
        $endpoint = sprintf(InstagramEndpoints::COMMENT_UNLIKE, $mediaId);

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
            'comment_id' => $commentId,
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => $result['status'] === 'ok' ? __('Comment unliked!') : ($result['message'] ?? __('Failed to unlike comment.')),
            'data'    => [
                'media_id'   => $mediaId,
                'comment_id' => $commentId,
            ]
        ];
    }

    public function setBiography($bio)
    {
        $endpoint = InstagramEndpoints::SET_BIOGRAPHY;

        $data = [
            '_uuid'      => $this->authData['device_id'],
            '_uid'       => $this->authData['user_id'],
            '_csrftoken' => $this->authData['csrftoken'] ?? $this->generateToken(32),
            'raw_text'   => $bio,
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $this->signData($data));
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }

        return [
            'status'  => !empty($result['status']) && $result['status'] === 'ok',
            'message' => $result['status'] === 'ok' ? __('Biography updated!') : ($result['message'] ?? __('Failed to update biography.')),
            'data'    => $result,
        ];
    }

    public function searchUsers($keyword, $count = 10)
    {
        $endpoint = InstagramEndpoints::SEARCH_TOP;

        $params = [
            'query' => $keyword,
            'count' => $count,
            'timezone_offset' => '-14400',
        ];

        try {
            $response = $this->http()->get($endpoint, $params);
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ];
        }

        return [
            'status'  => true,
            'message' => __('Search successful.'),
            'data'    => [
                'users' => array_map(function ($item) {
                    return [
                        'pk'        => $item['user']['pk'] ?? null,
                        'username'  => $item['user']['username'] ?? null,
                        'full_name' => $item['user']['full_name'] ?? null,
                        'profile_pic_url' => $item['user']['profile_pic_url'] ?? null,
                        'is_verified' => $item['user']['is_verified'] ?? false,
                    ];
                }, $result['users'] ?? []),
            ],
        ];
    }

    public function searchHashtags($keyword, $count = 10)
    {
        $endpoint = InstagramEndpoints::SEARCH_TOP;

        $params = [
            'query' => $keyword,
            'count' => $count,
            'context' => 'blended',
        ];

        try {
            $response = $this->http()->get($endpoint, $params);
            $result = $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ];
        }

        // Extract and format hashtags only
        $hashtags = array_map(function ($item) {
            return [
                'name'        => $item['hashtag']['name'] ?? null,
                'id'          => $item['hashtag']['id'] ?? null,
                'media_count' => $item['hashtag']['media_count'] ?? null,
            ];
        }, $result['hashtags'] ?? []);

        return [
            'status'  => true,
            'message' => __('Search successful.'),
            'data'    => [
                'hashtags' => $hashtags,
            ],
        ];
    }

    protected function uploadMediaVideo($uploadId, $video)
    {
        if (!file_exists($video['path']) || !is_readable($video['path'])) {
            return __('Video file does not exist or is not readable. Please check the file path.');
        }

        $params = [
            'upload_id' => $uploadId,
            'retry_context' => json_encode([
                'num_step_auto_retry' => 0,
                'num_reupload' => 0,
                'num_step_manual_retry' => 0
            ]),
            'xsharing_user_ids' => '[]',
            'upload_media_height' => (string)$video['height'],
            'upload_media_width' => (string)$video['width'],
            'upload_media_duration_ms' => (string)($video['duration'] * 1000),
            'media_type' => '2'
        ];
        $entity_name = sprintf('%s_%d_%d', $uploadId, 0, crc32(basename($video['path'])));
        $endpoint = InstagramEndpoints::UPLOAD_VIDEO . $entity_name;

        $headers = array_merge($this->getDefaultHeaders(), [
            'X_FB_VIDEO_WATERFALL_ID' => $this->generateUuid(),
            'X-Instagram-Rupload-Params' => json_encode($params),
            'X-Entity-Type' => 'video/mp4',
            'X-Entity-Name' => $entity_name,
            'X-Entity-Length' => filesize($video['path']),
            'Offset' => '0',
            'Content-Type' => 'application/octet-stream'
        ]);

        try {
            $fileStream = fopen($video['path'], 'r');

            $response = $this->http(false)
                ->withHeaders($headers)
                ->withBody($fileStream, 'application/octet-stream')
                ->post($endpoint);

            fclose($fileStream);

            if (!$response->successful()) {
                return __('Failed to upload video to Instagram server. Please try again.')
                    . ($response->body() ? ' ' . $response->body() : '');
            }
            return true;
        } catch (\Throwable $e) {
            return __('Upload failed!') . ' ' . $e->getMessage();
        }
    }

    protected function getLocalMediaPath($filePathOrUrl, $defaultExt = 'jpg'): array
    {
        if (filter_var($filePathOrUrl, FILTER_VALIDATE_URL)) {
            $filePathRelative = \UploadFile::storeSingleFileFromURL($filePathOrUrl, 'temp_instagram');
            $absolutePath = \Media::path($filePathRelative); // always get full absolute path
            return [ $absolutePath, $filePathRelative, true ];
        }
        return [ $filePathOrUrl, $filePathOrUrl, false ];
    }

    protected function deleteTempFile($filePathRelative)
    {
        if (!$filePathRelative) return;
        \UploadFile::deleteFileFromServer($filePathRelative);

        if (file_exists($filePathRelative)) {
            @unlink($filePathRelative);
        }
    }

    function createVideoThumbnail($videoPath, $outputJpgPath, $second = 1)
    {
        $ffmpegBin = getenv('FFMPEG_BINARIES') ?: (function_exists('env') ? env('FFMPEG_BINARIES', null) : null);
        $ffprobeBin = getenv('FFPROBE_BINARIES') ?: (function_exists('env') ? env('FFPROBE_BINARIES', null) : null);

        if (!$ffmpegBin || !$ffprobeBin) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $ffmpegBin = $ffmpegBin ?: 'C:/ffmpeg/bin/ffmpeg.exe';
                $ffprobeBin = $ffprobeBin ?: 'C:/ffmpeg/bin/ffprobe.exe';
            } else {
                $ffmpegBin = $ffmpegBin ?: 'ffmpeg';
                $ffprobeBin = $ffprobeBin ?: 'ffprobe';
            }
        }

        try {
            $ffmpeg = \FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => $ffmpegBin,
                'ffprobe.binaries' => $ffprobeBin,
            ]);
            $video = $ffmpeg->open($videoPath);
            $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($second));
            $frame->save($outputJpgPath);
            return file_exists($outputJpgPath);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function setAuthData(array $authData): self
    {
        $this->authData = $authData;

        if (empty($this->authData['phone_id'])) {
            $this->authData['phone_id'] = $this->generateUuid();
        }
        if (empty($this->authData['device_id'])) {
            $this->authData['device_id'] = $this->generateUuid();
        }
        if (empty($this->authData['android_device_id'])) {
            $this->authData['android_device_id'] = 'android-' . strtolower($this->generateToken(20));
        }
        return $this;
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'User-Agent'                   => $this->userAgent,
            'Accept-Encoding'              => 'gzip, deflate',
            'Accept'                       => '*/*',
            'Connection'                   => 'keep-alive',
            'X-IG-App-Locale'              => 'en_US',
            'X-IG-Device-Locale'           => 'en_US',
            'X-IG-Mapped-Locale'           => 'en_US',
            'X-Pigeon-Session-Id'          => 'UFS-' . $this->generateUuid() . '-1',
            'X-Pigeon-Rawclienttime'       => sprintf('%.3f', microtime(true)),
            'X-IG-Bandwidth-Speed-KBPS'    => sprintf('%.3f', mt_rand(2500000, 3000000) / 1000),
            'X-IG-Bandwidth-TotalBytes-B'  => (string)mt_rand(5000000, 90000000),
            'X-IG-Bandwidth-TotalTime-MS'  => (string)mt_rand(2000, 9000),
            'X-IG-App-Startup-Country'     => 'US',
            'X-Bloks-Version-Id'           => '5fd5e6e0f986d7e592743211c2dda24efc502cff541d7a7cfbb69da25b293bf1', // có thể cần cập nhật bản mới
            'X-Bloks-Is-Layout-RTL'        => 'false',
            'X-Bloks-Is-Panorama-Enabled'  => 'true',
            'X-IG-Device-ID'               => $this->authData['device_id'] ?? '',
            'X-IG-Family-Device-ID'        => $this->authData['phone_id'] ?? '',
            'X-IG-Android-ID'              => $this->authData['android_device_id'] ?? '',
            'X-IG-Timezone-Offset'         => (string) (int) date('Z'), // offset thực của server (giây)
            'X-IG-Connection-Type'         => 'WIFI',
            'X-IG-Capabilities'            => '3brTvx0=', // cái này cũng hay đổi theo app version
            'X-IG-App-ID'                  => '567067343352427',
            'Priority'                     => 'u=3',
            'Accept-Language'              => 'en-US',
            'X-MID'                        => $this->authData['mid'] ?? '',
            'X-FB-HTTP-Engine'             => 'Liger',
            'X-FB-Client-IP'               => 'True',
            'X-FB-Server-Cluster'          => 'True',
            'IG-INTENDED-USER-ID'          => $this->authData['user_id'] ?? '',
            'Authorization'                => $this->authData['authorization'] ?? '',
            'Content-Type'                 => 'application/x-www-form-urlencoded; charset=UTF-8',
        ];
    }

    protected function generateUploadId(): string
    {
        return number_format(round(microtime(true) * 1000), 0, '', '');
    }

    protected function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    protected function generateToken($len = 10): string
    {
        $letters = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
        $token = '';
        for ($i = 0; $i < $len; $i++) {
            $token .= $letters[mt_rand() % strlen($letters)];
        }
        return $token;
    }

    protected function generateFakeDevice(): array
    {
        $devices = [
            // model, manufacturer, os_version, os_release
            ['Samsung', 'SM-G991B', 30, '11.0.0'],
            ['Xiaomi', 'MI 11', 30, '11.0.0'],
            ['Oppo', 'CPH2219', 29, '10.0.0'],
            ['Huawei', 'LYA-L29', 28, '9.0.0'],
            ['Google', 'Pixel 5', 30, '11.0.0'],
            ['OnePlus', 'IN2020', 29, '10.0.0'],
            ['Realme', 'RMX3085', 30, '11.0.0'],
            ['Vivo', 'V2027', 29, '10.0.0'],
            ['Samsung', 'SM-A525F', 30, '11.0.0'],
            ['Xiaomi', 'Redmi Note 10', 30, '11.0.0'],
        ];
        $pick = $devices[array_rand($devices)];
        return [
            'manufacturer' => $pick[0],
            'model'        => $pick[1],
            'os_version'   => $pick[2],
            'os_release'   => $pick[3],
        ];
    }

    /**
     * Generate a realistic Instagram Android User-Agent string.
     */
    protected function generateInstagramUserAgent(array $device = null): string
    {
        $appVersions = [
            '304.0.0.36.110',
            '300.1.0.32.115',
            '289.0.0.77.109',
            '252.0.0.17.111',
            '220.0.0.12.114',
            '200.1.0.30.121',
            '178.0.0.37.123',
        ];
        $androidVersions = [
            [33, '13'],
            [32, '12'],
            [31, '12'],
            [30, '11'],
            [29, '10'],
            [28, '9'],
            [27, '8.1.0'],
            [26, '8.0.0'],
            [25, '7.1.2'],
        ];
        $dpiList = ['420dpi', '480dpi', '400dpi', '320dpi', '560dpi', '440dpi'];
        $screenSizes = ['1080x2400', '1080x2280', '1080x2340', '1080x1920', '720x1600', '1440x2960'];

        $device = $device ?: $this->generateFakeDevice();
        $android = $androidVersions[array_rand($androidVersions)];
        $appVersion = $appVersions[array_rand($appVersions)];
        $dpi = $dpiList[array_rand($dpiList)];
        $screen = $screenSizes[array_rand($screenSizes)];

        return sprintf(
            'Instagram %s Android (%d/%s; %s; %s; %s; %s; qcom; en_US; %d)',
            $appVersion,
            $android[0],
            $android[1],
            $dpi,
            $screen,
            $device['manufacturer'],
            $device['model'],
            mt_rand(1000000, 999999999)
        );
    }

    protected function prefill(): void {}

    protected function parseInstagramError($response, $default = null): string
    {
        if (is_object($response) && method_exists($response, 'json')) {
            $body = $response->json();
        } elseif (is_array($response)) {
            $body = $response;
        } else {
            return $default ?? __('An unknown error occurred.');
        }

        if (!empty($body['message'])) {
            return $body['message'];
        }
        if (!empty($body['error_message'])) {
            return $body['error_message'];
        }
        if (!empty($body['error_title'])) {
            return $body['error_title'];
        }

        return $default ?? __('An unknown error occurred.');
    }

    private function sync()
    {
        $csrfToken = $this->generateToken(32);
        $mid       = $this->generateToken(28);
        $igDid     = strtoupper($this->generateUuid());

        $cookies = [
            'csrftoken' => $csrfToken,
            'ig_did'    => $igDid,
            'ig_nrcb'   => '1',
            'mid'       => $mid,
        ];

        $headers = [
            'User-Agent'      => $this->userAgent,
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept'          => '*/*',
            'Connection'      => 'Keep-Alive',
            'Accept-Language' => 'en-US',
            'X-IG-App-ID'     => '567067343352427',
        ];

        $payload = [
            'id'          => $this->authData['device_id'] ?? ($this->authData['device_id'] = $this->generateUuid()),
            'experiments' => 'ig_android_password_encryption'
        ];

        $data = [
            'signed_body' => 'SIGNATURE.' . json_encode($payload, JSON_UNESCAPED_SLASHES),
        ];

        $attempts = 0;
        do {
            $attempts++;
            $response = $this->http(false)
                        ->asForm()
                        ->withHeaders($headers)
                        ->withCookies($cookies, 'i.instagram.com')
                        ->post(InstagramEndpoints::QE_SYNC, $data);

            if ($response->successful()) {
                // store cookies (server có thể set lại)
                $this->authData['csrftoken'] = $csrfToken;
                if ($response->cookies()) {
                    $midCookie  = $response->cookies()->getCookieByName('mid');
                    $csrfCookie = $response->cookies()->getCookieByName('csrftoken');
                    if ($midCookie && $midCookie->getValue())  $this->authData['mid']      = $midCookie->getValue(); else $this->authData['mid'] = $mid;
                    if ($csrfCookie && $csrfCookie->getValue()) $this->authData['csrftoken'] = $csrfCookie->getValue();
                } else {
                    $this->authData['mid'] = $mid;
                }

                $keyId  = $response->header('ig-set-password-encryption-key-id');
                $pubKey = $response->header('ig-set-password-encryption-pub-key');
                if (is_array($keyId))  $keyId  = $keyId[0] ?? null;
                if (is_array($pubKey)) $pubKey = $pubKey[0] ?? null;
                $pubKey = $pubKey ? trim($pubKey, '"') : null;

                if ($keyId && $pubKey) {
                    return ['ok' => true, 'key_id' => $keyId, 'pub_key' => $pubKey];
                }
                \Log::warning('[IG][QE_SYNC] Missing encryption headers', [
                    'status'  => $response->status(),
                    'headers' => $response->headers(),
                    'body'    => substr($response->body() ?? '', 0, 200),
                ]);
                return ['ok' => false, 'error' => 'Missing encryption headers'];
            }

            $status = $response->status();
            \Log::warning('[IG][QE_SYNC] HTTP error', [
                'status'  => $status,
                'headers' => $response->headers(),
                'body'    => substr($response->body() ?? '', 0, 200),
            ]);

            if ($status === 429 || ($status >= 500 && $status < 600)) {
                usleep(200000 * $attempts); // 200ms, 400ms, 600ms
                continue;
            }
            return ['ok' => false, 'error' => 'QE_SYNC http '.$status];

        } while ($attempts < 3);

        return ['ok' => false, 'error' => 'QE_SYNC retry exhausted'];
    }

    protected function encPass($password, $publicKeyId, $publicKey): string
    {
        $key = substr(md5(uniqid(mt_rand(), true)), 0, 32);
        $iv = substr(md5(uniqid(mt_rand(), true)), 0, 12);
        $time = time();
        $rsa = PublicKeyLoader::loadPublicKey(base64_decode($publicKey));
        $rsa = $rsa->withPadding(RSA::ENCRYPTION_PKCS1);
        $encryptedRSA = $rsa->encrypt($key);
        $aes = new AES('gcm');
        $aes->setNonce($iv);
        $aes->setKey($key);
        $aes->setAAD(strval($time));
        $encrypted = $aes->encrypt($password);
        $payload = base64_encode(
            "\x01" |
            pack('n', intval($publicKeyId)) .
            $iv .
            pack('s', strlen($encryptedRSA)) .
            $encryptedRSA .
            $aes->getTag() .
            $encrypted
        );
        return sprintf('#PWD_INSTAGRAM:4:%s:%s', $time, $payload);
    }
}