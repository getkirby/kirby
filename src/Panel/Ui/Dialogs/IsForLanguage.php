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
trait IsForLanguage
{
	public static function for(string $code): static
	{
		$language = Find::language($code);
		return new static($language);
	}
}
