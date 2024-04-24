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

	public function get(string $endpoint, array $params = [], string $url = self::API_HOST): ?Response
	{
		return $this->makeRequest($url, 'GET', $endpoint, $params);
	}

	public function post(string $endpoint, array $params = [], string $baseUrl = self::API_HOST): ?Response
	{
		return $this->makeRequest($baseUrl, 'POST', $endpoint, $params);
	}

	private function makeRequest(string $baseUrl, string $method, string $endpoint, array $params = []): ?Response
	{
		$this->initializeHttpClient($baseUrl);
		$this->resetLastResponse();
		$this->resetAttemptsNumber();

		$headers = [
			'Content-Type' => 'application/x-www-form-urlencoded',
		];

		do {
			try {
				$response = $this->httpClient->request($method, $endpoint, [
					'form_params' => $params,
					'headers' => $headers
				]);

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
}
