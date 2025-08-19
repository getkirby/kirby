<?php

namespace Kirby\Image;

use Closure;
use GdImage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Stringable;

/**
 * Creates a QR code
 * @since 4.0.0
 *
 * @package   Kirby Image
 * @author    Nico Hoffmann <nico@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * QR Code® is a registered trademark of DENSO WAVE INCORPORATED.
 *
 * The code of this class is based on:
 * https://github.com/psyon/php-qrcode
 *
 * qrcode.php - Generate QR Codes. MIT license.
 *
 * Copyright for portions of this project are held by Kreative Software, 2016-2018.
 * All other copyright for the project are held by Donald Becker, 2019
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */
class QrCode implements Stringable
{
	public function __construct(public string $data)
	{
	}

	/**
	 * Returns the QR code as a PNG data URI
	 *
	 * @param int|null $size Image width/height in pixels, defaults to a size per module of 4x4
	 * @param string $color Foreground color in hex format
	 * @param string $back Background color in hex format
	 * @param int $border Border size in number of modules
	 */
	public function toDataUri(
		int|null $size = null,
		string $color = '#000000',
		string $back = '#ffffff',
		int $border = 4
	): string {
		$image = $this->toImage($size, $color, $back, $border);

		ob_start();
		imagepng($image);
		$data = ob_get_contents();
		ob_end_clean();

		return 'data:image/png;base64,' . base64_encode($data);
	}

	/**
	 * Returns the QR code as a GdImage object
	 *
	 * @param int|null $size Image width/height in pixels, defaults to a size per module of 4x4
	 * @param string $color Foreground color in hex format
	 * @param string $back Background color in hex format
	 * @param int $border Border size in number of modules
	 */
	public function toImage(
		int|null $size = null,
		string $color = '#000000',
		string $back = '#ffffff',
		int $border = 4
	): GdImage {
		// get code and size measurements
		$code   = $this->encode($border);
		[$width, $height] = $this->measure($code);
		$size ??= ceil($width * 4);
		$ws     = $size / $width;
		$hs     = $size / $height;

		// create image baseplate
		$image = imagecreatetruecolor($size, $size);

		$allocateColor = static function (string $hex) use ($image) {
			$hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
			$r   = hexdec(substr($hex, 0, 2));
			$g   = hexdec(substr($hex, 2, 2));
			$b   = hexdec(substr($hex, 4, 2));
			return imagecolorallocate($image, $r, $g, $b);
		};

		$back  = $allocateColor($back);
		$color = $allocateColor($color);
		imagefill($image, 0, 0, $back);

		// paint square for each module
		$this->eachModuleGroup(
			$code,
			fn ($x, $y, $width, $height) => imagefilledrectangle(
				$image,
				floor($x * $ws),
				floor($y * $hs),
				floor($x * $ws + $ws * $width) - 1,
				floor($y * $hs + $hs * $height) - 1,
				$color
			)
		);

		return $image;
	}

	/**
	 * Returns the QR code as `<svg>` element
	 *
	 * @param int|string|null $size Optional CSS width of the `<svg>` element
	 * @param string $color Foreground color in hex format
	 * @param string $back Background color in hex format
	 * @param int $border Border size in number of modules
	 */
	public function toSvg(
		int|string|null $size = null,
		string $color = '#000000',
		string $back = '#ffffff',
		int $border = 4
	): string {
		$code = $this->encode($border);
		[$vbw, $vbh] = $this->measure($code);

		$modules = $this->eachModuleGroup(
			$code,
			fn ($x, $y, $width, $height) => 'M' . $x . ',' . $y . 'h' . $width . 'v' . $height . 'h-' . $width . 'z'
		);

		$size = $size ? ' style="width: ' . $size . '"' : '';

		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $vbw . ' ' . $vbh . '" stroke="none"' . $size . '>' .
			'<rect width="100%" height="100%" fill="' . $back . '"/>' .
			'<path d="' . implode(' ', $modules) . '" fill="' . $color . '"/>' .
			'</svg>';
	}

	public function __toString(): string
	{
		return $this->toSvg();
	}

	/**
	 * Saves the QR code to a file.
	 * Supported formats: gif, jpg, jpeg, png, svg, webp
	 *
	 * @param string $file Path to the output file with one of the supported file extensions
	 * @param int|string|null $size Optional image width/height in pixels (defaults to a size per module of 4x4) or CSS width of the `<svg>` element
	 * @param string $color Foreground color in hex format
	 * @param string $back Background color in hex format
	 * @param int $border Border size in number of modules
	 */
	public function write(
		string $file,
		int|string|null $size = null,
		string $color = '#000000',
		string $back = '#ffffff',
		int $border = 4
	): void {
		$format = F::extension($file);
		$args    = [$size, $color, $back, $border];

		match ($format) {
			'gif'   => imagegif($this->toImage(...$args), $file),
			'jpg',
			'jpeg'  => imagejpeg($this->toImage(...$args), $file),
			'png'   => imagepng($this->toImage(...$args), $file),
			'svg'   => F::write($file, $this->toSvg(...$args)),
			'webp'  => imagewebp($this->toImage(...$args), $file),
			default => throw new InvalidArgumentException(
				message: 'Cannot write QR code as ' . $format
			)
		};
	}

	protected function applyMask(array $matrix, int $size, int $mask): array
	{
		for ($i = 0; $i < $size; $i++) {
			for ($j = 0; $j < $size; $j++) {
				if ($matrix[$i][$j] >= 4 && $this->mask($mask, $i, $j)) {
					$matrix[$i][$j] ^= 1;
				}
			}
		}

		return $matrix;
	}

	protected function applyBestMask(array $matrix, int $size): array
	{
		$mask     = 0;
		$mmatrix  = $this->applyMask($matrix, $size, $mask);
		$penalty  = $this->penalty($mmatrix, $size);

		for ($tmask = 1; $tmask < 8; $tmask++) {
			$tmatrix  = $this->applyMask($matrix, $size, $tmask);
			$tpenalty = $this->penalty($tmatrix, $size);

			if ($tpenalty < $penalty) {
				$mask    = $tmask;
				$mmatrix = $tmatrix;
				$penalty = $tpenalty;
			}
		}

		return [$mask, $mmatrix];
	}

