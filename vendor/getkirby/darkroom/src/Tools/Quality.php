<?php

declare(strict_types = 1);

namespace Darkroom\Tools;

use Darkroom\Format;

/**
 * Quality as a SSIMULACRA2 score
 *
 * @internal
 */
final class Quality
{
	/**
	 * Encoder quality at the scores 0, 5, … 100, with chroma
	 * at half resolution (4:2:0) where the format can choose
	 */
	protected const array HALF = [
		'Avif' => [5, 7, 9, 12, 15, 17, 19, 23, 27, 30, 33, 37, 41, 46, 51, 57, 66, 78, 95, 100, 100],
		'Heic' => [10, 11, 12, 14, 15, 17, 19, 21, 23, 25, 28, 31, 34, 37, 41, 46, 51, 58, 73, 100, 100],
		'Jpeg' => [10, 11, 12, 13, 14, 15, 17, 19, 21, 24, 27, 32, 39, 48, 61, 72, 82, 90, 98, 100, 100],
		'Jxl'  => [1, 1, 3, 5, 8, 10, 13, 15, 17, 19, 23, 28, 39, 52, 63, 71, 80, 87, 93, 98, 100],
		'Webp' => [1, 1, 1, 1, 3, 4, 6, 9, 13, 17, 23, 29, 38, 49, 65, 78, 85, 92, 99, 100, 100],
	];

	/**
	 * libgd's AVIF, which maps quality to quantizers
	 * itself and keeps chroma whole from quality 90 on
	 */
	protected const array LIBGD = [9, 11, 13, 15, 17, 19, 21, 24, 27, 30, 34, 38, 42, 47, 52, 58, 69, 82, 89, 98, 100];

	/**
	 * Encoder quality with whole chroma (4:4:4) at the scores 0, 5, … 100,
	 * from the score on at which it takes fewer bytes than half (4:2:0)
	 */
	protected const array WHOLE = [
		'Avif' => [4 => 12, 15, 18, 20, 24, 28, 31, 35, 40, 44, 49, 55, 63, 74, 88, 100, 100],
		'Heic' => [16 => 51, 57, 67, 100, 100],
		'Jpeg' => [18 => 93, 100, 100],
	];

	/**
	 * The encoder quality for a score and whether
	 * to keep chroma whole, where the encoder can
	 *
	 * @return array{int, bool}
	 */
	public static function for(
		Format $format,
		int $score,
		bool $whole = true
	): array {
		$index = intdiv(min(max($score, 0), 100), 5);
		$whole = $whole === true && isset(static::WHOLE[$format->name][$index]) === true;

		$scales = $whole === true ? static::WHOLE : static::HALF;
		$scale  = $scales[$format->name] ?? null;

		// formats without a scale take the score as is
		if ($scale === null) {
			return [$score, false];
		}

		return [static::scale($scale, $score), $whole];
	}

	public static function libgd(int $score): int
	{
		return static::scale(static::LIBGD, $score);
	}

	/**
	 * The quality at a score, between the two nearest
	 *
	 * @param array<int, int> $scale
	 */
	protected static function scale(array $scale, int $score): int
	{
		$score = min(max($score, 0), 100);
		$index = intdiv($score, 5);
		$low   = (float)($scale[$index] ?? 100);
		$high  = (float)($scale[$index + 1] ?? $low);
		$part  = (float)($score % 5) / 5.0;

		return (int)round($low + $part * ($high - $low));
	}
}
