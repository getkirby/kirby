<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Gif;

use Darkroom\Containers\Bytes;
use Darkroom\Formats\Header;
use Darkroom\Formats\Reader as Base;
use Darkroom\Rect;
use Darkroom\Size;
use Generator;

/**
 * @internal
 */
final class Reader extends Base
{
	public const array SIGNATURES = ['GIF87a', 'GIF89a'];

	/**
	 * Start and head of each part by its introducer: `GIF` header,
	 * `,` image, `!` extension with its label, `;` trailer
	 *
	 * @param resource $handle
	 * @return Generator<string, array{int, string}>
	 */
	public static function blocks($handle): Generator
	{
		rewind($handle);

		// the signature and logical screen
		$head = (string)fread($handle, 13);

		if (in_array(substr($head, 0, 6), self::SIGNATURES, true) === false) {
			return;
		}

		yield 'GIF' => [0, $head];

		$table = self::table(ord($head[10] ?? "\0"));

		fseek($handle, 13 + $table);

		for ($step = 0; $step < Bytes::LIMIT; $step++) {
			$start = (int)ftell($handle);
			$kind  = (string)fread($handle, 1);
			$head  = '';

			// an image's descriptor,
			// then its color table and code size
			if ($kind === ',') {
				$head  = (string)fread($handle, 9);
				$table = self::table(ord($head[8] ?? "\0"));
				fseek($handle, $table + 1, SEEK_CUR);
			} elseif ($kind === '!') {
				$kind .= (string)fread($handle, 1);
			} else {
				break;
			}

			// sub-blocks, each read with the next one's length;
			// an extension's first is its head
			$length = Bytes::int((string)fread($handle, 1));

			if ($kind !== ',' && $length > 0) {
				$block  = (string)fread($handle, $length + 1);
				$head   = substr($block, 0, $length);
				$length = ord($block[$length] ?? "\0");
			}

			// on from here, as a copy may move the handle
			$position = (int)ftell($handle);

			yield $kind => [$start, $head];

			Bytes::seek($handle, $position);

			while ($length > 0 && $step++ < Bytes::LIMIT) {
				$block  = (string)fread($handle, $length + 1);
				$length = ord($block[$length] ?? "\0");
			}
		}

		yield ';' => [$start, ''];
	}

	/**
	 * The logical screen,
	 * which some decoders crop to the first image
	 */
	public function canvas(): Rect|null
	{
		$screen = $this->screen();
		$images = $this->images();
		$image  = (string)$images->current();

		if (strlen($screen) < 7 || strlen($image) < 8) {
			return null;
		}

		// left, top, width and height
		/** @var array{1: int, 2: int, 3: int, 4: int} $frame */
		$frame  = unpack('v4', $image);
		$size   = self::size($screen);
		$canvas = new Rect(
			-$frame[1],
			-$frame[2],
			$size->width,
			$size->height
		);

		if ($canvas == new Rect(0, 0, $frame[3], $frame[4])) {
			return null;
		}

		return $canvas;
	}

	/**
	 * Size from the logical screen descriptor
	 */
	public function header(): Header
	{
		$screen = $this->screen();

		if (strlen($screen) < 4) {
			return new Header();
		}

		$size = self::size($screen);

		return new Header($size);
	}

	/**
	 * The descriptor of each image
	 *
	 * @return Generator<int, string>
	 */
	protected function images(): Generator
	{
		$blocks = self::blocks($this->handle);

		foreach ($blocks as $kind => [, $head]) {
			if ($kind === ',') {
				yield $head;
			}
		}
	}

	/**
	 * Two or more images
	 */
	public function isAnimated(): bool
	{
		$images = $this->images();
		$images->next();
		return $images->valid();
	}

	/**
	 * The logical screen descriptor
	 */
	protected function screen(): string
	{
		rewind($this->handle);

		$head = (string)fread($this->handle, 13);

		if (in_array(substr($head, 0, 6), self::SIGNATURES, true) === false) {
			return '';
		}

		return substr($head, 6);
	}

	/**
	 * Width and height at the start of the data
	 */
	protected static function size(string $data): Size
	{
		/** @var array{1: int, 2: int} $sides */
		$sides = unpack('v2', $data);

		return new Size($sides[1], $sides[2]);
	}

	/**
	 * Length of the color table that the flags announce
	 */
	public static function table(int $flags): int
	{
		return ($flags & 0x80) === 0x80 ? 3 << ($flags & 0x07) + 1 : 0;
	}
}
