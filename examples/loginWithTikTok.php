<?php
    
    use src\TikTok;

    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
    }
    require_once __DIR__ . '/../vendor/autoload.php';

    $tikTok = new TikTok($tikTokClientKey, $tikTokClientSecret, $tikTokCallbackUrl);
    $tikTokAuthUrl = $tikTok->getAuthUrl();

    // Lets say you are using the same page as callback
    if($_GET){
        $code = $_GET['code'];
        $accessTokenData = $tiktok->fetchAccessToken($code);

        // All the data returned, probably you will only need access token, expires in, refresh token, refresh expires in
    	$accessToken = $accessTokenData['access_token'];
    	$expiresIn = $accessTokenData['expires_in'];
    	$openId = $accessTokenData['open_id'];
    	$refreshExpiresIn = $accessTokenData['refresh_expires_in'];
    	$refreshToken = $accessTokenData['refresh_token'];
    	$scope = $accessTokenData['scope'];
    	$tokenType = $accessTokenData['token_type'];
    }
?>
<a href="<?= $tikTokAuthUrl; ?>">Login with TikTok</a>
