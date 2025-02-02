<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Cms\LanguageVariable;

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
	/**
	 * @psalm-return ($key is null ? \Kirby\Cms\Language : \Kirby\Cms\LanguageVariable)
	 */
	public static function for(
		string $code,
		string|null $key = null
	): Language|LanguageVariable {
		$language = Find::language($code);

		if ($key === null) {
			return new static($language);
		}

		$variable = $language->variable($key, true);
		return new static($variable);
	}
}
