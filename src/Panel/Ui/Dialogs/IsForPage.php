<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Find;

/**
 * Trait for statically initializing dialogs
 * that are based on a \Kirby\Cms\Page object
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
trait IsForPage
{
	public static function for(string $id): static
	{
		$page = Find::page($id);
		return new static($page);
	}
}