	protected function createMatrix(int $version, array $data): array
	{
		$size   = $version * 4 + 17;
		$matrix = [];
		$row    = array_fill(0, $size, 0);

		for ($i = 0; $i < $size; $i++) {
			$matrix[] = $row;
		}

		// finder patterns
		for ($i = 0; $i < 8; $i++) {
			for ($j = 0; $j < 8; $j++) {
				$m = (($i == 7 || $j == 7) ? 2 :
					 (($i == 0 || $j == 0 || $i == 6 || $j == 6) ? 3 :
					 (($i == 1 || $j == 1 || $i == 5 || $j == 5) ? 2 : 3)));
				$matrix[$i][$j] = $m;
				$matrix[$size - $i - 1][$j] = $m;
				$matrix[$i][$size - $j - 1] = $m;
			}
		}

		// alignment patterns
		if ($version >= 2) {
			$alignment = static::ALIGNMENT_PATTERNS[$version - 2];

			foreach ($alignment as $i) {
				foreach ($alignment as $j) {
					if (!$matrix[$i][$j]) {
						for ($ii = -2; $ii <= 2; $ii++) {
							for ($jj = -2; $jj <= 2; $jj++) {
								$m = (max(abs($ii), abs($jj)) & 1) ^ 3;
								$matrix[$i + $ii][$j + $jj] = $m;
							}
						}
					}
				}
			}
		}

		// timing patterns
		for ($i = $size - 9; $i >= 8; $i--) {
			$matrix[$i][6] = ($i & 1) ^ 3;
			$matrix[6][$i] = ($i & 1) ^ 3;
		}

		// dark module – such an ominous name for such an innocuous thing
		$matrix[$size - 8][8] = 3;

		// format information area
		for ($i = 0; $i <= 8; $i++) {
			if (!$matrix[$i][8]) {
				$matrix[$i][8] = 1;
			}
			if (!$matrix[8][$i]) {
				$matrix[8][$i] = 1;
			}
			if ($i && !$matrix[$size - $i][8]) {
				$matrix[$size - $i][8] = 1;
			}
			if ($i && !$matrix[8][$size - $i]) {
				$matrix[8][$size - $i] = 1;
			}
		}

		// version information area
		if ($version >= 7) {
			for ($i = 9; $i < 12; $i++) {
				for ($j = 0; $j < 6; $j++) {
					$matrix[$size - $i][$j] = 1;
					$matrix[$j][$size - $i] = 1;
				}
			}
		}

		// data
		$col    = $size - 1;
		$row    = $size - 1;
		$dir    = -1;
		$offset = 0;
		$length = count($data);

		while ($col > 0 && $offset < $length) {
			if (!$matrix[$row][$col]) {
				$matrix[$row][$col] = $data[$offset] ? 5 : 4;
				$offset++;
			}
			if (!$matrix[$row][$col - 1]) {
				$matrix[$row][$col - 1] = $data[$offset] ? 5 : 4;
				$offset++;
			}
			$row += $dir;
			if ($row < 0 || $row >= $size) {
				$dir = -$dir;
				$row += $dir;
				$col -= 2;

				if ($col == 6) {
					$col--;
				}
			}
		}

		return [$size, $matrix];
	}

	/**
	 * Loops over every row and column, finds all modules that can
	 * be grouped as rectangle (starting at the top left corner)
	 * and applies the given action to each active module group
	 */
	protected function eachModuleGroup(array $code, Closure $action): array
	{
		$result = [];
		$xStart = $code['q'][3];
		$yStart = $code['q'][0];

		// generate empty matrix to track what modules have been covered
		$covered = array_fill(0, count($code['bits']), array_fill(0, count($code['bits'][0]), 0));

		foreach ($code['bits'] as $by => $row) {
			foreach ($row as $bx => $module) {
				// skip if module is inactive or already covered
				if ($module === 0 || $covered[$by][$bx] === 1) {
					continue;
				}

				$width  = 0;
				$height = 0;

				$rowLength = count($row);
				$colLength = count($code['bits']);

				// extend to the right as long as the modules are active
				// and use this to determine the width of the group
				for ($x = $bx; $x < $rowLength; $x++) {
					if ($row[$x] === 0) {
						break;
					}
					$width++;
					$covered[$by][$x] = 1;
				}

				// extend downwards as long as all the modules
				// at the same width range are active;
				// use this to determine the height of the group
				for ($y = $by; $y < $colLength; $y++) {
					$below = array_slice($code['bits'][$y], $bx, $width);

					// if the sum is less than the width,
					// there is at least one inactive module
					if (array_sum($below) < $width) {
						break;
					}

					$height++;

					for ($x = $bx; $x < $bx + $width; $x++) {
						$covered[$y][$x] = 1;
					}
				}

				$result[] = $action(
					$xStart + $bx,
					$yStart + $by,
					$width,
					$height
				);
			}
		}

		return $result;
	}

	protected function encode(int $q = 4): array
	{
		[$data, $version, $ecl, $ec] = $this->encodeData();
		$data = $this->encodeErrorCorrection($data, $ec, $version);
		[$size, $mtx] = $this->createMatrix($version, $data);
		[$mask, $mtx] = $this->applyBestMask($mtx, $size);
		$mtx = $this->finalizeMatrix($mtx, $size, $ecl, $mask, $version);

		return [
			'q'    => [$q, $q, $q, $q],
			'size' => [$size, $size],
			'bits' => $mtx
		];
	}

	protected function encodeData(): array
	{
		$mode = $this->mode();
		[$version, $ecl] = $this->version($mode);

		$group = match (true) {
			$version >= 27 => 2,
			$version >= 10 => 1,
			default        => 0
		};

		$ec = static::EC_PARAMS[($version - 1) * 4 + $ecl];

		// don't cut off mid-character if exceeding capacity
		$max_chars = static::CAPACITY[$version - 1][$ecl][$mode];

		if ($mode == 3) {
			$max_chars <<= 1;
		}

		$data = substr($this->data, 0, $max_chars);

		// convert from character level to bit level
		$code = match ($mode) {
			0 => $this->encodeNumeric($data, $group),
			1 => $this->encodeAlphanum($data, $group),
			2 => $this->encodeBinary($data, $group),
			default => throw new LogicException(message: 'Invalid QR mode') // @codeCoverageIgnore
		};

		$code = [...$code, ...array_fill(0, 4, 0)];

		if ($remainder = count($code) % 8) {
			$code = [...$code, ...array_fill(0, 8 - $remainder, 0)];
		}

		// convert from bit level to byte level
		$data = [];

		for ($i = 0, $n = count($code); $i < $n; $i += 8) {
			$byte = 0;

			if ($code[$i + 0]) {
				$byte |= 0x80;
			}
			if ($code[$i + 1]) {
				$byte |= 0x40;
			}
			if ($code[$i + 2]) {
				$byte |= 0x20;
			}
			if ($code[$i + 3]) {
				$byte |= 0x10;
			}
			if ($code[$i + 4]) {
				$byte |= 0x08;
			}
			if ($code[$i + 5]) {
				$byte |= 0x04;
			}
			if ($code[$i + 6]) {
				$byte |= 0x02;
			}
			if ($code[$i + 7]) {
				$byte |= 0x01;
			}

			$data[] = $byte;
		}

		for (
			$i = count($data),
			$a = 1,
			$n = $ec[0];
			$i < $n;
			$i++,
			$a ^= 1
		) {
			$data[] = $a ? 236 : 17;
		}

		return [
			$data,
			$version,
			$ecl,
			$ec
		];
	}

