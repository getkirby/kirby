<?php

declare(strict_types = 1);

namespace Darkroom;

use Darkroom\Exception\UnsupportedException;
use Darkroom\Formats\Header;
use Darkroom\Formats\Reader;
use Darkroom\Meta\Exif;

/**
 * An image's facts, read lazily from its header
 */
final class Source
{
	protected Rect|false|null $canvas = null;

	/**
	 * @var array{int, int, int, int}|null
	 */
	protected array|null $cicp = null;
	protected array|null $exif = null;
	protected Header|null $header = null;
	protected string|null $icc = null;
	protected bool|null $isAnimated = null;
	protected Size|null $size = null;
	protected string|null $tiff = null;

	public function __construct(
		public readonly string $file
	) {
	}

	public function canvas(): Rect|null
	{
		$this->canvas ??= Reader::open($this->file)?->canvas() ?? false;

		return $this->canvas === false ? null : $this->canvas;
	}

	/**
	 * The color signal:
	 * primaries, transfer, matrix and full range (H.273)
	 *
	 * @internal
	 * @return array{int, int, int, int}
	 */
	public function cicp(): array
	{
		return $this->cicp ??= Reader::open($this->file)?->cicp() ?? Reader::UNSPECIFIED;
	}

	/**
	 * The raw tags by section (`IFD0`, `EXIF`, `GPS`, …)
	 */
	public function exif(): array
	{
		if ($this->exif !== null) {
			return $this->exif;
		}

		$tiff = $this->tiff();

		return $this->exif = Exif::tags($tiff);
	}

	/**
	 * @throws UnsupportedException If the file is no image or can't be read
	 */
	public function format(): Format
	{
		return $this->header()->format ?? throw new UnsupportedException(
			message: 'Unsupported or unreadable image: ' . $this->file
		);
	}

	protected function header(): Header
	{
		if ($this->header !== null) {
			return $this->header;
		}

		$reader = Reader::open($this->file);

		if ($reader !== null) {
			$header = $reader->header();

			return $this->header = new Header(
				$header->size,
				$header->orientation,
				$reader->format
			);
		}

		// PHP knows the size of more images, such as BMP or TIFF;
		// a format with a reader failed its stricter signature check
		$size = $this->file === '' ? false : @getimagesize($this->file);

		if (
			$size === false ||
			Format::tryFrom($size['mime']) !== null
		) {
			return $this->header = new Header();
		}

		return $this->header = new Header(
			size: new Size($size[0], $size[1])
		);
	}

	public function icc(): string
	{
		return $this->icc ??= Reader::open($this->file)?->icc() ?? '';
	}

	public function isAnimated(): bool
	{
		return $this->isAnimated ??= Reader::open($this->file)?->isAnimated() === true;
	}

	public function isHdr(): bool
	{
		$transfer = $this->cicp()[1];

		// PQ or HLG (H.273)
		return $transfer === 16 || $transfer === 18;
	}

	/**
	 * The EXIF data the categories keep,
	 * with the orientation unless `None`
	 *
	 * @internal
	 * @param list<Meta> $meta
	 */
	public function keep(
		array $meta,
		Orientation $orientation = Orientation::None
	): string {
		$tags = Meta::exif($meta);
		$tiff = $tags === [] ? '' : $this->tiff();

		return Exif::filter($tiff, $tags, $orientation);
	}

	public function orientation(): Orientation
	{
		return $this->header()->orientation;
	}

	public function size(): Size
	{
		if ($this->size !== null) {
			return $this->size;
		}

		$header = $this->header();

		if (
			$header->size->width === 0 ||
			$header->size->height === 0
		) {
			return $this->size = new Size(0, 0);
		}

		return $this->size = $header->orientation->apply($header->size);
	}

	/**
	 * The EXIF data as stored, in TIFF's layout
	 *
	 * @internal
	 */
	public function tiff(): string
	{
		return $this->tiff ??= Reader::open($this->file)?->exif() ?? '';
	}
}
