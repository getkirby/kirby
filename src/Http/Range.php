<?php

namespace Kirby\Http;

use Kirby\Filesystem\F;

/**
 * Handles HTTP Range requests (RFC 7233)
 * for partial content delivery, primarily
 * used for video streaming in browsers
 *
 * @package   Kirby Http
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.3.0
 */
class Range
{
	/**
	 * Parses the Range header and returns start and end byte positions
	 *
	 * @return array{int, int}|false Array of [start, end] or false if invalid
	 */
	public static function parse(
		string $range,
		int $size
	): array|false {
		if ($size <= 0) {
			return false;
		}

		$range = trim($range);

		// only support byte ranges (not other units)
		if (strncasecmp($range, 'bytes=', 6) !== 0) {
			return false;
		}

		// extract the range part after "bytes="
		$range = substr($range, 6);

		// support only single ranges (not multiple ranges like "0-100,200-300")
		if (str_contains($range, ',') === true) {
			return false;
		}

		// split start and end
		$parts = explode('-', $range, 2);

		if (count($parts) !== 2) {
			return false;
		}

		[$startStr, $endStr] = $parts;
		$startStr = trim($startStr);
		$endStr   = trim($endStr);

		// handle "bytes=-500" (last 500 bytes)
		if ($startStr === '') {
			if (is_numeric($endStr) === false) {
				return false;
			}

			$suffix = (int)$endStr;

			if ($suffix <= 0) {
				return false;
			}

			if ($suffix > $size) {
				$suffix = $size;
			}

			$start = $size - $suffix;
			$end   = $size - 1;

			return [$start, $end];
		}

		// validate that start is numeric
		if (is_numeric($startStr) === false) {
			return false;
		}

		$start = (int)$startStr;

		// handle "bytes=1024-" (from byte 1024 to end)
		if ($endStr === '') {
			$end = $size - 1;
		} elseif (is_numeric($endStr) === false) {
			return false;
		} else {
			$end = (int)$endStr;

			// clamp end to file size if a client overshoots
			if ($end >= $size) {
				$end = $size - 1;
			}
		}

		// validate the range
		if (
			$start < 0 ||
			$start >= $size ||
			$end < $start
		) {
			return false;
		}

		return [$start, $end];
	}

	/**
	 * Creates a response for a partial file request (byte-range)
	 */
	public static function response(
		string $file,
		string $range,
		array $props = []
	): Response {
		// parse the Range header (e.g., "bytes=0-1" or "bytes=1024-")
		$size   = filesize($file);
		$parsed = static::parse($range, $size);

		// if the range is invalid, return 416 Range Not Satisfiable
		if ($parsed === false) {
			return new Response(
				code: 416,
				body: 'Requested Range Not Satisfiable',
				headers: [
					'Content-Range' => 'bytes */' . $size
				]
			);
		}

		[$start, $end] = $parsed;
		$length        = $end - $start + 1;

		// read only the requested byte range from the file
		$body = F::range($file, offset: $start, length: $length);

		$props = Response::ensureSafeMimeType([
			'body' => $body,
			'code' => 206, // Partial Content
			'type' => F::extensionToMime(F::extension($file)),
			'headers' => [
				'Accept-Ranges'  => 'bytes',
				'Content-Range'  => 'bytes ' . $start . '-' . $end . '/' . $size,
				'Content-Length' => $length,
				...$props['headers'] ?? []
			],
			...$props
		]);

		return new Response($props);
	}
}
