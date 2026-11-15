<?php

declare(strict_types = 1);

namespace Darkroom;

use InvalidArgumentException;

/**
 * Target specifications for the thumbnail
 */
final readonly class Options extends Defaults
{
	/**
	 * @param $crop Gravity name, `x% y%` or the part in pixels
	 * @param $blur Gaussian sigma in output pixels, 1–100
	 * @param $sharpen Amount, 1–100
	 * @param $quality SSIMULACRA2 score, 1–100
	 * @param bool|list<Meta>|null $strip The EXIF categories the thumb strips; `true` all, `false` none
	 * @param $other Keys it doesn't know, such as a plugin's
	 */
	public function __construct(
		public int|null $width = null,
		public int|null $height = null,
		public Rect|string|null $crop = null,
		public int|null $blur = null,
		public bool $grayscale = false,
		public int|null $sharpen = null,
		int|null $quality = null,
		Format|null $format = null,
		bool|null $interlace = null,
		bool|array|null $strip = null,
		public array $other = [],
	) {
		parent::__construct(
			quality:   $quality,
			format:    $format,
			interlace: $interlace,
			strip:     $strip
		);
	}

	public function focus(): Focus|null
	{
		if (is_string($this->crop) === false) {
			return null;
		}

		return Focus::from($this->crop);
	}

	/**
	 * From an array, defaults or a plan's key, also as a string
	 *
	 * @throws InvalidArgumentException For an invalid option or key
	 */
	public static function from(Defaults|Key|array|string $options): static
	{
		if ($options instanceof static) {
			return $options;
		}

		if ($options instanceof Defaults) {
			$options = $options->toArray();
		}

		if (is_string($options) === true) {
			$options = Key::from($options);
		}

		if ($options instanceof Key) {
			return $options->toOptions();
		}

		$defaults  = Defaults::from($options);
		$grayscale = $options['grayscale'] ?? $options['greyscale'] ?? $options['bw'] ?? null;
		$blur      = $options['blur'] ?? null;
		$sharpen   = $options['sharpen'] ?? null;

		unset($options['greyscale'], $options['bw']);

		$other = array_diff_key($options, get_class_vars(self::class));

		return new static(
			width:     static::number('width', $options['width'] ?? null),
			height:    static::number('height', $options['height'] ?? null),
			crop:      static::position($options['crop'] ?? null),
			blur:      static::number('blur', $blur === true ? 10 : $blur, 100),
			grayscale: static::flag('grayscale', $grayscale) === true,
			sharpen:   static::number('sharpen', $sharpen === true ? 50 : $sharpen, 100),
			quality:   $defaults->quality,
			format:    $defaults->format,
			interlace: $defaults->interlace,
			strip:     $defaults->strip,
			other:     $other
		);
	}

	/**
	 * @throws InvalidArgumentException For an invalid crop
	 */
	protected static function position(mixed $position): Rect|string|null
	{
		if (in_array($position, [null, '', false, 0], true) === true) {
			return null;
		}

		if ($position === true) {
			return Gravity::Center->value;
		}

		if ($position instanceof Gravity) {
			return $position->value;
		}

		if ($position instanceof Focus) {
			return (string)$position;
		}

		if ($position instanceof Rect) {
			return $position;
		}

		if (is_string($position) === false) {
			static::error('crop', $position);
		}

		Focus::from($position);

		return $position;
	}

	public function toArray(): array
	{
		$array = [
			...parent::toArray(),
			'width'     => $this->width,
			'height'    => $this->height,
			'crop'      => $this->crop,
			'blur'      => $this->blur,
			'grayscale' => $this->grayscale === true ? true : null,
			'sharpen'   => $this->sharpen,
			...$this->other
		];

		return array_filter($array, fn ($value) => $value !== null);
	}
}
