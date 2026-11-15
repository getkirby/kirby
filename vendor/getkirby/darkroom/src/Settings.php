<?php

declare(strict_types = 1);

namespace Darkroom;

use InvalidArgumentException;

/**
 * General settings for `Darkroom` and its drivers
 */
final readonly class Settings
{
	public const int LIMIT = 200_000_000;

	/**
	 * @param $limit Pixel cap for a source and its frames
	 * @param $other Driver-specific settings
	 */
	public function __construct(
		public int|null $limit = self::LIMIT,
		public array $other = [],
	) {
	}

	public static function from(Settings|array $settings): self
	{
		if ($settings instanceof self) {
			return $settings;
		}

		$limit = self::LIMIT;

		if (array_key_exists('limit', $settings) === true) {
			$limit = $settings['limit'];
			unset($settings['limit']);
		}

		if ($limit === null) {
			return new self(limit: null, other: $settings);
		}

		if (is_int($limit) === false || $limit < 1) {
			$value = is_scalar($limit) === true ? var_export($limit, true) : get_debug_type($limit);

			throw new InvalidArgumentException(
				message: 'Invalid limit: ' . $value
			);
		}

		return new self(limit: $limit, other: $settings);
	}

	public function toArray(): array
	{
		return [...$this->other, 'limit' => $this->limit];
	}
}
