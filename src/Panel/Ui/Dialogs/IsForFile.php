<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Find;

/**
 * Trait for statically initializing dialogs
 * that are based on a \Kirby\Cms\File object
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
trait IsForFile
{
	public static function for(string $path, string $filename): static
	{
		$file = Find::file($path, $filename);
		return new static($file);
	}
}
