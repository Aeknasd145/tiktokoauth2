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
