<?php

namespace Modules\AppChannelLinkedinProfiles\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class LinkedinAPI  
{
    protected $app_id;
    protected $app_secret;
    protected $callback;
    protected $csrf;
    protected $scopes;
    protected $ssl;
    protected $type;
    protected $client; // Guzzle client

    /**
     * Constructor for LinkedinAPI.
     *
     * @param string $app_id      The application ID.
     * @param string $app_secret  The application secret.
     * @param string $callback    The callback URL.
     * @param string $scopes      The OAuth scopes.
     * @param bool   $ssl         Whether to verify SSL (default: true).
     */
    public function __construct(
        string $app_id, 
        string $app_secret, 
        string $callback, 
        string $scopes, 
        bool $ssl = true
    ) {
        $this->app_id     = $app_id;
        $this->app_secret = $app_secret;
        $this->callback   = $callback;
        $this->scopes     = $scopes;
        $this->ssl        = $ssl;
        $this->csrf       = random_int(1111111111, 99999999999);
        $this->type       = "urn:li:person:";

        // Initialize Guzzle client with SSL verification option.
        $this->client = new Client([
            'verify' => $this->ssl,
            'timeout' => 30
        ]);
    }

    /**
     * Generates the LinkedIn authorization URL.
     *
     * @return string The authorization URL.
     */
    public function getAuthUrl()
    {
        $_SESSION['linkedincsrf'] = $this->csrf;
        return "https://www.linkedin.com/oauth/v2/authorization?response_type=code"
            . "&client_id=" . $this->app_id
            . "&redirect_uri=" . $this->callback
            . "&state=" . $this->csrf
            . "&scope=" . $this->scopes;
    }

    /**
     * Retrieves the access token using the provided authorization code.
     *
     * @param string $code The authorization code.
     * @return array Returns an array with status and either the access token or an error message.
     */
    public function getAccessToken($code)
    {
        $url = "https://www.linkedin.com/oauth/v2/accessToken";
        $params = [
            'client_id'     => $this->app_id,
            'client_secret' => $this->app_secret,
            'redirect_uri'  => $this->callback,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ];

        $options = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $params
        ];

