<?php

namespace Kirby\Panel\Response;

use Kirby\Http\Response;

/**
 * The Dialog response class handles Fiber
 * requests to render the JSON object for
 * Panel dialogs and creates the routes
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class DialogResponse extends JsonResponse
{
	protected static string $key = 'dialog';

	public static function from(mixed $data): Response
	{
		if ($data === true) {
			return new static([], 200);
		}

		return parent::from($data);
	}
}
