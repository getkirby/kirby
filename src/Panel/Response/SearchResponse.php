<?php

namespace Kirby\Panel\Response;

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
class SearchResponse extends JsonResponse
{
	protected static string $key = 'search';

	public static function from(mixed $data): Response
	{
		if (
			is_array($data) === true &&
			array_key_exists('results', $data) === false
		) {
			$data = [
				'results'    => $data,
				'pagination' => [
					'page'      => 1,
					'limit'     => $total = count($data),
					'total'     => $total
				]
			];
		}

		return parent::from($data);
	}
}
