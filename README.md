# TikTok OAuth2 PHP API
TikTok OAuth 2 PHP8 api created by base of [TikTok Developers Documentation](https://developers.tiktok.com/).
This api just support [Login Kit](https://developers.tiktok.com/products/login-kit/) for now. Other functions will be added soon.

## How to get OAuth Url for Login with TikTok?
$tikTok = new TikTok($tikTokClientKey, $tikTokClientSecret, $tikTokCallbackUrl);
$tikTokAuthUrl = $tikTok->getAuthUrl();
