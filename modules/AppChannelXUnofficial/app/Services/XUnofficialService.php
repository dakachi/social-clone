<?php
namespace Modules\AppChannelXUnofficial\Services;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Modules\AppChannelXUnofficial\Constants\XEndpoints;
use Exception;

class XUnofficialService
{
    protected $twCsrfToken;
    protected $twAuthToken;
    protected $twScreenName;
    protected $proxy;

    // Set credentials for the session
    public function setCredentials($twCsrfToken, $twAuthToken, $twScreenName, $proxy = null)
    {
        $this->twCsrfToken  = $twCsrfToken;
        $this->twAuthToken  = $twAuthToken;
        $this->twScreenName = $twScreenName ? $this->extractScreenNameFromInput($twScreenName) : null;
        $this->proxy        = $proxy;
    }

    public function getProfile()
    {
        try {
            $variables = [
                'screen_name' => $this->twScreenName,
                'withSafetyModeUserFields' => true,
            ];
            $endpoint = sprintf(
                XEndpoints::USER_BY_SCREEN_NAME,
                urlencode(json_encode($variables)),
                urlencode(json_encode(XEndpoints::FEATURES)),
                urlencode(XEndpoints::FIELD_TOGGLES)
            );
            $response = Http::withHeaders($this->buildHeaders())
                ->timeout(30)
                ->get($endpoint);
            $responseData = $response->json();

            $errorMsg = $this->getTwitterApiErrorMessage($responseData);
            if ($errorMsg) {
                return [
                    'status' => 0,
                    'data' => null,
                    'error' => $errorMsg,
                ];
            }

            $profileData = $responseData['data']['user']['result'] ?? null;
            if (!$profileData) {
                return [
                    'status' => 0,
                    'data' => null,
                    'error' => __('Unable to login to X. Please check your credentials or try again later.'),
                ];
            }

            $profile = $this->normalizeProfile($profileData);

            return [
                'status' => 1,
                'data' => $profile,
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'data' => null,
                'error' => $e->getMessage() ?: __('Unknown error'),
            ];
        }
    }

    public function getTimeline($userId)
    {
        try {
            $endpoint = str_replace('{value}', $userId, XEndpoints::USER_TIMELINE);

            $response = Http::withHeaders($this->buildHeaders())
                ->timeout(30)
                ->get($endpoint);

            $data = $response->json();

            $errorMsg = $this->getTwitterApiErrorMessage($data);
            if ($errorMsg) {
                return [
                    'status' => 0,
                    'data' => null,
                    'error' => 'Twitter API error: ' . $errorMsg,
                ];
            }

            $timeline = $data['data']['user']['result']['timeline_v2']['timeline']['instructions'] ?? null;
            if (!$timeline) {
                return [
                    'status' => 0,
                    'data' => null,
                    'error' => __('Timeline data is not available for this user.'),
                ];
            }

            return [
                'status' => 1,
                'data' => $timeline,
                'error' => null,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 0,
                'data' => null,
                'error' => 'An unexpected error occurred: ' . ($e->getMessage() ?: 'Unknown error'),
            ];
        }
    }

    public function createTweet($caption, $media_ids = [])
    {
        try {
            // Normalize media_entities array to the correct Twitter/X format
            $media_entities = [];
            foreach ($media_ids as $id) {
                $media_entities[] = ['media_id' => $id];
            }

            $variables = [
                'tweet_text' => $caption,
                'dark_request' => false,
                'media' => [
                    'media_entities' => $media_entities,
                    'possibly_sensitive' => false,
                ],
                'semantic_annotation_ids' => [],
            ];

            $sendData = $this->buildSendData($variables);
            $sendData = json_encode($sendData);

            $response = Http::withHeaders($this->buildHeaders([
                'X-Csrf-Token' => $this->twCsrfToken,
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($sendData),
            ]))
                ->timeout(30)
                ->withBody($sendData, 'application/json')
                ->post(XEndpoints::CREATE_TWEET);

            $result = $response->json();

            // Check for API errors
            $errorMsg = $this->getTwitterApiErrorMessage($result);
            if ($errorMsg) {
                return [
                    'status' => 0,
                    'data' => null,
                    'error' => $errorMsg,
                ];
            }

            // Get tweet result
            $tweetData = $result['data']['create_tweet']['tweet_results']['result'] ?? null;
            if (!$tweetData) {
                return [
                    'status' => 0,
                    'data' => null,
                    'error' => __('Could not create the tweet.'),
                ];
            }

            return [
                'status' => 1,
                'data' => $tweetData,
                'error' => null,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 0,
                'data' => null,
                'error' => $e->getMessage() ?: __('Unknown error'),
            ];
        }
    }