	protected function encodeNumeric($data, $version_group): array
	{
		$code   = [0, 0, 0, 1];
		$length = strlen($data);

		switch ($version_group) {
			case 2: // 27 - 40
				$code[] = $length & 0x2000;
				$code[] = $length & 0x1000;
				// no break
			case 1: // 10 - 26
				$code[] = $length & 0x0800;
				$code[] = $length & 0x0400;
				// no break
			case 0: // 1 - 9
				$code[] = $length & 0x0200;
				$code[] = $length & 0x0100;
				$code[] = $length & 0x0080;
				$code[] = $length & 0x0040;
				$code[] = $length & 0x0020;
				$code[] = $length & 0x0010;
				$code[] = $length & 0x0008;
				$code[] = $length & 0x0004;
				$code[] = $length & 0x0002;
				$code[] = $length & 0x0001;
		}
		for ($i = 0; $i < $length; $i += 3) {
			$group = substr($data, $i, 3);
			switch (strlen($group)) {
				case 3:
					$code[] = $group & 0x200;
					$code[] = $group & 0x100;
					$code[] = $group & 0x080;
					// no break
				case 2:
					$code[] = $group & 0x040;
					$code[] = $group & 0x020;
					$code[] = $group & 0x010;
					// no break
				case 1:
					$code[] = $group & 0x008;
					$code[] = $group & 0x004;
					$code[] = $group & 0x002;
					$code[] = $group & 0x001;
			}
		}
		return $code;
	}

	protected function encodeAlphanum($data, $version_group): array
	{
		$alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';
		$code = [0, 0, 1, 0];
		$length = strlen($data);
		switch ($version_group) {
			case 2: // 27 - 40
				$code[] = $length & 0x1000;
				$code[] = $length & 0x0800;
				// no break
			case 1: // 10 - 26
				$code[] = $length & 0x0400;
				$code[] = $length & 0x0200;
				// no break
			case 0: // 1 - 9
				$code[] = $length & 0x0100;
				$code[] = $length & 0x0080;
				$code[] = $length & 0x0040;
				$code[] = $length & 0x0020;
				$code[] = $length & 0x0010;
				$code[] = $length & 0x0008;
				$code[] = $length & 0x0004;
				$code[] = $length & 0x0002;
				$code[] = $length & 0x0001;
		}
		for ($i = 0; $i < $length; $i += 2) {
			$group = substr($data, $i, 2);
			if (strlen($group) > 1) {
				$c1 = strpos($alphabet, substr($group, 0, 1));
				$c2 = strpos($alphabet, substr($group, 1, 1));
				$ch = $c1 * 45 + $c2;
				$code[] = $ch & 0x400;
				$code[] = $ch & 0x200;
				$code[] = $ch & 0x100;
				$code[] = $ch & 0x080;
				$code[] = $ch & 0x040;
				$code[] = $ch & 0x020;
				$code[] = $ch & 0x010;
				$code[] = $ch & 0x008;
				$code[] = $ch & 0x004;
				$code[] = $ch & 0x002;
				$code[] = $ch & 0x001;
			} else {
				$ch = strpos($alphabet, $group);
				$code[] = $ch & 0x020;
				$code[] = $ch & 0x010;
				$code[] = $ch & 0x008;
				$code[] = $ch & 0x004;
				$code[] = $ch & 0x002;
				$code[] = $ch & 0x001;
			}
		}
		return $code;
	}

	protected function encodeBinary(string $data, int $version_group): array
	{
		$code   = [0, 1, 0, 0];
		$length = strlen($data);

		switch ($version_group) {
			case 2:  // 27 - 40
			case 1:  // 10 - 26
				$code[] = $length & 0x8000;
				$code[] = $length & 0x4000;
				$code[] = $length & 0x2000;
				$code[] = $length & 0x1000;
				$code[] = $length & 0x0800;
				$code[] = $length & 0x0400;
				$code[] = $length & 0x0200;
				$code[] = $length & 0x0100;
				// no break
			case 0:  // 1 - 9
				$code[] = $length & 0x0080;
				$code[] = $length & 0x0040;
				$code[] = $length & 0x0020;
				$code[] = $length & 0x0010;
				$code[] = $length & 0x0008;
				$code[] = $length & 0x0004;
				$code[] = $length & 0x0002;
				$code[] = $length & 0x0001;
		}

		for ($i = 0; $i < $length; $i++) {
			$ch = ord(substr($data, $i, 1));
			$code[] = $ch & 0x80;
			$code[] = $ch & 0x40;
			$code[] = $ch & 0x20;
			$code[] = $ch & 0x10;
			$code[] = $ch & 0x08;
			$code[] = $ch & 0x04;
			$code[] = $ch & 0x02;
			$code[] = $ch & 0x01;
		}

		return $code;
	}

	protected function encodeErrorCorrection(
		array $data,
		array $ec_params,
		int $version
	): array {
		$blocks    = $this->errorCorrectionSplit($data, $ec_params);
		$ec_blocks = [];

		for ($i = 0, $n = count($blocks); $i < $n; $i++) {
			$ec_blocks[] = $this->errorCorrectionDivide($blocks[$i], $ec_params);
		}

		$data    = $this->errorCorrectionInterleave($blocks);
		$ec_data = $this->errorCorrectionInterleave($ec_blocks);
		$code    = [];

		foreach ($data as $ch) {
			$code[] = $ch & 0x80;
			$code[] = $ch & 0x40;
			$code[] = $ch & 0x20;
			$code[] = $ch & 0x10;
			$code[] = $ch & 0x08;
			$code[] = $ch & 0x04;
			$code[] = $ch & 0x02;
			$code[] = $ch & 0x01;
		}
		foreach ($ec_data as $ch) {
			$code[] = $ch & 0x80;
			$code[] = $ch & 0x40;
			$code[] = $ch & 0x20;
			$code[] = $ch & 0x10;
			$code[] = $ch & 0x08;
			$code[] = $ch & 0x04;
			$code[] = $ch & 0x02;
			$code[] = $ch & 0x01;
		}
		for ($n = static::REMAINER_BITS[$version - 1]; $n > 0; $n--) {
			$code[] = 0;
		}

		return $code;
	}

	protected function errorCorrectionSplit(array $data, array $ec): array
	{
		$blocks = [];
		$offset = 0;

		for ($i = $ec[2], $length = $ec[3]; $i > 0; $i--) {
			$blocks[] = array_slice($data, $offset, $length);
			$offset += $length;
		}
		for ($i = $ec[4], $length = $ec[5]; $i > 0; $i--) {
			$blocks[] = array_slice($data, $offset, $length);
			$offset += $length;
		}

		return $blocks;
	}

	protected function errorCorrectionDivide(array $data, array $ec): array
	{
		$num_data  = count($data);
		$num_error = $ec[1];
		$generator = static::EC_POLYNOMIALS[$num_error];
		$message   = $data;

		for ($i = 0; $i < $num_error; $i++) {
			$message[] = 0;
		}

		for ($i = 0; $i < $num_data; $i++) {
			if ($message[$i]) {
				$leadterm = static::LOG[$message[$i]];

				for ($j = 0; $j <= $num_error; $j++) {
					$term = ($generator[$j] + $leadterm) % 255;
					$message[$i + $j] ^= static::EXP[$term];
				}
			}
		}

		return array_slice($message, $num_data, $num_error);
	}

