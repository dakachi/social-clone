<?php
namespace Modules\AppChannelInstagramUnofficial\Constants;

class InstagramEndpoints
{
    public const BASE_API = 'https://i.instagram.com/api/v1/';
    public const LOGIN = self::BASE_API . 'accounts/login/';
    public const TWO_FACTOR_LOGIN = self::BASE_API . 'accounts/two_factor_login/';
    public const CURRENT_USER = self::BASE_API . 'accounts/current_user/?edit=true';
    public const UPLOAD_PHOTO = 'https://i.instagram.com/rupload_igphoto/';
    public const UPLOAD_VIDEO = 'https://i.instagram.com/rupload_igvideo/';
    public const MEDIA_CONFIGURE = self::BASE_API . 'media/configure/';
    public const MEDIA_CONFIGURE_VIDEO = self::BASE_API . 'media/configure/?video=1';
    public const MEDIA_CONFIGURE_SIDE_CAR = self::BASE_API . 'media/configure_sidecar/';
    public const COMMENT = self::BASE_API . 'media/%s/comment/';
    public const QE_SYNC = self::BASE_API . 'qe/sync/';
    public const PIN_POST = self::BASE_API . 'users/pin_timeline_media/';
    public const MEDIA_CONFIGURE_TO_STORY = self::BASE_API . 'media/configure_to_story/';
    public const LIVE_CREATE = self::BASE_API . 'live/create/';
    public const LIVE_START = self::BASE_API . 'live/%s/start/';
    public const LIVE_END = self::BASE_API . 'live/%s/end/';
    public const LIVE_COMMENT = self::BASE_API . 'live/%s/comment/';
    public const LIVE_PIN_COMMENT = self::BASE_API . 'live/%s/pin_comment/';
    public const MEDIA_LIKE   = self::BASE_API . 'media/%s/like/';
    public const USER_FEED = self::BASE_API . 'feed/user/%s/';
    public const USER_INFO = self::BASE_API . 'users/%s/info/';
    public const USER_FOLLOW  = self::BASE_API . 'friendships/create/%s/';
    public const USER_UNFOLLOW= self::BASE_API . 'friendships/destroy/%s/';
    public const USER_LOOKUP = self::BASE_API . 'users/web_profile_info/?username=%s';
    public const DIRECT_SEND  = self::BASE_API . 'direct_v2/threads/broadcast/text/';
    public const COMMENT_LIKE = self::BASE_API . 'media/%s/comment_like/';
    public const COMMENT_UNLIKE = self::BASE_API . 'media/%s/comment_unlike/';
    public const SET_BIOGRAPHY = self::BASE_API . 'accounts/set_biography/';
    public const SEARCH_TOP = self::BASE_API . 'topsearch/flat/';
}