    // Upload media to Twitter (standardized response)
    public function uploadMediaToTwitter($media, $chunked = false)
    {
        try {
            $isUrl = filter_var($media, FILTER_VALIDATE_URL);
            if ($isUrl) {
                $content = @file_get_contents($media);
                if ($content === false) {
                    return [
                        'status' => 0,
                        'media_id' => null,
                        'error' => __('Cannot download remote media file.'),
                    ];
                }
                $headers = get_headers($media, 1);
                $media_type = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? end($headers['Content-Type']) : $headers['Content-Type']) : 'image/jpeg';
                $total_bytes = strlen($content);
                $basename = uniqid('remote_') . '.' . (pathinfo(parse_url($media, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
            } else {
                $content = fopen($media, 'r');
                $media_type = mime_content_type($media);
                $total_bytes = filesize($media);
                $basename = basename($media);
            }

            $tweet_type = \Media::isImg($media) ? "tweet_image" : "tweet_video";

            // INIT (still use Laravel Http)
            $init_query = [
                'command'        => 'INIT',
                'total_bytes'    => $total_bytes,
                'media_type'     => $media_type,
                'media_category' => $tweet_type,
            ];

            $init_response = Http::withHeaders([
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Host'          => 'upload.twitter.com',
                    'X-Csrf-Token'  => $this->twCsrfToken,
                    'origin'        => 'https://twitter.com',
                    'referer'       => 'https://twitter.com/',
                    'Cookie'       => "ct0={$this->twCsrfToken}; auth_token={$this->twAuthToken};",
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
                ])
                ->asForm()
                ->post(XEndpoints::UPLOAD_MEDIA, $init_query)
                ->json();

            if (empty($init_response) || !isset($init_response['media_id'])) {
                return [
                    'status' => 0,
                    'media_id' => null,
                    'error' => __('Cannot INIT upload to Twitter.'),
                ];
            }
            $media_id = $init_response['media_id'];

            // APPEND: MUST use Guzzle to attach media in body, other params in URL
            $client = new Client();
            $append_url = XEndpoints::UPLOAD_MEDIA . http_build_query([
                'command' => 'APPEND',
                'media_id' => $media_id,
                'segment_index' => 0,
            ]);
            $response = $client->request('POST', $append_url, [
                'headers' => [
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Host'          => 'upload.twitter.com',
                    'X-Csrf-Token'  => $this->twCsrfToken,
                    'origin'        => 'https://twitter.com',
                    'referer'       => 'https://twitter.com/',
                    'Cookie'       => "ct0={$this->twCsrfToken}; auth_token={$this->twAuthToken};",
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
                ],
                'multipart' => [
                    [
                        'name'     => 'media',
                        'contents' => $content,
                        'filename' => $basename,
                        // 'headers'  => ['Content-Type' => $media_type], // optional
                    ],
                ],
            ]);

            $append_result = json_decode((string)$response->getBody(), true);

            // FINALIZE (use Laravel Http)
            $finalize_resp = Http::withHeaders([
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Host'          => 'upload.twitter.com',
                    'X-Csrf-Token'  => $this->twCsrfToken,
                    'origin'        => 'https://twitter.com',
                    'referer'       => 'https://twitter.com/',
                    'Cookie'       => "ct0={$this->twCsrfToken}; auth_token={$this->twAuthToken};",
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
                ])
                ->asForm()
                ->post(XEndpoints::UPLOAD_MEDIA, [
                    'command'   => 'FINALIZE',
                    'media_id'  => $media_id
                ])
                ->json();

            if (!empty($finalize_resp) && isset($finalize_resp['media_id'])) {
                return [
                    'status' => 1,
                    'media_id' => $finalize_resp['media_id'],
                    'error' => null,
                ];
            }

            return [
                'status' => 0,
                'media_id' => null,
                'error' => __('Upload finalize failed.'),
            ];

        } catch (\Exception $e) {
            return [
                'status' => 0,
                'data' => null,
                'error' => $e->getMessage() ?: __('Unknown error'),
            ];
        }
    }



    // ==== SUPPORTING PRIVATE METHODS ====

    // Build standard headers for all requests
    protected function buildHeaders($additionalHeaders = [])
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Content-Type' => 'application/json',
            'Authorization' => XEndpoints::AUTH_BEARER,
            'Connection' => 'close',
            'X-Csrf-Token' => $this->twCsrfToken,
            'Cookie' => "ct0={$this->twCsrfToken}; auth_token={$this->twAuthToken};"
        ];
        return array_merge($headers, $additionalHeaders);
    }

