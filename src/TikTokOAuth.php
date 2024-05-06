<?php

declare(strict_types=1);

namespace App\TikTok;

use App\TikTok\Config;
use App\TikTok\Response;
use App\TikTok\Util;
use App\TikTok\TikTokOAuthException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * TikTokOAuth class for interacting with the TikTok API.
 *
 * @author Abbas Eren Kılıç <abbaserenkilic@gmail.com>
 */
class TikTokOAuth extends Config
{
	private const API_HOST = 'https://www.tiktok.com/';
	private const UPLOAD_HOST = 'https://open.tiktokapis.com/';

	private ?Client $httpClient;
	private ?Response $response = null;
	private int $attempts = 0;

	public function __construct(string $clientKey, string $clientSecret, string $redirectUri, ?string $accessToken = null)
	{
		parent::__construct($clientKey, $clientSecret, $redirectUri, $accessToken);
		$this->httpClient = new Client();
		$this->resetLastResponse();
	}

	private function initializeHttpClient(string $baseUrl = self::API_HOST, bool $verify = true, bool $errors = false)
	{
		$this->httpClient = new Client([
			'base_uri' => $baseUrl,
			'timeout' => $this->timeout,
		]);
	}

	public function getLastApiPath(): ?string
	{
		return $this->response->getApiPath();
	}

	public function getLastHttpCode(): int
	{
		return $this->response->getHttpCode();
	}

	public function getLastXHeaders(): array
	{
		return $this->response->getXHeaders();
	}

	public function getLastBody(): array|object|null
	{
		return $this->response->getBody();
	}

	public function resetLastResponse(): void
	{
		$this->response = new Response();
	}

	private function resetAttemptsNumber(): void
	{
		$this->attempts = 0;
	}

	private function sleepIfNeeded(): void
	{
		if ($this->maxRetries && $this->attempts) {
			sleep($this->retriesDelay);
		}
	}

	private function cleanUpParameters($params)
	{
		return Util::sanitizeParameters($params);
	}

	protected function apiUrl(string $host, string $path): string
	{
		return sprintf(
			'%s/%s/%s',
			$host,
			$this->apiVersion,
			$path
		);
	}

	private function requestsAvailable(): bool
	{
		return $this->maxRetries &&
			$this->attempts <= $this->maxRetries &&
			$this->getLastHttpCode() >= 500;
	}

	public function get(string $endpoint, array $params = [], string $baseUrl = self::API_HOST, ?array $headers = NULL): ?Response
	{
		if($headers) {
			return $this->makeRequest($baseUrl, 'GET', $endpoint, $params, $headers);
		} else {
			return $this->makeRequest($baseUrl, 'GET', $endpoint, $params);
		}
	}

	public function post(string $endpoint, array $params = [], string $baseUrl = self::API_HOST, ?array $headers = null): ?Response
	{
		if ($headers) {
			return $this->makeRequest($baseUrl, 'POST', $endpoint, $params, $headers);
		} else {
			return $this->makeRequest($baseUrl, 'POST', $endpoint, $params);
		}
	}

	private function makeRequest(string $baseUrl, string $method, string $endpoint, array $params = [], array $headers = ['Content-Type' => 'application/x-www-form-urlencoded']): ?Response
	{
		$this->initializeHttpClient($baseUrl);
		$this->resetLastResponse();
		$this->resetAttemptsNumber();

		$options = [
			'headers' => $headers
		];

		if (!empty($params)) {
			if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
				$options['body'] = json_encode($params);
			} else {
				$options['form_params'] = $params;
			}
		}

		do {
			try {
				$response = $this->httpClient->request($method, $endpoint, $options);

				$body = (string)$response->getBody();
				$parsedBody = json_decode($body, true);

				$this->response->setApiPath($endpoint);
				$this->response->setBody($parsedBody);
				$this->response->setHttpCode($response->getStatusCode());
				$this->response->setHeaders($response->getHeaders());

				return $this->response;
			} catch (GuzzleException $e) {
				$this->attempts++;
				$this->sleepIfNeeded();
				if (!$this->requestsAvailable()) {
					throw new TikTokOAuthException("Maximum retry limit reached with no successful response.", $e->getCode());
				}
			}
		} while ($this->requestsAvailable());