	protected function errorCorrectionInterleave(array $blocks): array
	{
		$data       = [];
		$num_blocks = count($blocks);

		for ($offset = 0; true; $offset++) {
			$break = true;

			for ($i = 0; $i < $num_blocks; $i++) {
				if (isset($blocks[$i][$offset]) === true) {
					$data[] = $blocks[$i][$offset];
					$break = false;
				}
			}

			if ($break) {
				break;
			}
		}

		return $data;
	}

	protected function finalizeMatrix(
		array $matrix,
		int $size,
		int $ecl,
		int $mask,
		int $version
	): array {
		// Format info
		$format = static::FORMAT_INFO[$ecl * 8 + $mask];
		$matrix[8][0] = $format[0];
		$matrix[8][1] = $format[1];
		$matrix[8][2] = $format[2];
		$matrix[8][3] = $format[3];
		$matrix[8][4] = $format[4];
		$matrix[8][5] = $format[5];
		$matrix[8][7] = $format[6];
		$matrix[8][8] = $format[7];
		$matrix[7][8] = $format[8];
		$matrix[5][8] = $format[9];
		$matrix[4][8] = $format[10];
		$matrix[3][8] = $format[11];
		$matrix[2][8] = $format[12];
		$matrix[1][8] = $format[13];
		$matrix[0][8] = $format[14];
		$matrix[$size - 1][8] = $format[0];
		$matrix[$size - 2][8] = $format[1];
		$matrix[$size - 3][8] = $format[2];
		$matrix[$size - 4][8] = $format[3];
		$matrix[$size - 5][8] = $format[4];
		$matrix[$size - 6][8] = $format[5];
		$matrix[$size - 7][8] = $format[6];
		$matrix[8][$size - 8] = $format[7];
		$matrix[8][$size - 7] = $format[8];
		$matrix[8][$size - 6] = $format[9];
		$matrix[8][$size - 5] = $format[10];
		$matrix[8][$size - 4] = $format[11];
		$matrix[8][$size - 3] = $format[12];
		$matrix[8][$size - 2] = $format[13];
		$matrix[8][$size - 1] = $format[14];

		// version info
		if ($version >= 7) {
			$version = static::VERSION_INFO[$version - 7];

			for ($i = 0; $i < 18; $i++) {
				$r = $size - 9 - ($i % 3);
				$c = 5 - floor($i / 3);
				$matrix[$r][$c] = $version[$i];
				$matrix[$c][$r] = $version[$i];
			}
		}

		// patterns and data
		for ($i = 0; $i < $size; $i++) {
			for ($j = 0; $j < $size; $j++) {
				$matrix[$i][$j] &= 1;
			}
		}

		return $matrix;
	}

	protected function mask(int $mask, int $row, int $column): int
	{
		return match ($mask) {
			0 => !(($row + $column) % 2),
			1 => !($row % 2),
			2 => !($column % 3),
			3 => !(($row + $column) % 3),
			4 => !((floor($row / 2) + floor($column / 3)) % 2),
			5 => !(((($row * $column) % 2) + (($row * $column) % 3))),
			6 => !(((($row * $column) % 2) + (($row * $column) % 3)) % 2),
			7 => !(((($row + $column) % 2) + (($row * $column) % 3)) % 2),
			default => throw new LogicException(message: 'Invalid QR mask') // @codeCoverageIgnore
		};
	}

	/**
	 * Returns width and height based on the
	 * generated modules and quiet zone
	 */
	protected function measure($code): array
	{
		return [
			$code['q'][3] + $code['size'][0] + $code['q'][1],
			$code['q'][0] + $code['size'][1] + $code['q'][2]
		];
	}

	/**
	 * Detect what encoding mode (numeric, alphanumeric, binary)
	 * can be used
	 */
	protected function mode(): int
	{
		// numeric
		if (preg_match('/^[0-9]*$/', $this->data)) {
			return 0;
		}

		// alphanumeric
		if (preg_match('/^[0-9A-Z .\/:$%*+-]*$/', $this->data)) {
			return 1;
		}

		return 2;
	}

	protected function penalty(array &$matrix, int $size): int
	{
		$score  = $this->penalty1($matrix, $size);
		$score += $this->penalty2($matrix, $size);
		$score += $this->penalty3($matrix, $size);
		$score += $this->penalty4($matrix, $size);
		return $score;
	}

	protected function penalty1(array &$matrix, int $size): int
	{
		$score = 0;

		for ($i = 0; $i < $size; $i++) {
			$rowvalue = 0;
			$rowcount = 0;
			$colvalue = 0;
			$colcount = 0;

			for ($j = 0; $j < $size; $j++) {
				$rv = ($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) ? 1 : 0;
				$cv = ($matrix[$j][$i] == 5 || $matrix[$j][$i] == 3) ? 1 : 0;

				if ($rv == $rowvalue) {
					$rowcount++;
				} else {
					if ($rowcount >= 5) {
						$score += $rowcount - 2;
					}
					$rowvalue = $rv;
					$rowcount = 1;
				}

				if ($cv == $colvalue) {
					$colcount++;
				} else {
					if ($colcount >= 5) {
						$score += $colcount - 2;
					}
					$colvalue = $cv;
					$colcount = 1;
				}
			}

			if ($rowcount >= 5) {
				$score += $rowcount - 2;
			}
			if ($colcount >= 5) {
				$score += $colcount - 2;
			}
		}

		return $score;
	}

	protected function penalty2(array &$matrix, int $size): int
	{
		$score = 0;

		for ($i = 1; $i < $size; $i++) {
			for ($j = 1; $j < $size; $j++) {
				$v1 = $matrix[$i - 1][$j - 1];
				$v2 = $matrix[$i - 1][$j    ];
				$v3 = $matrix[$i    ][$j - 1];
				$v4 = $matrix[$i    ][$j    ];
				$v1 = ($v1 == 5 || $v1 == 3) ? 1 : 0;
				$v2 = ($v2 == 5 || $v2 == 3) ? 1 : 0;
				$v3 = ($v3 == 5 || $v3 == 3) ? 1 : 0;
				$v4 = ($v4 == 5 || $v4 == 3) ? 1 : 0;

				if ($v1 == $v2 && $v2 == $v3 && $v3 == $v4) {
					$score += 3;
				}
			}
		}

		return $score;
	}

