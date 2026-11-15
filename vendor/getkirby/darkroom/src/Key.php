<?php

declare(strict_types = 1);

namespace Darkroom;

use InvalidArgumentException;
use Stringable;

/**
 * String representation of a plan,
 * e.g. `300x200-blur5-sharpen25-grayscale-q90`
 */
final readonly class Key implements Stringable
{
	protected function __construct(
		protected string $value
	) {
	}

	public function __toString(): string
	{
		return $this->value;
	}

	public static function for(Plan $plan): static
	{
		$original = $plan->source->size();
		$options  = $plan->options;
		$crop     = $plan->crop;
		$size     = $plan->size();
		$parts    = [$size->width . 'x' . $size->height];

		if (
			$crop->width !== $original->width ||
			$crop->height !== $original->height
		) {
			$parts[] = sprintf(
				'crop%d-%d-%d-%d',
				$crop->x,
				$crop->y,
				$crop->width,
				$crop->height
			);
		}

		if ($options->blur !== null) {
			$parts[] = 'blur' . $options->blur;
		}

		if ($options->sharpen !== null) {
			$parts[] = 'sharpen' . $options->sharpen;
		}

		if ($options->grayscale === true) {
			$parts[] = 'grayscale';
		}

		$parts[] = 'q' . $plan->quality;

		if ($plan->interlace === true) {
			$parts[] = 'interlace';
		}

		// the EXIF categories it keeps:
		// none, all as `exif`, else by code
		$kept  = Meta::kept($plan->strip);
		$codes = array_map(fn (Meta $category) => $category->code(), $kept);

		if ($kept === Meta::cases()) {
			$codes = [];
		}

		if ($kept !== []) {
			$parts[] = implode('-', ['exif', ...$codes]);
		}

		return new static(implode('-', $parts));
	}

	/**
	 * @throws InvalidArgumentException For an invalid key
	 */
	public static function from(string $key): static
	{
		$pattern = self::pattern();

		if (preg_match($pattern, $key) !== 1) {
			throw new InvalidArgumentException(
				message: 'Invalid key: ' . $key
			);
		}

		return new static($key);
	}

	/**
	 * The parts of a key in order, each optional but size and quality
	 *
	 * @return non-empty-string
	 */
	protected static function pattern(): string
	{
		$codes = array_map(fn (Meta $category) => $category->code(), Meta::cases());
		$codes = implode('|', $codes);

		return '/^(\d+)x(\d+)(?:-crop(\d+)-(\d+)-(\d+)-(\d+))?(?:-blur(\d+))?(?:-sharpen(\d+))?(-grayscale)?-q(\d+)(-interlace)?(-exif(?:-(?:' . $codes . '))*)?$/';
	}

	/**
	 * The options the key names, all set
	 * so that defaults change nothing
	 */
	public function toOptions(): Options
	{
		$pattern = self::pattern();
		$crop    = null;

		preg_match($pattern, $this->value, $match, PREG_UNMATCHED_AS_NULL);

		if ($match[3] !== null) {
			$crop = new Rect(
				x:      (int)$match[3],
				y:      (int)$match[4],
				width:  (int)$match[5],
				height: (int)$match[6]
			);
		}

		// strips what a list doesn't name, so that it keeps no category added since
		$codes = explode('-', substr((string)$match[12], 6));
		$strip = match ($match[12]) {
			null    => true,
			'-exif' => false,
			default => array_values(array_filter(
				Meta::cases(),
				fn (Meta $category) => in_array($category->code(), $codes, true) === false
			))
		};

		return Options::from([
			'width'     => $match[1],
			'height'    => $match[2],
			'crop'      => $crop,
			'blur'      => $match[7],
			'sharpen'   => $match[8],
			'grayscale' => $match[9] !== null,
			'quality'   => $match[10],
			'interlace' => $match[11] !== null,
			'strip'     => $strip,
		]);
	}

	public function toString(): string
	{
		return $this->value;
	}
}
