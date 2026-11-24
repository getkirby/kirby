<?php

namespace Kirby\Cms;

use Kirby\Http\Uri;

/**
 * Custom response object with an optimized
 * redirect method to build correct Urls
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Response extends \Kirby\Http\Response
{
	/**
	 * Adjusted redirect creation which
	 * parses locations with the Url::to method
	 * first.
	 *
	 * @param $inherit Keep the query and params of the current request (since 5.7.0)
	 */
	public static function redirect(
		string $location = '/',
		int $code = 302,
		bool $inherit = false
	): static {
		$location = Url::to($location);

		if ($inherit === true) {
			$current  = App::instance()->request()->url();
			$location = (new Uri($location))->inherit($current)->toString();
		}

		return parent::redirect($location, $code);
	}
}