	protected function penalty3(array &$matrix, int $size): int
	{
		$score = 0;

		for ($i = 0; $i < $size; $i++) {
			$rowvalue = 0;
			$colvalue = 0;

			for ($j = 0; $j < 11; $j++) {
				$rv = ($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) ? 1 : 0;
				$cv = ($matrix[$j][$i] == 5 || $matrix[$j][$i] == 3) ? 1 : 0;
				$rowvalue = (($rowvalue << 1) & 0x7FF) | $rv;
				$colvalue = (($colvalue << 1) & 0x7FF) | $cv;
			}

			if ($rowvalue == 0x5D0 || $rowvalue == 0x5D) {
				$score += 40;
			}
			if ($colvalue == 0x5D0 || $colvalue == 0x5D) {
				$score += 40;
			}

			for ($j = 11; $j < $size; $j++) {
				$rv = ($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) ? 1 : 0;
				$cv = ($matrix[$j][$i] == 5 || $matrix[$j][$i] == 3) ? 1 : 0;
				$rowvalue = (($rowvalue << 1) & 0x7FF) | $rv;
				$colvalue = (($colvalue << 1) & 0x7FF) | $cv;

				if ($rowvalue == 0x5D0 || $rowvalue == 0x5D) {
					$score += 40;
				}

				if ($colvalue == 0x5D0 || $colvalue == 0x5D) {
					$score += 40;
				}
			}
		}

		return $score;
	}

	protected function penalty4(array &$matrix, int $size): int
	{
		$dark = 0;

		for ($i = 0; $i < $size; $i++) {
			for ($j = 0; $j < $size; $j++) {
				if ($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) {
					$dark++;
				}
			}
		}

		$dark *= 20;
		$dark /= $size * $size;
		$a     = abs(floor($dark) - 10);
		$b     = abs(ceil($dark) - 10);
		return min($a, $b) * 10;
	}

	/**
	 * Detect what version needs to be used by
	 * trying to maximize the error correction level
	 */
	protected function version(int $mode): array
	{
		$length = strlen($this->data);

		if ($mode == 3) {
			$length >>= 1;
		}

		$ecl = 0;

		// first try to find the minimum version
		// that can contain the data
		for ($version = 1; $version <= 40; $version++) {
			if ($length <= static::CAPACITY[$version - 1][$ecl][$mode]) {
				break;
			}
		}

		// with the version in place, try to raise
		// the error correction level as long as
		// the data still fits
		for ($newEcl = 1; $newEcl <= 3; $newEcl++) {
			if ($length <= static::CAPACITY[$version - 1][$newEcl][$mode]) {
				$ecl = $newEcl;
			}
		}

		return [$version, $ecl];
	}

	/**
	 * maximum encodable characters = $qr_capacity [ (version - 1) ]
	 * [ (0 for L, 1 for M, 2 for Q, 3 for H) ]
	 * [ (0 for numeric, 1 for alpha, 2 for binary) ]
	 */
	protected const CAPACITY = [
		[
			[41, 25, 17],
			[34, 20, 14],
			[27, 16, 11],
			[17, 10, 7]
		],
		[
			[77, 47, 32],
			[63, 38, 26],
			[48, 29, 20],
			[34, 20, 14]
		],
		[
			[127, 77, 53],
			[101, 61, 42],
			[77, 47, 32],
			[58, 35, 24]
		],
		[
			[187, 114, 78],
			[149, 90, 62],
			[111, 67, 46],
			[82, 50, 34]
		],
		[
			[255, 154, 106],
			[202, 122, 84],
			[144, 87, 60],
			[106, 64, 44]
		],
		[
			[322, 195, 134],
			[255, 154, 106],
			[178, 108, 74],
			[139, 84, 58]
		],
		[
			[370, 224, 154],
			[293, 178, 122],
			[207, 125, 86],
			[154, 93, 64]
		],
		[
			[461, 279, 192],
			[365, 221, 152],
			[259, 157, 108],
			[202, 122, 84]
		],
		[
			[552, 335, 230],
			[432, 262, 180],
			[312, 189, 130],
			[235, 143, 98]],
		[
			[652, 395, 271],
			[513, 311, 213],
			[364, 221, 151],
			[288, 174, 119]
		],
		[
			[772, 468, 321],
			[604, 366, 251],
			[427, 259, 177],
			[331, 200, 137]
		],
		[
			[883, 535, 367],
			[691, 419, 287],
			[489, 296, 203],
			[374, 227, 155]
		],
		[
			[1022, 619, 425],
			[796, 483, 331],
			[580, 352, 241],
			[427, 259, 177]
		],
		[
			[1101, 667, 458],
			[871, 528, 362],
			[621, 376, 258],
			[468, 283, 194]
		],
		[
			[1250, 758, 520],
			[991, 600, 412],
			[703, 426, 292],
			[530, 321, 220]
		],
		[
			[1408, 854, 586],
			[1082, 656, 450],
			[775, 470, 322],
			[602, 365, 250]
		],
		[
			[1548, 938, 644],
			[1212, 734, 504],
			[876, 531, 364],
			[674, 408, 280]
		],
		[
			[1725, 1046, 718],
			[1346, 816, 560],
			[948, 574, 394],
			[746, 452, 310]
		],
		[
			[1903, 1153, 792],
			[1500, 909, 624],
			[1063, 644, 442],
			[813, 493, 338]
		],
		[
			[2061, 1249, 858],
			[1600, 970, 666],
			[1159, 702, 482],
			[919, 557, 382]
		],
		[
			[2232, 1352, 929],
			[1708, 1035, 711],
			[1224, 742, 509],
			[969, 587, 403]
		],
		[
			[2409, 1460, 1003],
			[1872, 1134, 779],
			[1358, 823, 565],
			[1056, 640, 439]
		],
		[
			[2620, 1588, 1091],
			[2059, 1248, 857],
			[1468, 890, 611],
			[1108, 672, 461]
		],
		[
			[2812, 1704, 1171],
			[2188, 1326, 911],
			[1588, 963, 661],
			[1228, 744, 511]
		],
		[
			[3057, 1853, 1273],
			[2395, 1451, 997],
			[1718, 1041, 715],
			[1286, 779, 535]
		],
		[
			[3283, 1990, 1367],
			[2544, 1542, 1059],
			[1804, 1094, 751],
			[1425, 864, 593]
		],
		[
			[3517, 2132, 1465],
			[2701, 1637, 1125],
			[1933, 1172, 805],
			[1501, 910, 625]
		],
		[
			[3669, 2223, 1528],
			[2857, 1732, 1190],
			[2085, 1263, 868],
			[1581, 958, 658]
		],
		[
			[3909, 2369, 1628],
			[3035, 1839, 1264],
			[2181, 1322, 908],
			[1677, 1016, 698]
		],
		[
			[4158, 2520, 1732],
			[3289, 1994, 1370],
			[2358, 1429, 982],
			[1782, 1080, 742]
		],
		[
			[4417, 2677, 1840],
			[3486, 2113, 1452],
			[2473, 1499, 1030],
			[1897, 1150, 790]
		],
		[
			[4686, 2840, 1952],
			[3693, 2238, 1538],
			[2670, 1618, 1112],
			[2022, 1226, 842]
		],
		[
			[4965, 3009, 2068],
			[3909, 2369, 1628],
			[2805, 1700, 1168],
			[2157, 1307, 898]
		],
		[
			[5253, 3183, 2188],
			[4134, 2506, 1722],
			[2949, 1787, 1228],
			[2301, 1394, 958]
		],
		[
			[5529, 3351, 2303],
			[4343, 2632, 1809],
			[3081, 1867, 1283],
			[2361, 1431, 983]
		],
		[
			[5836, 3537, 2431],
			[4588, 2780, 1911],
			[3244, 1966, 1351],
			[2524, 1530, 1051]
		],
		[
			[6153, 3729, 2563],
			[4775, 2894, 1989],
			[3417, 2071, 1423],
			[2625, 1591, 1093]
		],
		[
			[6479, 3927, 2699],
			[5039, 3054, 2099],
			[3599, 2181, 1499],
			[2735, 1658, 1139]
		],
		[
			[6743, 4087, 2809],
			[5313, 3220, 2213],
			[3791, 2298, 1579],
			[2927, 1774, 1219]
		],
		[
			[7089, 4296, 2953],
			[5596, 3391, 2331],
			[3993, 2420, 1663],
			[3057, 1852, 1273]
		],
	];

