# TikTok OAuth2 PHP API
TikTok OAuth 2 PHP8 api created by base of [TikTok Developers Documentation](https://developers.tiktok.com/).
This api just support [Login Kit](https://developers.tiktok.com/doc/login-kit-web/) for now. Other functions will be added soon.

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
