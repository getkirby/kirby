<?php

namespace Kirby\Panel\Lab;

use Kirby\Template\Snippet as BaseSnippet;

/**
 * Custom snippet class for lab examples
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @internal
 * @codeCoverageIgnore
 */
class Snippet extends BaseSnippet
{
	public static function root(): string
	{
		return __DIR__ . '/snippets';
	}
}