	/**
	 * $qr_ec_params[
	 *   4 * (version - 1) + (0 for L, 1 for M, 2 for Q, 3 for H)
	 * ] = [
	 *   total number of data codewords,
	 *   number of error correction codewords per block,
	 *   number of blocks in first group,
	 *   number of data codewords per block in first group,
	 *   number of blocks in second group,
	 *   number of data codewords per block in second group
	 * );
	 */
	protected const EC_PARAMS = [
		[19, 7, 1, 19, 0, 0],
		[16, 10, 1, 16, 0, 0],
		[13, 13, 1, 13, 0, 0],
		[9, 17, 1, 9, 0, 0],
		[34, 10, 1, 34, 0, 0],
		[28, 16, 1, 28, 0, 0],
		[22, 22, 1, 22, 0, 0],
		[16, 28, 1, 16, 0, 0],
		[55, 15, 1, 55, 0, 0],
		[44, 26, 1, 44, 0, 0],
		[34, 18, 2, 17, 0, 0],
		[26, 22, 2, 13, 0, 0],
		[80, 20, 1, 80, 0, 0],
		[64, 18, 2, 32, 0, 0],
		[48, 26, 2, 24, 0, 0],
		[36, 16, 4, 9, 0, 0],
		[108, 26, 1, 108, 0, 0],
		[86, 24, 2, 43, 0, 0],
		[62, 18, 2, 15, 2, 16],
		[46, 22, 2, 11, 2, 12],
		[136, 18, 2, 68, 0, 0],
		[108, 16, 4, 27, 0, 0],
		[76, 24, 4, 19, 0, 0],
		[60, 28, 4, 15, 0, 0],
		[156, 20, 2, 78, 0, 0],
		[124, 18, 4, 31, 0, 0],
		[88, 18, 2, 14, 4, 15],
		[66, 26, 4, 13, 1, 14],
		[194, 24, 2, 97, 0, 0],
		[154, 22, 2, 38, 2, 39],
		[110, 22, 4, 18, 2, 19],
		[86, 26, 4, 14, 2, 15],
		[232, 30, 2, 116, 0, 0],
		[182, 22, 3, 36, 2, 37],
		[132, 20, 4, 16, 4, 17],
		[100, 24, 4, 12, 4, 13],
		[274, 18, 2, 68, 2, 69],
		[216, 26, 4, 43, 1, 44],
		[154, 24, 6, 19, 2, 20],
		[122, 28, 6, 15, 2, 16],
		[324, 20, 4, 81, 0, 0],
		[254, 30, 1, 50, 4, 51],
		[180, 28, 4, 22, 4, 23],
		[140, 24, 3, 12, 8, 13],
		[370, 24, 2, 92, 2, 93],
		[290, 22, 6, 36, 2, 37],
		[206, 26, 4, 20, 6, 21],
		[158, 28, 7, 14, 4, 15],
		[428, 26, 4, 107, 0, 0],
		[334, 22, 8, 37, 1, 38],
		[244, 24, 8, 20, 4, 21],
		[180, 22, 12, 11, 4, 12],
		[461, 30, 3, 115, 1, 116],
		[365, 24, 4, 40, 5, 41],
		[261, 20, 11, 16, 5, 17],
		[197, 24, 11, 12, 5, 13],
		[523, 22, 5, 87, 1, 88],
		[415, 24, 5, 41, 5, 42],
		[295, 30, 5, 24, 7, 25],
		[223, 24, 11, 12, 7, 13],
		[589, 24, 5, 98, 1, 99],
		[453, 28, 7, 45, 3, 46],
		[325, 24, 15, 19, 2, 20],
		[253, 30, 3, 15, 13, 16],
		[647, 28, 1, 107, 5, 108],
		[507, 28, 10, 46, 1, 47],
		[367, 28, 1, 22, 15, 23],
		[283, 28, 2, 14, 17, 15],
		[721, 30, 5, 120, 1, 121],
		[563, 26, 9, 43, 4, 44],
		[397, 28, 17, 22, 1, 23],
		[313, 28, 2, 14, 19, 15],
		[795, 28, 3, 113, 4, 114],
		[627, 26, 3, 44, 11, 45],
		[445, 26, 17, 21, 4, 22],
		[341, 26, 9, 13, 16, 14],
		[861, 28, 3, 107, 5, 108],
		[669, 26, 3, 41, 13, 42],
		[485, 30, 15, 24, 5, 25],
		[385, 28, 15, 15, 10, 16],
		[932, 28, 4, 116, 4, 117],
		[714, 26, 17, 42, 0, 0],
		[512, 28, 17, 22, 6, 23],
		[406, 30, 19, 16, 6, 17],
		[1006, 28, 2, 111, 7, 112],
		[782, 28, 17, 46, 0, 0],
		[568, 30, 7, 24, 16, 25],
		[442, 24, 34, 13, 0, 0],
		[1094, 30, 4, 121, 5, 122],
		[860, 28, 4, 47, 14, 48],
		[614, 30, 11, 24, 14, 25],
		[464, 30, 16, 15, 14, 16],
		[1174, 30, 6, 117, 4, 118],
		[914, 28, 6, 45, 14, 46],
		[664, 30, 11, 24, 16, 25],
		[514, 30, 30, 16, 2, 17],
		[1276, 26, 8, 106, 4, 107],
		[1000, 28, 8, 47, 13, 48],
		[718, 30, 7, 24, 22, 25],
		[538, 30, 22, 15, 13, 16],
		[1370, 28, 10, 114, 2, 115],
		[1062, 28, 19, 46, 4, 47],
		[754, 28, 28, 22, 6, 23],
		[596, 30, 33, 16, 4, 17],
		[1468, 30, 8, 122, 4, 123],
		[1128, 28, 22, 45, 3, 46],
		[808, 30, 8, 23, 26, 24],
		[628, 30, 12, 15, 28, 16],
		[1531, 30, 3, 117, 10, 118],
		[1193, 28, 3, 45, 23, 46],
		[871, 30, 4, 24, 31, 25],
		[661, 30, 11, 15, 31, 16],
		[1631, 30, 7, 116, 7, 117],
		[1267, 28, 21, 45, 7, 46],
		[911, 30, 1, 23, 37, 24],
		[701, 30, 19, 15, 26, 16],
		[1735, 30, 5, 115, 10, 116],
		[1373, 28, 19, 47, 10, 48],
		[985, 30, 15, 24, 25, 25],
		[745, 30, 23, 15, 25, 16],
		[1843, 30, 13, 115, 3, 116],
		[1455, 28, 2, 46, 29, 47],
		[1033, 30, 42, 24, 1, 25],
		[793, 30, 23, 15, 28, 16],
		[1955, 30, 17, 115, 0, 0],
		[1541, 28, 10, 46, 23, 47],
		[1115, 30, 10, 24, 35, 25],
		[845, 30, 19, 15, 35, 16],
		[2071, 30, 17, 115, 1, 116],
		[1631, 28, 14, 46, 21, 47],
		[1171, 30, 29, 24, 19, 25],
		[901, 30, 11, 15, 46, 16],
		[2191, 30, 13, 115, 6, 116],
		[1725, 28, 14, 46, 23, 47],
		[1231, 30, 44, 24, 7, 25],
		[961, 30, 59, 16, 1, 17],
		[2306, 30, 12, 121, 7, 122],
		[1812, 28, 12, 47, 26, 48],
		[1286, 30, 39, 24, 14, 25],
		[986, 30, 22, 15, 41, 16],
		[2434, 30, 6, 121, 14, 122],
		[1914, 28, 6, 47, 34, 48],
		[1354, 30, 46, 24, 10, 25],
		[1054, 30, 2, 15, 64, 16],
		[2566, 30, 17, 122, 4, 123],
		[1992, 28, 29, 46, 14, 47],
		[1426, 30, 49, 24, 10, 25],
		[1096, 30, 24, 15, 46, 16],
		[2702, 30, 4, 122, 18, 123],
		[2102, 28, 13, 46, 32, 47],
		[1502, 30, 48, 24, 14, 25],
		[1142, 30, 42, 15, 32, 16],
		[2812, 30, 20, 117, 4, 118],
		[2216, 28, 40, 47, 7, 48],
		[1582, 30, 43, 24, 22, 25],
		[1222, 30, 10, 15, 67, 16],
		[2956, 30, 19, 118, 6, 119],
		[2334, 28, 18, 47, 31, 48],
		[1666, 30, 34, 24, 34, 25],
		[1276, 30, 20, 15, 61, 16],
	];

