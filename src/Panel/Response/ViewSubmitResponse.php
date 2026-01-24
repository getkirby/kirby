<?php

namespace Kirby\Panel\Response;

use Kirby\Http\Response;

/**
 * The View submit response class handles Panel
 * requests to submit view data and returns a
 * JSON object for the view only
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ViewSubmitResponse extends JsonResponse
{
	protected static string $key = 'view';

	public static function from(mixed $data): Response
	{
		if ($data === true) {
			return new static([], 200);
		}

		return parent::from($data);
	}
}
