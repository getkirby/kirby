<?php

namespace Kirby\Panel;

use Kirby\Http\Response;

/**
 * The Search response class handles Fiber
 * requests to render the JSON object for
 * search queries
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Search extends Json
{
	protected static string $key = '$search';

	public static function response($data, array $options = []): Response
	{
		if (is_array($data) === true) {
			$data = [
				'results' => $data
			];
		}

		return parent::response($data, $options);
	}
}