	protected const EC_POLYNOMIALS = [
		7  => [0, 87, 229, 146, 149, 238, 102, 21],
		10 => [0, 251, 67, 46, 61, 118, 70, 64, 94, 32, 45],
		13 => [0, 74, 152, 176, 100, 86, 100, 106, 104, 130, 218, 206, 140, 78],
		15 => [0, 8, 183, 61, 91, 202, 37, 51, 58, 58, 237, 140, 124, 5, 99, 105],
		16 => [0, 120, 104, 107, 109, 102, 161, 76, 3, 91, 191, 147, 169, 182, 194, 225, 120],
		17 => [0, 43, 139, 206, 78, 43, 239, 123, 206, 214, 147, 24, 99, 150, 39, 243, 163, 136],
		18 => [0, 215, 234, 158, 94, 184, 97, 118, 170, 79, 187, 152, 148, 252, 179, 5, 98, 96, 153],
		20 => [0, 17, 60, 79, 50, 61, 163, 26, 187, 202, 180, 221, 225, 83, 239, 156, 164, 212, 212, 188, 190],
		22 => [0, 210, 171, 247, 242, 93, 230, 14, 109, 221, 53, 200, 74, 8, 172, 98, 80, 219, 134, 160, 105, 165, 231],
		24 => [0, 229, 121, 135, 48, 211, 117, 251, 126, 159, 180, 169, 152, 192, 226, 228, 218, 111, 0, 117, 232, 87, 96, 227, 21],
		26 => [0, 173, 125, 158, 2, 103, 182, 118, 17, 145, 201, 111, 28, 165, 53, 161, 21, 245, 142, 13, 102, 48, 227, 153, 145, 218, 70],
		28 => [0, 168, 223, 200, 104, 224, 234, 108, 180, 110, 190, 195, 147, 205, 27, 232, 201, 21, 43, 245, 87, 42, 195, 212, 119, 242, 37, 9, 123],
		30 => [0, 41, 173, 145, 152, 216, 31, 179, 182, 50, 48, 110, 86, 239, 96, 222, 125, 42, 173, 226, 193, 224, 130, 156, 37, 251, 216, 238, 40, 192, 180],
	];

	protected const LOG = [0, 0, 1, 25, 2, 50, 26, 198, 3, 223, 51, 238, 27, 104, 199, 75, 4, 100, 224, 14, 52, 141, 239, 129, 28, 193, 105, 248, 200, 8, 76, 113, 5, 138, 101, 47, 225, 36, 15, 33, 53, 147, 142, 218, 240, 18, 130, 69, 29, 181, 194, 125, 106, 39, 249, 185, 201, 154, 9, 120, 77, 228, 114, 166, 6, 191, 139, 98, 102, 221, 48, 253, 226, 152, 37, 179, 16, 145, 34, 136, 54, 208, 148, 206, 143, 150, 219, 189, 241, 210, 19, 92, 131, 56, 70, 64, 30, 66, 182, 163, 195, 72, 126, 110, 107, 58, 40, 84, 250, 133, 186, 61, 202, 94, 155, 159, 10, 21, 121, 43, 78, 212, 229, 172, 115, 243, 167, 87, 7, 112, 192, 247, 140, 128, 99, 13, 103, 74, 222, 237, 49, 197, 254, 24, 227, 165, 153, 119, 38, 184, 180, 124, 17, 68, 146, 217, 35, 32, 137, 46, 55, 63, 209, 91, 149, 188, 207, 205, 144, 135, 151, 178, 220, 252, 190, 97, 242, 86, 211, 171, 20, 42, 93, 158, 132, 60, 57, 83, 71, 109, 65, 162, 31, 45, 67, 216, 183, 123, 164, 118, 196, 23, 73, 236, 127, 12, 111, 246, 108, 161, 59, 82, 41, 157, 85, 170, 251, 96, 134, 177, 187, 204, 62, 90, 203, 89, 95, 176, 156, 169, 160, 81, 11, 245, 22, 235, 122, 117, 44, 215, 79, 174, 213, 233, 230, 231, 173, 232, 116, 214, 244, 234, 168, 80, 88, 175];

