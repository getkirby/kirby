<?php

namespace Kirby\Cms;

/**
 * Custom response object with an optimized
 * redirect method to build correct Urls
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Response extends \Kirby\Http\Response
{
	/**
	 * Adjusted redirect creation which
	 * parses locations with the Url::to method
	 * first.
	 */
	public static function redirect(
		string $location = '/',
		int $code = 302
	): static {
		return parent::redirect(Url::to($location), $code);
	}
}
