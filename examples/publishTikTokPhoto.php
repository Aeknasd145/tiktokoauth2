<?php
    
    use src\TikTok;

    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
    }
    require_once __DIR__ . '/../vendor/autoload.php';

    $tikTok = new TikTok($tikTokClientKey, $tikTokClientSecret, $tikTokCallbackUrl);

    $title = 'Content Title';
    $caption = 'Content caption';
    // $contentFiles has to be array and at least 2 image
    $contentFiles = [
        'https://site.com/file-url.png',
        'https://site.com/file-url-2.png,
    ];

    $photoData = $this->publishTikTokPhoto($accessToken, $title, $caption, $contentFiles);
    
    sleep(5); // wait a few seconds

    // TODO: if status is PROCESSING_DOWNLOAD, it should try again, you should use sleep and var getPostStatus inside do while
	$postStatusData = $this->getPostStatus($accessToken, $photoData['data']['publish_id']);
 
    $postStatus = $postStatusData['data']['status'];
    $postStatusErrorCode = $postStatusData['error']['code'];
    $postStatusErrorMessage = $postStatusData['error']['message'];
    $postStatusErrorLogId = $postStatusData['error']['log_id'];
?>
<a href="<?= $tikTokAuthUrl; ?>">Login with TikTok</a>
