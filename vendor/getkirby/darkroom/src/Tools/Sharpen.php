<?php

declare(strict_types = 1);

namespace Darkroom\Tools;

/**
 * A gaussian sharpen with a sigma of the amount
 * in hundredths, as a 3×3 kernel
 *
 * @internal
 */
final class Sharpen
{
	/**
	 * The kernel, its weights summing to 1
	 *
	 * @return list<list<float>>
	 */
	public static function kernel(int $amount): array
	{
		$sigma  = (float)$amount / 100.0;
		$edge   = exp(-0.5 / ($sigma * $sigma));
		$corner = $edge * $edge;
		$sum    = 1.0 + 4.0 * ($edge + $corner);
		$scale  = 1.0 / ($sum + 1.0);
		$edge   = -$edge * $scale;
		$corner = -$corner * $scale;
		$center = 2.0 * $sum * $scale;

		return [
			[$corner, $edge, $corner],
			[$edge, $center, $edge],
			[$corner, $edge, $corner],
		];
	}
}
