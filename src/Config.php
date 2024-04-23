<?php

declare(strict_types=1);

namespace src;

/**
 * Handle setting and storing config for TikTokOAuth.
 *
 * @author Abbas Eren Kılıç <abbaserenkilic@gmail.com>
 */
class Config
{
	private const SUPPORTED_VERSIONS = ['2'];

	protected int $timeout = 5;
	protected int $connectionTimeout = 5;
	protected int $maxRetries = 0;
	protected int $retriesDelay = 1;
	protected string $apiVersion = '2';
	protected int $chunkSize = 250000; // 0.25 MegaByte
	protected bool $decodeJsonAsArray = false;
	protected array $proxy = [];
	protected bool $gzipEncoding = true;

	public ?string $clientKey;
	public ?string $clientSecret;
	public ?string $accessToken;
	private ?string $redirectUri;
	private ?string $bearer;

	public function __construct(string $clientKey, string $clientSecret, string $redirectUri, ?string $accessToken = NULL)
	{
		$this->clientKey = $clientKey;
		$this->clientSecret = $clientSecret;
		$this->redirectUri = $redirectUri;
		$this->accessToken = $accessToken;
		$this->bearer = $accessToken;
	}

	public function setApiVersion(string $apiVersion): void
	{
		if (in_array($apiVersion, self::SUPPORTED_VERSIONS, true)) {
			$this->apiVersion = $apiVersion;
		} else {
			throw new TikTokOAuthException('Unsupported API version');
		}
	}

	public function setTimeouts(int $connectionTimeout, int $timeout): void
	{
		$this->connectionTimeout = $connectionTimeout;
		$this->timeout = $timeout;
	}

	public function setRetries(int $maxRetries, int $retriesDelay): void
	{
		$this->maxRetries = $maxRetries;
		$this->retriesDelay = $retriesDelay;
	}

	public function setDecodeJsonAsArray(bool $value): void
	{
		$this->decodeJsonAsArray = $value;
	}

	public function setProxy(array $proxy): void
	{
		$this->proxy = $proxy;
	}

	public function setGzipEncoding(bool $gzipEncoding): void
	{
		$this->gzipEncoding = $gzipEncoding;
	}

	public function setChunkSize(int $value): void
	{
		$this->chunkSize = $value;
	}

	public function setAccessToken(string $accessToken): void
	{
		$this->accessToken = $accessToken;
	}

	public function setBearer(string $bearer): void
	{
		$this->bearer = $bearer;
	}

	protected function getClientKey(): string|null
	{
		return $this->clientKey;
	}

	protected function getClientSecret(): string|null
	{
		return $this->clientSecret;
	}

	protected function getAccessToken(): string|null
	{
		return $this->accessToken;
	}

	protected function getBearer(): string|null
	{
		return $this->bearer;
	}

	protected function getRedirectUri(): string|null
	{
		return $this->redirectUri;
	}
}
