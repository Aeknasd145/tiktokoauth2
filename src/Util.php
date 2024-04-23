<?php

declare(strict_types=1);

namespace App\TikTok;

/**
 * Handle utils for TikTokOAuth.
 *
 * @author Abbas Eren Kılıç <abbaserenkilic@gmail.com>
 */
class Util
{
	public static function jsonDecode(string $string, bool $asArray): array|object
	{
		if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
			return json_decode($string, $asArray, 512, JSON_BIGINT_AS_STRING);
		}

		return json_decode($string, $asArray, 512, JSON_THROW_ON_ERROR);
	}

	public static function sanitizeParameters($params)
	{
		foreach ($params as $key => $value) {
			if (is_string($value)) {
				$params[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			}
		}

		return $params;
	}
}