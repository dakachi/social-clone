<?php

namespace Modules\AppChannelXUnofficial\Constants;

/**
 * X (Twitter) API Endpoints (Unofficial, for use with Laravel HTTP/Guzzle)
 * Chú ý: Domain 'twitter.com' vẫn hoạt động ổn định hơn 'x.com' ở hầu hết các API, kể cả upload.
 */
class XEndpoints
{
    // ===== Base URLs =====
    public const BASE_API      = 'https://api.twitter.com/';
    public const BASE_GRAPHQL  = 'https://twitter.com/i/api/graphql/';
    public const BASE_I        = 'https://twitter.com/i/api/';
    public const UPLOAD_MEDIA  = 'https://upload.twitter.com/i/media/upload.json?';

    // ===== Auth, Session =====
    public const GUEST_ACTIVATE    = self::BASE_API . '1.1/guest/activate.json';
    public const ACCOUNT_SETTINGS  = self::BASE_API . '1.1/account/settings.json?include_mention_filter=true&include_nsfw_user_flag=true&include_nsfw_admin_flag=true&include_ranked_timeline=true&include_alt_text_compose=true&ext=ssoConnections&include_country_code=true&include_ext_dm_nsfw_media_filter=true&include_ext_sharing_audiospaces_listening_data_with_followers=true';
    public const SETTINGS = self::BASE_API . '1.1/account/settings.json?include_mention_filter=true&include_nsfw_user_flag=true&include_nsfw_admin_flag=true&include_ranked_timeline=true&include_alt_text_compose=true&ext=ssoConnections&include_country_code=true&include_ext_dm_nsfw_media_filter=true&include_ext_sharing_audiospaces_listening_data_with_followers=true';

    // ===== User/Profile Info =====
    public const USER_BY_SCREEN_NAME = self::BASE_GRAPHQL . 'NimuplG1OB7Fd2btCLdBOw/UserByScreenName?variables=%s&features=%s&fieldToggles=%s';

    // ===== Timeline =====
    public const USER_TWEETS      = self::BASE_GRAPHQL . 'VgitpdpNZ-RUIp5D1Z_D-A/UserTweets?variables=%s&features=%s';
    public const SEARCH_TIMELINE  = self::BASE_GRAPHQL . 'GcjM7tlxA-EAM98COHsYwg/SearchTimeline?variables=%s&features=%s';

    // ===== Tweet Actions =====
    public const CREATE_TWEET     = self::BASE_GRAPHQL . 'BN7FYuBiFfIcD_D76Zea_Q/CreateTweet';

    // ===== Media upload =====
    public const UPLOAD_INIT      = self::UPLOAD_MEDIA . 'command=INIT';
    public const UPLOAD_APPEND    = self::UPLOAD_MEDIA . 'command=APPEND';
    public const UPLOAD_FINALIZE  = self::UPLOAD_MEDIA . 'command=FINALIZE';
    public const UPLOAD_STATUS    = self::UPLOAD_MEDIA . 'command=STATUS';

    // ===== Notifications, Settings, v.v... =====
    public const NOTIFICATIONS = self::BASE_I . '2/notifications/all.json?include_profile_interstitial_type=1&include_blocking=1&include_blocked_by=1&include_followed_by=1&include_want_retweets=1&include_mute_edge=1&include_can_dm=1&include_can_media_tag=1&include_ext_has_nft_avatar=1&include_ext_is_blue_verified=1&include_ext_verified_type=1&include_ext_profile_image_shape=1&skip_status=1&cards_platform=Web-12&include_cards=1&include_ext_alt_text=true&include_ext_limited_action_results=true&include_quote_count=true&include_reply_count=1&tweet_mode=extended&include_ext_views=true&include_entities=true&include_user_entities=true&include_ext_media_color=true&include_ext_media_availability=true&include_ext_sensitive_media_warning=true&include_ext_trusted_friends_metadata=true&send_error_codes=true&simple_quoted_tweet=true&count=20&requestContext=launch&ext=mediaStats%2ChighlightedLabel%2ChasNftAvatar%2CvoiceInfo%2CbirdwatchPivot%2CsuperFollowMetadata%2CunmentionInfo%2CeditControl';

    // ===== OAuth Bearer (Public, hardcoded for unofficial usage) =====
    public const AUTH_BEARER = 'Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA';

    public const QUERY_ID_CREATE_TWEET = 'BN7FYuBiFfIcD_D76Zea_Q';
    public const FIELD_TOGGLES = '{"withArticleRichContentState":false}';
    public const FEATURES = [
        "responsive_web_graphql_exclude_directive_enabled" => true,
        "verified_phone_label_enabled" => false,
        "responsive_web_graphql_timeline_navigation_enabled" => true,
        "responsive_web_edit_tweet_api_enabled" => true,
        "tweetypie_unmention_optimization_enabled" => true,
        "graphql_is_translatable_rweb_tweet_is_translatable_enabled" => true,
        "view_counts_everywhere_api_enabled" => true,
        "longform_notetweets_consumption_enabled" => true,
        "responsive_web_twitter_article_tweet_consumption_enabled" => false,
        "tweet_awards_web_tipping_enabled" => false,
        "longform_notetweets_rich_text_read_enabled" => true,
        "longform_notetweets_inline_media_enabled" => true,
        "responsive_web_media_download_video_enabled" => false,
        "responsive_web_graphql_skip_user_profile_image_extensions_enabled" => false,
        "responsive_web_enhance_cards_enabled" => false,
        "highlights_tweets_tab_ui_enabled" => true,
        "subscriptions_verification_info_is_identity_verified_enabled" => true,
        "hidden_profile_likes_enabled" => false,
        "subscriptions_verification_info_verified_since_enabled" => true,
        "creator_subscriptions_tweet_preview_api_enabled" => true,
        "hidden_profile_subscriptions_enabled" => true,
        "responsive_web_twitter_article_notes_tab_enabled" => false,
    ];
}