		return $this->response;
	}

	public function getAuthUrl(array $scopes = ['user.info.basic'], string $responseCode = 'code'): string
	{
		$csrfState = bin2hex(random_bytes(16)); // More secure CSRF token generation
		// TODO: Use CSRF for security reasons
		/*setcookie('csrfState', $csrfState, time() + 600);*/

		$scopes = implode(',', $scopes);

		return self::API_HOST . "v{$this->apiVersion}/auth/authorize/?client_key={$this->getClientKey()}&scope={$scopes}&response_type={$responseCode}&redirect_uri={$this->getRedirectUri()}&state={$csrfState}";
	}

	/**
	 *	Create first access token after login callback
	 */
	public function fetchAccessToken(string $code): object|array|string
	{
		$endpoint = 'oauth/token/';
		$params = [
			'client_key' => $this->getClientKey(),
			'client_secret' => $this->getClientSecret(),
			'code' => urldecode($code),
			'grant_type' => 'authorization_code',
			'redirect_uri' => $this->getRedirectUri()
		];

		return $this->post("v{$this->apiVersion}/$endpoint", $params, self::UPLOAD_HOST)->getBody();
	}

	/**
	 *	Refresh access token when needed
	 */
	public function refreshAccessToken(string $refreshToken): object|array|string
	{
		$endpoint = 'oauth/token/';
		$params = [
			'client_key' => $this->getClientKey(),
			'client_secret' => $this->getClientSecret(),
			'grant_type' => 'refresh_token',
			'refresh_token' => $refreshToken
		];

		return $this->post("v{$this->apiVersion}/$endpoint", $params, self::UPLOAD_HOST)->getBody();
	}

	/**
	 *	Get user basic information with access token
  	 *	Available fields: open_id, union_id, avatar_url, avatar_url_100, avatar_large_url, display_name
	 */
	public function getUserInfoBasic(string $accessToken, array $fields = ['display_name', 'avatar_url']): object|array|string
	{
		$endpoint = 'user/info/';
		$fields = implode(',', $fields);
		$endpoint .= '?fields='.$fields;
		$headers = [
			'Authorization' => "Bearer {$accessToken}"
		];
		return $this->get("v{$this->apiVersion}/$endpoint", baseUrl: self::UPLOAD_HOST, headers: $headers)->getBody();
	}

	public function getQueryCreatorInfo(string $accessToken): object|array|string
	{
		$endpoint = 'post/publish/creator_info/query/';

		$headers = [
			'Authorization' => "Bearer {$accessToken}",
			'Content-Type' => "application/json; charset=UTF-8",
		];

		try {
			$response = $this->post("v{$this->apiVersion}/$endpoint", baseUrl: self::UPLOAD_HOST, headers: $headers);

			return $response->getBody();
		} catch (Exception $e) {
			error_log('Error in getQueryCreatorInfo: ' . $e->getMessage());
			return "Error: " . $e->getMessage();
		}
	}

	/*
	 * Only works with url sources
	*/
	public function publishTikTokPhoto(string $accessToken, string $title, string $description, array $photoImages, bool $disableComment = true, string $privacy_level = 'PUBLIC_TO_EVERYONE', bool $autoAddMusic = true): object|array|string
	{
		$endpoint = 'post/publish/content/init/';

		$headers = [
			'Authorization' => "Bearer {$accessToken}",
			'Content-Type' => "application/json"
		];

		$params = [
			'post_info' => [
				"title" => $title,
				"description" => $description,
				"disable_comment" => $disableComment,
				"privacy_level" => 'SELF_ONLY',
				"auto_add_music" => $autoAddMusic
			],
			'source_info' => [
				"source" => "PULL_FROM_URL",
				"photo_cover_index" => 1,
				"photo_images" => $photoImages
			],
			'post_mode' => 'DIRECT_POST',
			'media_type' => 'PHOTO'
		];

		return $this->post("v{$this->apiVersion}/$endpoint", $params, self::UPLOAD_HOST, $headers)->getBody();
	}

	/*
	 * Only works with url sources
  	 * You will see '--header 'Content-Type: application/json; charset=UTF-8' \' in the official documentation but nevermind I got error when i use it, and it worked when I use 'Content-Type: application/json'
	*/
	public function publishTikTokVideo(string $accessToken, string $title, string $videoUrl, int $videoCoverTimestampMs, bool $disableComment = true, string $privacy_level = 'MUTUAL_FOLLOW_FRIENDS', bool $disableDuet = false, bool $disableStitch = false): object|array|string
	{
		$endpoint = 'post/publish/video/init/';

		$headers = [
			'Authorization' => "Bearer {$accessToken}",
			'Content-Type' => "application/json"
		];

		$params = [
			'post_info' => [
				"title" => $title,
				"privacy_level" => 'SELF_ONLY',
				"disable_duet" => $disableDuet,
				"disable_comment" => $disableComment,
				"disable_stitch" => $disableDuet,
				"video_cover_timestamp_ms" => $videoCoverTimestampMs
			],
			'source_info' => [
				"source" => "PULL_FROM_URL",
				"video_url" => $videoUrl
			]
		];

		return $this->post("v{$this->apiVersion}/$endpoint", $params, self::UPLOAD_HOST, $headers)->getBody();
	}

	public function getPostStatus(string $accessToken, string $publishId): object|array|string
	{
		$endpoint = 'post/publish/status/fetch/';

		$headers = [
			'Authorization' => "Bearer {$accessToken}",
			'Content-Type' => "application/json"
		];

		$params = [
			'publish_id' => $publishId
		];

		return $this->post("v{$this->apiVersion}/$endpoint", $params, self::UPLOAD_HOST, $headers)->getBody();
	}
}
