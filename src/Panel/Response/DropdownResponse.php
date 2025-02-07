<?php

namespace Kirby\Panel\Response;

use Kirby\Http\Response;

/**
 * The Dropdown response class handles Fiber
 * requests to render the JSON object for
 * dropdown menus
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class DropdownResponse extends JsonResponse
{
	protected static string $key = 'dropdown';

	public static function from(mixed $data): Response
	{
		if (
			is_array($data) === true &&
			array_key_exists('options', $data) === false
		) {
			$data = [
				'options' => array_values($data),
			];
		}

		return parent::from($data);
	}
}