	protected const EXP = [1, 2, 4, 8, 16, 32, 64, 128, 29, 58, 116, 232, 205, 135, 19, 38, 76, 152, 45, 90, 180, 117, 234, 201, 143, 3, 6, 12, 24, 48, 96, 192, 157, 39, 78, 156, 37, 74, 148, 53, 106, 212, 181, 119, 238, 193, 159, 35, 70, 140, 5, 10, 20, 40, 80, 160, 93, 186, 105, 210, 185, 111, 222, 161, 95, 190, 97, 194, 153, 47, 94, 188, 101, 202, 137, 15, 30, 60, 120, 240, 253, 231, 211, 187, 107, 214, 177, 127, 254, 225, 223, 163, 91, 182, 113, 226, 217, 175, 67, 134, 17, 34, 68, 136, 13, 26, 52, 104, 208, 189, 103, 206, 129, 31, 62, 124, 248, 237, 199, 147, 59, 118, 236, 197, 151, 51, 102, 204, 133, 23, 46, 92, 184, 109, 218, 169, 79, 158, 33, 66, 132, 21, 42, 84, 168, 77, 154, 41, 82, 164, 85, 170, 73, 146, 57, 114, 228, 213, 183, 115, 230, 209, 191, 99, 198, 145, 63, 126, 252, 229, 215, 179, 123, 246, 241, 255, 227, 219, 171, 75, 150, 49, 98, 196, 149, 55, 110, 220, 165, 87, 174, 65, 130, 25, 50, 100, 200, 141, 7, 14, 28, 56, 112, 224, 221, 167, 83, 166, 81, 162, 89, 178, 121, 242, 249, 239, 195, 155, 43, 86, 172, 69, 138, 9, 18, 36, 72, 144, 61, 122, 244, 245, 247, 243, 251, 235, 203, 139, 11, 22, 44, 88, 176, 125, 250, 233, 207, 131, 27, 54, 108, 216, 173, 71, 142, 1];

	protected const REMAINER_BITS = [0, 7, 7, 7, 7, 7, 0, 0, 0, 0, 0, 0, 0, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 0, 0, 0, 0, 0, 0];

	protected const ALIGNMENT_PATTERNS = [
		[6, 18],
		[6, 22],
		[6, 26],
		[6, 30],
		[6, 34],
		[6, 22, 38],
		[6, 24, 42],
		[6, 26, 46],
		[6, 28, 50],
		[6, 30, 54],
		[6, 32, 58],
		[6, 34, 62],
		[6, 26, 46, 66],
		[6, 26, 48, 70],
		[6, 26, 50, 74],
		[6, 30, 54, 78],
		[6, 30, 56, 82],
		[6, 30, 58, 86],
		[6, 34, 62, 90],
		[6, 28, 50, 72,  94],
		[6, 26, 50, 74,  98],
		[6, 30, 54, 78, 102],
		[6, 28, 54, 80, 106],
		[6, 32, 58, 84, 110],
		[6, 30, 58, 86, 114],
		[6, 34, 62, 90, 118],
		[6, 26, 50, 74,  98, 122],
		[6, 30, 54, 78, 102, 126],
		[6, 26, 52, 78, 104, 130],
		[6, 30, 56, 82, 108, 134],
		[6, 34, 60, 86, 112, 138],
		[6, 30, 58, 86, 114, 142],
		[6, 34, 62, 90, 118, 146],
		[6, 30, 54, 78, 102, 126, 150],
		[6, 24, 50, 76, 102, 128, 154],
		[6, 28, 54, 80, 106, 132, 158],
		[6, 32, 58, 84, 110, 136, 162],
		[6, 26, 54, 82, 110, 138, 166],
		[6, 30, 58, 86, 114, 142, 170],
	];

	/**
	 * format info string = $qr_format_info[
	 *   (0 for L, 8 for M, 16 for Q, 24 for H) + mask
	 *];
	 */
	protected const FORMAT_INFO = [
		[1, 1, 1, 0, 1, 1, 1, 1, 1, 0, 0, 0, 1, 0, 0],
		[1, 1, 1, 0, 0, 1, 0, 1, 1, 1, 1, 0, 0, 1, 1],
		[1, 1, 1, 1, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0],
		[1, 1, 1, 1, 0, 0, 0, 1, 0, 0, 1, 1, 1, 0, 1],
		[1, 1, 0, 0, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1],
		[1, 1, 0, 0, 0, 1, 1, 0, 0, 0, 1, 1, 0, 0, 0],
		[1, 1, 0, 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1],
		[1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 1, 0, 1, 1, 0],
		[1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0],
		[1, 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 1],
		[1, 0, 1, 1, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0],
		[1, 0, 1, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1],
		[1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 1, 0, 0, 1],
		[1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 1, 1, 0],
		[1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 1],
		[1, 0, 0, 1, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0],
		[0, 1, 1, 0, 1, 0, 1, 0, 1, 0, 1, 1, 1, 1, 1],
		[0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0, 0, 0],
		[0, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 1],
		[0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0],
		[0, 1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, 0],
		[0, 1, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1],
		[0, 1, 0, 1, 1, 1, 0, 1, 1, 0, 1, 1, 0, 1, 0],
		[0, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 0, 1],
		[0, 0, 1, 0, 1, 1, 0, 1, 0, 0, 0, 1, 0, 0, 1],
		[0, 0, 1, 0, 0, 1, 1, 1, 0, 1, 1, 1, 1, 1, 0],
		[0, 0, 1, 1, 1, 0, 0, 1, 1, 1, 0, 0, 1, 1, 1],
		[0, 0, 1, 1, 0, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0],
		[0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 0, 0, 1, 0],
		[0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0, 1, 0, 1],
		[0, 0, 0, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 0, 0],
		[0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1]
	];

	/**
	 * version info string = $qr_version_info[ (version - 7) ]
	 */
	protected const VERSION_INFO = [
		[0, 0, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0],
		[0, 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0],
		[0, 0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1],
		[0, 0, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 0, 1, 0, 0, 1, 1],
		[0, 0, 1, 0, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 0],
		[0, 0, 1, 1, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 0, 0, 1, 0],
		[0, 0, 1, 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 0, 1, 1, 1],
		[0, 0, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1],
		[0, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0, 0],
		[0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0],
		[0, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1],
		[0, 1, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 1],
		[0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 0],
		[0, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 0, 1, 0, 0, 1, 1, 0],
		[0, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 1, 1],
		[0, 1, 0, 1, 1, 0, 1, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, 1],
		[0, 1, 0, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 0, 0],
		[0, 1, 1, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 0, 0, 1, 0, 0],
		[0, 1, 1, 0, 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 1],
		[0, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1, 1],
		[0, 1, 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 0, 1, 1, 1, 0],
		[0, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0],
		[0, 1, 1, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 1],
		[0, 1, 1, 1, 1, 0, 1, 1, 0, 1, 0, 1, 1, 1, 0, 1, 0, 1],
		[0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0, 0, 0],
		[1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 1, 1, 0, 1, 0, 1, 0, 1],
		[1, 0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0, 0],
		[1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1, 0],
		[1, 0, 0, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0, 1, 1, 1, 1, 1],
		[1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 1, 1],
		[1, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0],
		[1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 0, 0],
		[1, 0, 0, 1, 1, 1, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1],
		[1, 0, 1, 0, 0, 0, 1, 1, 0, 0, 0, 1, 1, 0, 1, 0, 0, 1]
	];
}
