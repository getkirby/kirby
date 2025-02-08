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
trait IsForLanguageVariable
{
	public static function for(
		string $code,
		string|null $key = null
	): static {
		$language = Find::language($code);
		$variable = $language->variable($key, true);
		return new static($variable);
	}
}
