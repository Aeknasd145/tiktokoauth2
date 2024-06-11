# TikTok OAuth2 PHP API
TikTok OAuth 2 PHP8 api created by base of [TikTok Developers Documentation](https://developers.tiktok.com/).
This api just support [Login Kit](https://developers.tiktok.com/doc/login-kit-web/) for now. Other functions will be added soon.

## How to Install via Composer?
`composer require aeknasd145/tiktokoauth2`

## How to get OAuth Url for Login with TikTok?
```php
$tikTok = new TikTok($tikTokClientKey, $tikTokClientSecret, $tikTokCallbackUrl);
$tikTokAuthUrl = $tikTok->getAuthUrl();
```

## When the callback returns, the following steps should be taken:
```php
$accessTokenData = $tikTok->fetchAccessToken($code);
$accessToken = $accessTokenData['access_token'];
$expiresIn = $accessTokenData['expires_in'];
$openId = $accessTokenData['open_id'];
$refreshExpiresIn = $accessTokenData['refresh_expires_in'];
$refreshToken = $accessTokenData['refresh_token'];
$scope = $accessTokenData['scope'];
$tokenType = $accessTokenData['token_type'];
```

## How to refresh access token when needed?
```php
$refreshTokenData = $tikTok->refreshAccessToken($refreshToken);
$accessToken = $refreshTokenData['access_token'];
$expiresIn = $refreshTokenData['expires_in'];
$openId = $refreshTokenData['open_id'];
$refreshExpiresIn = $refreshTokenData['refresh_expires_in'];
$refreshToken = $refreshTokenData['refresh_token'];
$scope = $refreshTokenData['scope'];
$tokenType = $refreshTokenData['token_type'];
```

## Get user basic info (open_id, union_id, avatar_url, avatar_url_100, avatar_large_url, display_name)
```php
$userInfoBasicData = $tiktok->getUserInfoBasic($accessToken)['data']['user'];
$openId = $userInfoBasicData['open_id'];
$unionId = $userInfoBasicData['union_id'];
$avatarUrl = $userInfoBasicData['avatar_url'];
$avatarUrl100 = $userInfoBasicData['avatar_url_100'];
$avatarLargeUrl = $userInfoBasicData['avatar_large_url'];
$displayName = $userInfoBasicData['display_name'];
```

## How to Get Query Creator Info?
```php
$queryCreatorInfo = $tiktok->getQueryCreatorInfo($accessToken)['data'];
$stitchDisabled = $queryCreatorInfo['stitch_disabled'];
$commentDisabled = $queryCreatorInfo['comment_disabled'];
$creatorAvatarUrl = $queryCreatorInfo['creator_avatar_url'];
$creatorNickname = $queryCreatorInfo['creator_nickname'];
$creatorUsername = $queryCreatorInfo['creator_username'];
$duetDisabled = $queryCreatorInfo['duet_disabled'];
$maxVideoPostDurationSec = $queryCreatorInfo['max_video_post_duration_sec'];
$privacyLevelOptions = $queryCreatorInfo['privacy_level_options']; // array, [0]=> string(18) "PUBLIC_TO_EVERYONE" [1]=> string(21) "MUTUAL_FOLLOW_FRIENDS" [2]=> string(9) "SELF_ONLY" 
```

## How to Direct Post Photo?
```php
$title = 'Content Title';
$caption = 'Content caption';
// $contentFiles has to be array and at least 2 image
$contentFiles = [
    'https://site.com/file-url.png',
    'https://site.com/file-url-2.png,
];

$photoData = $this->publishTikTokPhoto($accessToken, $title, $caption, $contentFiles);

// TODO: if status is PROCESSING_DOWNLOAD, it should try again, you should use sleep and var getPostStatus inside do while
sleep(5); // wait a few seconds
$postStatusData = $this->getPostStatus($accessToken, $photoData['data']['publish_id']);

$postStatus = $postStatusData['data']['status'];
$postStatusErrorCode = $postStatusData['error']['code'];
$postStatusErrorMessage = $postStatusData['error']['message'];
$postStatusErrorLogId = $postStatusData['error']['log_id'];
```

## How to Direct Post Video?
```php
$title = 'Content Title';
$videoUrl = 'https://site.com/video.mp4';
$videoCoverTimestampMs = 1000; // ms of the cover picture

$videoData = $this->publishTiktokVideo($accessToken, $title, $videoUrl, $videoCoverTimestampMs);

$videoPublishId = $videoData['data']['publish_id'];
$videoErrorCode = $videoData['error']['code'];
$videoErrorMessage = $videoData['error']['message'];
$videoErrorLogId = $videoData['error']['log_id'];
```

## Error Message Experience Different Then Official Documentation
- If you got 'unaudited_client_can_only_post_to_private_accounts' error, that means you are approved by tiktok for 'video.publish' scope but you need to apply for advanced access. Now, you can only post for private pages, you have this scope for basically testing purposes. Check https://developers.tiktok.com/doc/content-sharing-guidelines#direct_post_api_-_developer_guidelines
- In the Direct Post Video section you will see '--header 'Content-Type: application/json; charset=UTF-8' \' but nevermind I got error when i use it, and it worked when I use 'Content-Type: application/json'


## Credit
[Social Media Tools](https://plexorin.com/).