    // Build the data payload for a Tweet (Twitter GraphQL format)
    protected function buildSendData($variables = [])
    {
        $sendData = [
            'fieldToggles' => json_encode(['withArticleRichContentState' => false]),
            'features' => json_encode([
                'tweetypie_unmention_optimization_enabled' => true,
                'responsive_web_edit_tweet_api_enabled' => true,
                'graphql_is_translatable_rweb_tweet_is_translatable_enabled' => true,
                'view_counts_everywhere_api_enabled' => true,
                'longform_notetweets_consumption_enabled' => true,
                'responsive_web_twitter_article_tweet_consumption_enabled' => false,
                'tweet_awards_web_tipping_enabled' => false,
                'longform_notetweets_rich_text_read_enabled' => true,
                'longform_notetweets_inline_media_enabled' => true,
                'responsive_web_graphql_exclude_directive_enabled' => true,
                'verified_phone_label_enabled' => false,
                'freedom_of_speech_not_reach_fetch_enabled' => true,
                'standardized_nudges_misinfo' => true,
                'tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled' => true,
                'responsive_web_media_download_video_enabled' => false,
                'responsive_web_graphql_skip_user_profile_image_extensions_enabled' => false,
                'responsive_web_graphql_timeline_navigation_enabled' => true,
                'responsive_web_enhance_cards_enabled' => false,
            ]),
            'queryId' => XEndpoints::QUERY_ID_CREATE_TWEET
        ];
        if (!empty($variables)) {
            $sendData['variables'] = json_encode($variables);
        }
        return $sendData;
    }

    // Parse Twitter API error (return only message)
    protected function getTwitterApiErrorMessage($response)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }
        if (isset($response['errors'][0]['message'])) {
            return $response['errors'][0]['message'];
        }
        return null;
    }

    // Normalize profile data
    protected function normalizeProfile($data)
    {
        if (isset($data['legacy'])) {
            return [
                'id'             => $data['rest_id'] ?? null,
                'screen_name'    => $data['legacy']['screen_name'] ?? null,
                'name'           => $data['legacy']['name'] ?? null,
                'avatar'         => $data['legacy']['profile_image_url_https'] ?? null,
                'bio'            => $data['legacy']['description'] ?? '',
                'followers'      => $data['legacy']['followers_count'] ?? 0,
                'following'      => $data['legacy']['friends_count'] ?? 0,
                'tweet_count'    => $data['legacy']['statuses_count'] ?? 0,
                'is_verified'    => $data['legacy']['verified'] ?? false,
                'created_at'     => $data['legacy']['created_at'] ?? null,
            ];
        }
        return [];
    }

    // Extract @username from a link, username or any input
    protected function extractScreenNameFromInput($input)
    {
        $input = trim($input);
        if (preg_match('/^[A-Za-z0-9_]{1,15}$/', $input)) {
            return $input;
        }
        $parts = parse_url($input);
        if (isset($parts['host']) && preg_match('/(x\.com|twitter\.com)$/', $parts['host'])) {
            $pathParts = array_values(array_filter(explode('/', $parts['path'])));
            if (isset($pathParts[0]) && preg_match('/^[A-Za-z0-9_]{1,15}$/', $pathParts[0])) {
                return $pathParts[0];
            }
        }
        if (preg_match('~(?:x\.com|twitter\.com)/([A-Za-z0-9_]{1,15})~', $input, $m)) {
            return $m[1];
        }
        return null;
    }
}
