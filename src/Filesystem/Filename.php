<?php

namespace Kirby\Filesystem;

use Kirby\Toolkit\Str;

/**
 * The `Filename` class handles complex
 * mapping of file attributes (i.e for thumbnails)
 * into human readable filenames.
 *
 * ```php
 * $filename = new Filename('some-file.jpg', '{{ name }}-{{ attributes }}.{{ extension }}', [
 *   'crop'    => 'top left',
 *   'width'   => 300,
 *   'height'  => 200
 *   'quality' => 80
 * ]);
 *
 * echo $filename->toString();
 * // result: some-file-300x200-crop-top-left-q80.jpg
 *
 * @package   Kirby Filesystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Filename
{
	/**
	 * List of all applicable attributes
	 */
	protected array $attributes;

	/**
	 * The sanitized file extension
	 */
	protected string $extension;

	/**
	 * The source original filename
	 */
	protected string $filename;

	/**
	 * The sanitized file name
	 */
	protected string $name;

	/**
	 * The template for the final name
	 */
	protected string $template;

	/**
	 * Creates a new Filename object
	 */
	public function __construct(string $filename, string $template, array $attributes = [])
	{
		$this->filename   = $filename;
		$this->template   = $template;
		$this->attributes = $attributes;
		$this->extension  = $this->sanitizeExtension(
			$attributes['format'] ??
			pathinfo($filename, PATHINFO_EXTENSION)
		);
		$this->name       = $this->sanitizeName($filename);
	}

	/**
	 * Converts the entire object to a string
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * Converts all processed attributes
	 * to an array. The array keys are already
	 * the shortened versions for the filename
	 */
	public function attributesToArray(): array
	{
		$array = [
			'dimensions' => implode('x', $this->dimensions()),
			'crop'       => $this->crop(),
			'blur'       => $this->blur(),
			'bw'         => $this->grayscale(),
			'q'          => $this->quality(),
			'sharpen'    => $this->sharpen(),
		];

		$array = array_filter(
			$array,
			fn ($item) => $item !== null && $item !== false && $item !== ''
		);

		return $array;
	}

	/**
	 * Converts all processed attributes
	 * to a string, that can be used in the
	 * new filename
	 *
	 * @param string|null $prefix The prefix will be used in the filename creation
	 */
	public function attributesToString(string|null $prefix = null): string
	{
		$array  = $this->attributesToArray();
		$result = [];

		foreach ($array as $key => $value) {
			if ($value === true) {
				$value = '';
			}

			$result[] = match ($key) {
				'dimensions' => $value,
				'crop'       => ($value === 'center') ? 'crop' : $key . '-' . $value,
				default      => $key . $value
			};
		}

		$result     = array_filter($result);
		$attributes = implode('-', $result);

		if (empty($attributes) === true) {
			return '';
		}

		return $prefix . $attributes;
	}

	/**
	 * Normalizes the blur option value
	 */
	public function blur(): int|false
	{
		$value = $this->attributes['blur'] ?? false;

		if ($value === false) {
			return false;
		}

		return (int)$value;
	}

	/**
	 * Normalizes the crop option value
	 */
	public function crop(): string|false
	{
		// get the crop value
		$crop = $this->attributes['crop'] ?? false;

		if ($crop === false) {
			return false;
		}

		return Str::slug($crop);
	}

	/**
	 * Returns a normalized array
	 * with width and height values
	 * if available
	 */
	public function dimensions(): array
	{
		if (empty($this->attributes['width']) === true && empty($this->attributes['height']) === true) {
			return [];
		}

		return [
			'width'  => $this->attributes['width']  ?? null,
			'height' => $this->attributes['height'] ?? null
		];
	}

	/**
	 * Returns the sanitized extension
	 */
	public function extension(): string
	{
		return $this->extension;
	}

	/**
	 * Normalizes the grayscale option value
	 * and also the available ways to write
	 * the option. You can use `grayscale`,
	 * `greyscale` or simply `bw`. The function
	 * will always return `grayscale`
	 */
	public function grayscale(): bool
	{
		// normalize options
		$value = $this->attributes['grayscale'] ?? $this->attributes['greyscale'] ?? $this->attributes['bw'] ?? false;

		// turn anything into boolean
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Returns the filename without extension
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Normalizes the quality option value
	 */
	public function quality(): int|false
	{
		$value = $this->attributes['quality'] ?? false;

		if ($value === false || $value === true) {
			return false;
		}

		return (int)$value;
	}

	/**
	 * Sanitizes the file extension.
	 * It also replaces `jpeg` with `jpg`.
	 */
	protected function sanitizeExtension(string $extension): string
	{
		$extension = F::safeExtension('test.' . $extension);
		$extension = str_replace('jpeg', 'jpg', $extension);
		return $extension;
	}

	/**
	 * Sanitizes the file name
	 */
	protected function sanitizeName(string $name): string
	{
		return F::safeBasename($name);
	}

	/**
	 * Normalizes the sharpen option value
	 */
	public function sharpen(): int|false
	{
		return match ($this->attributes['sharpen'] ?? false) {
			false   => false,
			true    => 50,
			default => (int)$this->attributes['sharpen']
		};
	}

	/**
	 * Returns the converted filename as string
	 */
	public function toString(): string
	{
		return Str::template($this->template, [
			'name'       => $this->name(),
			'attributes' => $this->attributesToString('-'),
			'extension'  => $this->extension()
		], ['fallback' => '']);
	}
}
