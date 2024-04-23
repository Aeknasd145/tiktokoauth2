<?php

declare(strict_types=1);

namespace App\TikTok;

use Psr\Http\Message\StreamInterface;

/**
 * Handle response for TikTokOAuth.
 *
 * @author Abbas Eren Kılıç <abbaserenkilic@gmail.com>
 */
class Response
{
	private ?string $apiPath = null;
	private int $httpCode = 0;
	private array $headers = [];
	private array|object|null $body = [];
	private array $xHeaders = [];

	public function setApiPath(string $apiPath): void
	{
		$this->apiPath = $apiPath;
	}

	public function getApiPath(): ?string
	{
		return $this->apiPath;
	}

	public function setBody(array|object $body): void
	{
		if ($body instanceof StreamInterface) {
			$body = (string) $body; // Convert StreamInterface to string
		}

		if (is_string($body)) {
			$body = json_decode($body, true); // Decode JSON string to array/object
		}

		$this->body = $body;
	}

	public function getBody(): array|object|string
	{
		return $this->body;
	}

	public function setHttpCode(int $httpCode): void
	{
		$this->httpCode = $httpCode;
	}

	public function getHttpCode(): int
	{
		return $this->httpCode;
	}

	public function isSuccessful(): bool
	{
		return $this->httpCode >= 200 && $this->httpCode < 300;
	}

	public function setHeaders(array $headers): void
	{
		foreach ($headers as $key => $value) {
			if (str_starts_with($key, 'x')) {
				$this->xHeaders[$key] = $value;
			}
		}
		$this->headers = $headers;
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function setXHeaders(array $xHeaders = []): void
	{
		$this->xHeaders = $xHeaders;
	}

	public function getXHeaders(): array
	{
		return $this->xHeaders;
	}
}
