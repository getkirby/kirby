<?php

namespace Kirby\Image;

/**
 * Interface for stripping metadata
 * with keeping ICC profiles and orientation
 * @since 4.7.0
 *
 * @package   Kirby Image
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
interface Strippable
{
	public static function strip(string $file): void;
}
