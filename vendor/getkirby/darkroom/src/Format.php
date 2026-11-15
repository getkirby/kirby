<?php

declare(strict_types = 1);

namespace Darkroom;

use Darkroom\Containers\Bytes;
use InvalidArgumentException;

enum Format
{
	case Avif;
	case Gif;
	case Heic;
	case Jpeg;
	case Jxl;
	case Png;
	case Webp;

	/**
	 * What every major browser shows, in order of preference for thumbs
	 */
	public const array VIEWABLE = [
		self::Webp,
		self::Jpeg,
		self::Png,
		self::Gif,
		self::Avif,
	];

	/**
	 * From a file's first bytes (64 are enough)
	 */
	public static function detect(string $bytes): static|null
	{
		// AVIF and HEIC: the `ftyp` box's major brand and,
		// after the minor version, the compatible ones
		$brands = [];

		if (substr($bytes, 4, 4) === 'ftyp') {
			$size   = Bytes::int(substr($bytes, 0, 4));
			$brands = [
				substr($bytes, 8, 4),
				...str_split(substr($bytes, 16, max(0, $size - 16)), 4)
			];
		}

		// as literals, as the readers' constants would load their classes
		return match (true) {
			str_starts_with($bytes, "\xFF\xD8\xFF") => self::Jpeg,
			str_starts_with($bytes, "\x89PNG\r\n\x1A\n") => self::Png,
			str_starts_with($bytes, 'GIF87a'),
			str_starts_with($bytes, 'GIF89a') => self::Gif,
			str_starts_with($bytes, 'RIFF') && substr($bytes, 8, 4) === 'WEBP' => self::Webp,
			array_intersect($brands, ['avif', 'avis']) !== [] => self::Avif,
			array_intersect($brands, ['mif1', 'msf1', 'heic', 'heix', 'hevc', 'hevx']) !== [] => self::Heic,
			str_starts_with($bytes, "\xFF\x0A"),
			str_starts_with($bytes, "\0\0\0\x0CJXL \r\n\x87\n") => self::Jxl,
			default => null
		};
	}

	public function extension(): string
	{
		return $this->extensions()[0];
	}

	/**
	 * @return non-empty-list<string>
	 */
	protected function extensions(): array
	{
		return match ($this) {
			self::Avif => ['avif'],
			self::Gif  => ['gif'],
			self::Heic => ['heic', 'heif'],
			self::Jpeg => ['jpg', 'jpeg', 'jpe'],
			self::Jxl  => ['jxl'],
			self::Png  => ['png', 'apng'],
			self::Webp => ['webp'],
		};
	}

	/**
	 * From an extension or a MIME type
	 *
	 * @throws InvalidArgumentException For an unknown value
	 */
	public static function from(Format|string $value): static
	{
		if ($value instanceof self) {
			return $value;
		}

		$name = strtolower($value);

		foreach (self::cases() as $format) {
			if (
				in_array($name, $format->extensions(), true) === true ||
				in_array($name, $format->mimes(), true) === true
			) {
				return $format;
			}
		}

		throw new InvalidArgumentException(
			message: 'Unknown image format: ' . $value
		);
	}

	public function isOpaque(): bool
	{
		return $this === self::Jpeg;
	}

	public function mime(): string
	{
		return $this->mimes()[0];
	}

	/**
	 * @return non-empty-list<string>
	 */
	protected function mimes(): array
	{
		return match ($this) {
			self::Avif => ['image/avif'],
			self::Gif  => ['image/gif'],
			self::Heic => ['image/heic', 'image/heif'],
			self::Jpeg => ['image/jpeg', 'image/pjpeg'],
			self::Jxl  => ['image/jxl'],
			self::Png  => ['image/png', 'image/apng'],
			self::Webp => ['image/webp'],
		};
	}

	public static function tryFrom(Format|string $value): static|null
	{
		try {
			return static::from($value);
		} catch (InvalidArgumentException) {
			return null;
		}
	}
}
