<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Find;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
trait IsForUser
{
	public static function for(string $id): static
	{
		$user = Find::user($id);
		return new static($user);
	}
}
