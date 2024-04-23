<?php
    
    use src\TikTok;

    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
    }
    require_once __DIR__ . '/../vendor/autoload.php';

    $tikTok = new TikTok($tikTokClientKey, $tikTokClientSecret, $tikTokCallbackUrl);
    $tikTokAuthUrl = $tikTok->getAuthUrl();
?>
<a href="<?= $tikTokAuthUrl; ?>">Login with TikTok</a>
