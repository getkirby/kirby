<?php

namespace Kirby\Image;

/**
 * Interface for stripping exif data from images
 * @since 4.7.0
 *
 * @package   Kirby Image
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
interface StripExif
{
	public static function stripExif(string $file): void;
}
