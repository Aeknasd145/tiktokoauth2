<?php

declare(strict_types=1);

namespace src;

use src\Config;
use src\Response;
use src\Util;
use src\TikTokOAuthException;
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
			'connect_timeout' => $this->connectionTimeout,
			'verify' => $verify,
			'http_errors' => $errors,
			'curl' => [
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => true,
			]
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

	public function get($endpoint, $params = []): ?Response
	{
		return $this->makeRequest('GET', $endpoint, $params);
	}

	public function post(string $endpoint, array $params = []): ?Response
	{
		return $this->makeRequest(self::API_HOST, 'POST', $endpoint, $params);
	}

	private function makeRequest(string $url, string $method, string $endpoint, array $params = []): ?Response
	{
		$this->initializeHttpClient($url);
		$this->resetLastResponse();
		$this->resetAttemptsNumber();
		$params = $this->cleanUpParameters($params);

		$headers = [
			'Authorization' => 'Bearer ' . $this->accessToken,
			'Content-Type' => 'application/json',
		];

		while ($this->requestsAvailable()) {
			try {
				$response = $this->httpClient->request($method, $endpoint, [
					'headers' => $headers,
					'body' => json_encode($params)
				]);

				$this->response->setApiPath($endpoint);
				$this->response->setBody(json_decode((string) $response->getBody(), true));
				$this->response->setHttpCode($response->getStatusCode());
				$this->response->setHeaders($response->getHeaders());

				return $this->response;
			} catch (GuzzleException $e) {
				$this->attempts++;
				$this->sleepIfNeeded();
				throw new TikTokOAuthException($e->getMessage(), $e->getCode());
			}
		}
		throw new TikTokOAuthException("Failed to post after multiple attempts.");
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

	public function getAuthUrl(array $scopes = ['user.info.basic'], string $responseCode = 'code'): string
	{
		$csrfState = bin2hex(random_bytes(16)); // More secure CSRF token generation
		// TODO: CSRF için kullan
		/*setcookie('csrfState', $csrfState, time() + 600);*/

		$scopes = implode(',', $scopes);

		return self::API_HOST . "v{$this->apiVersion}/auth/authorize/?client_key={$this->getClientKey()}&scope={$scopes}&response_type={$responseCode}&redirect_uri={$this->getRedirectUri()}&state={$csrfState}";
	}
}