        $response = $this->sendRequest('POST', $url, $options);
        $data = json_decode($response);
        if (isset($data->access_token)) {
            return [ "status" => "success", "accessToken" => $data->access_token ];
        } else {
            return [ "status" => "error", "message" => $data->error_description ?? 'Unknown error' ];
        }
    }

    /**
     * Retrieves the entire person information.
     *
     * @param string $accessToken The LinkedIn access token.
     * @return array The person data as associative array.
     */
    public function getPerson($accessToken)
    {
        $url = "https://api.linkedin.com/v2/userinfo?oauth2_access_token=" . $accessToken;
        $response = $this->sendRequest('GET', $url);
        return json_decode($response, true);
    }

    /**
     * Retrieves the LinkedIn person ID.
     *
     * @param string $accessToken The LinkedIn access token.
     * @return mixed The person's ID.
     */
    public function getPersonID($accessToken)
    {
        $url = "https://api.linkedin.com/v2/userinfo?oauth2_access_token=" . $accessToken;
        $response = $this->sendRequest('GET', $url);

        $data = json_decode($response);
        return $data->sub ?? null;
    }

    /**
     * Retrieves company pages for which the user is an administrator.
     *
     * @param string $accessToken The LinkedIn access token.
     * @return array The company pages data as associative array.
     */
    public function getCompanyPages($accessToken)
    {
        $url = "https://api.linkedin.com/v2/organizationalEntityAcls?q=roleAssignee&role=ADMINISTRATOR"
             . "&projection=(elements*(organizationalTarget~(id,localizedName,vanityName,logoV2(original~:playableStreams,cropped~:playableStreams,cropInfo))))"
             . "&oauth2_access_token=" . trim($accessToken);
        $response = $this->sendRequest('GET', $url, [
            'headers' => ['Content-Type' => 'application/json']
        ]);
        return json_decode($response, true);
    }

    /**
     * Sets the type prefix for the author (e.g., "urn:li:person:").
     *
     * @param string $type The new type prefix.
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Posts a text-only update to LinkedIn.
     *
     * @param string $accessToken The LinkedIn access token.
     * @param string $person_id   The person's ID.
     * @param string $message     The post message.
     * @param string $visibility  Post visibility ("PUBLIC" or other).
     * @return mixed The response from LinkedIn.
     */
    public function linkedInTextPost($accessToken, $person_id, $message, $visibility = "PUBLIC")
    {
        $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
        $request = [
            "author"         => $this->type . $person_id,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary"   => [ "text" => $message ],
                    "shareMediaCategory" => "NONE",
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
            ]
        ];

        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($request)
        ];
        return $this->sendRequest('POST', $url, $options);
    }

    /**
     * Posts a link update to LinkedIn.
     *
     * @param string $accessToken The LinkedIn access token.
     * @param string $person_id   The person's ID.
     * @param string $message     The post message.
     * @param string $link_title  The title of the link.
     * @param string $link_desc   The description of the link.
     * @param string $link_url    The link URL.
     * @param string $visibility  Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInLinkPost($accessToken, $person_id, $message, $link_title, $link_desc, $link_url, $visibility = "PUBLIC")
    {
        $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
        $request = [
            "author"         => $this->type . $person_id,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary"   => [ "text" => $message ],
                    "shareMediaCategory" => "ARTICLE",
                    "media" => [[
                        "status"      => "READY",
                        "description" => [ "text" => substr($link_desc, 0, 200) ],
                        "originalUrl" => $link_url,
                        "title"       => [ "text" => $link_title ]
                    ]]
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($request)
        ];
        return $this->sendRequest('POST', $url, $options);
    }

    /**
     * Posts a video update to LinkedIn.
     *
     * Registers an upload session with the video recipe, uploads the video using the provided
     * video file path, and then creates the post with the video.
     *
     * @param string $accessToken       The LinkedIn access token.
     * @param string $person_id         The person's ID.
     * @param string $message           The post message.
     * @param string $video_path        The local path to the video file.
     * @param string $video_title       The title for the video.
     * @param string $video_description The description of the video.
     * @param string $visibility        Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInVideoPost(
        $accessToken,
        $person_id,
        $message,
        $video_path,
        $video_title,
        $video_description,
        $visibility = "PUBLIC"
    ) {
        // Register upload request using video recipe.
        $prepareUrl = "https://api.linkedin.com/v2/assets?action=registerUpload&oauth2_access_token=" . $accessToken;
        $prepareRequest = [
            "registerUploadRequest" => [
                "recipes" => [ "urn:li:digitalmediaRecipe:feedshare-video" ],
                "owner"   => $this->type . $person_id,
                "serviceRelationships" => [
                    [
                        "relationshipType" => "OWNER",
                        "identifier"       => "urn:li:userGeneratedContent"
                    ]
                ]
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($prepareRequest)
        ];
        $prepareResponse = $this->sendRequest('POST', $prepareUrl, $options);
        $prepareData = json_decode($prepareResponse);

        // If no error returned from registration, proceed with the upload.
        if (!isset($prepareData->message)) {
            $uploadURL = $prepareData->value->uploadMechanism
                ->{"com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"}
                ->uploadUrl;
            $asset_id = $prepareData->value->asset;

            // Upload the video via a PUT request.
            try {
                $fileStream = fopen($video_path, 'r');
                $this->client->request('PUT', $uploadURL, [
                    'headers' => [ 'Authorization' => 'Bearer ' . $accessToken ],
                    'body'    => $fileStream
                ]);
            } catch (RequestException $e) {
                return json_encode([
                    "error"   => "Video upload failed.",
                    "details" => $e->getMessage()
                ]);
            }

            $parse_id = explode(":", "urn:li:digitalmediaAsset:D5605AQG6_pTRbNXiOg");
            $id = end( $parse_id );

            $checkUpload = $this->sendRequest('GET', "https://api.linkedin.com/v2/assets/".$id."?oauth2_access_token=" . $accessToken);
            $checkUpload = json_decode($checkUpload);
            if($checkUpload->status == "ALLOWED"){

                // Assemble the post request including the video asset.
                $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
                $request = [
                    "author"         => $this->type . $person_id,
                    "lifecycleState" => "PUBLISHED",
                    "specificContent" => [
                        "com.linkedin.ugc.ShareContent" => [
                            "shareCommentary"   => [ "text" => $message ],
                            "shareMediaCategory"=> "VIDEO",
                            "media"             => [[
                                "status"      => "READY",
                                "media"       => $asset_id,
                            ]]
                        ]
                    ],
                    "visibility" => [
                        "com.linkedin.ugc.MemberNetworkVisibility" => $visibility
                    ]
                ];
                $options = [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => json_encode($request)
                ];
                return $this->sendRequest('POST', $url, $options);
            }

            return json_encode([
                "error"   => "Video upload failed.",
                "details" => "Video upload failed."
            ]);
        } else {
            return $prepareResponse;
        }
    }

    /**
     * Posts a single photo update to LinkedIn.
     *
     * @param string $accessToken       The LinkedIn access token.
     * @param string $person_id         The person's ID.
     * @param string $message           The post message.
     * @param string $image_path        The local path to the image.
     * @param string $image_title       The title for the image.
     * @param string $image_description The image description.
     * @param string $visibility        Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInPhotoPost($accessToken, $person_id, $message, $image_path, $image_title, $image_description, $visibility = "PUBLIC")
    {
        // Register upload request.
        $prepareUrl = "https://api.linkedin.com/v2/assets?action=registerUpload&oauth2_access_token=" . $accessToken;
        $prepareRequest = [
            "registerUploadRequest" => [
                "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
                "owner"   => $this->type . $person_id,
                "serviceRelationships" => [
                    [
                        "relationshipType" => "OWNER",
                        "identifier"       => "urn:li:userGeneratedContent"
                    ]
                ]
            ]
        ];

        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($prepareRequest)
        ];
        $prepareResponse = $this->sendRequest('POST', $prepareUrl, $options);
        $prepareData = json_decode($prepareResponse);

        // If no error, then upload.
        if (!isset($prepareData->message)) {
            $uploadURL = $prepareData->value->uploadMechanism
                ->{"com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"}
                ->uploadUrl;
            $asset_id = $prepareData->value->asset;

            // Use Guzzle client to upload the file via PUT.
            try {
                // Open the file stream.
                $fileStream = fopen($image_path, 'r');
                $this->client->request('PUT', $uploadURL, [
                    'headers' => [ 'Authorization' => 'Bearer ' . $accessToken ],
                    'body'    => $fileStream
                ]);
            } catch (RequestException $e) {
                return json_encode(["error" => "Image upload failed.", "details" => $e->getMessage()]);
            }

            // Prepare post request.
            $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
            $request = [
                "author"         => $this->type . $person_id,
                "lifecycleState" => "PUBLISHED",
                "specificContent" => [
                    "com.linkedin.ugc.ShareContent" => [
                        "shareCommentary"   => [ "text" => $message ],
                        "shareMediaCategory" => "IMAGE",
                        "media" => [[
                            "status"      => "READY",
                            "description" => [ "text" => substr($image_description, 0, 200) ],
                            "media"       => $asset_id,
                            "title"       => [ "text" => $image_title ]
                        ]]
                    ]
                ],
                "visibility" => [
                    "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
                ]
            ];
            $options = [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($request)
            ];
            return $this->sendRequest('POST', $url, $options);
        } else {
            return $prepareResponse;
        }
    }

    /**
     * Posts multiple photos to LinkedIn.
     *
     * @param string $accessToken The LinkedIn access token.
     * @param string $person_id   The person's ID.
     * @param string $message     The post message.
     * @param array  $images      An array of image information objects (each with keys: image_path, desc, title).
     * @param string $visibility  Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInMultiplePhotosPost($accessToken, $person_id, $message, array $images, $visibility = "PUBLIC")
    {
        $media = [];
        foreach ($images as $key => $image) {
            // Register upload for each image.
            $prepareUrl = "https://api.linkedin.com/v2/assets?action=registerUpload&oauth2_access_token=" . $accessToken;
            $prepareRequest = [
                "registerUploadRequest" => [
                    "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
                    "owner"   => $this->type . $person_id,
                    "serviceRelationships" => [
                        [
                            "relationshipType" => "OWNER",
                            "identifier"       => "urn:li:userGeneratedContent"
                        ]
                    ]
                ]
            ];
            $options = [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($prepareRequest)
            ];
            $prepareResponse  = $this->sendRequest('POST', $prepareUrl, $options);
            $prepareData = json_decode($prepareResponse);

            if (!isset($prepareData->message)) {
                $uploadURL = $prepareData->value->uploadMechanism
                    ->{"com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"}
                    ->uploadUrl;
                $asset_id = $prepareData->value->asset;

                // Save asset_id in image array for debugging if needed.
                $images[$key]['asset_id'] = $asset_id;
                // Upload the file via PUT.
                try {
                    $fileStream = fopen($image['image_path'], 'r');
                    $this->client->request('PUT', $uploadURL, [
                        'headers' => [ 'Authorization' => 'Bearer ' . $accessToken ],
                        'body'    => $fileStream
                    ]);
                } catch (RequestException $e) {
                    return json_encode(["error" => "Image upload failed.", "details" => $e->getMessage()]);
                }
                // Prepare media data.
                $media[$key]["status"] = "READY";
                $media[$key]["description"]["text"] = substr($image["desc"], 0, 200);
                $media[$key]["media"] = $asset_id;
                $media[$key]["title"]["text"] = substr($image["title"], 0, 200);
            } else {
                return $prepareResponse;
            }
        }
        // Finalize post with all image media.
        $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
        $request = [
            "author"         => $this->type . $person_id,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary"   => [ "text" => $message ],
                    "shareMediaCategory" => "IMAGE",
                    "media"             => array_values($media)
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($request)
        ];
        return $this->sendRequest('POST', $url, $options);
    }

    /**
     * A helper method that sends HTTP requests using Guzzle.
     *
     * @param string $method  The HTTP method (GET, POST, PUT, etc.).
     * @param string $url     The target URL.
     * @param array  $options Optional Guzzle options.
     * @return string The response body.
     */
    private function sendRequest($method, $url, $options = [])
    {
        try {
            $response = $this->client->request($method, $url, $options);
            return (string)$response->getBody();
        } catch (RequestException $e) {
            // Return the response body if available, or the error message.
            if ($e->hasResponse()) {
                return (string)$e->getResponse()->getBody();
            }
            return $e->getMessage();
        }
    }
}