<?php
    
    use src\TikTok;

    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
    }
    require_once __DIR__ . '/../vendor/autoload.php';

    $tikTok = new TikTok($tikTokClientKey, $tikTokClientSecret, $tikTokCallbackUrl);

    $title = 'Content Title';
    $videoUrl = 'https://site.com/video.mp4';
    $videoCoverTimestampMs = 1000;
    
    $videoData = $this->publishTiktokVideo($accessToken, $title, $videoUrl, $videoCoverTimestampMs);

    $videoPublishId = $videoData['data']['publish_id'];
    $videoErrorCode = $videoData['error']['code'];
    $videoErrorMessage = $videoData['error']['message'];
    $videoErrorLogId = $videoData['error']['log_id'];
