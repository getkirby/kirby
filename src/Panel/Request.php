<?php

namespace Kirby\Panel;

use Kirby\Http\Response;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Request
{
	/**
	 * Renders request responses
	 */
	public static function response($data, array $options = []): Response
	{
		$data = Json::responseData($data);
		return Panel::json($data, $data['code'] ?? 200);
	}
}
