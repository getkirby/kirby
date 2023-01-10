<?php

namespace Kirby\Panel;

use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Throwable;

/**
 * The Json abstract response class provides
 * common framework for Fiber requests
 * to render the JSON object for, e.g.
 * Panel dialogs, dropdowns etc.
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Json
{
	protected static string $key = '$response';

	/**
	 * Renders the error response with the provided message
	 */
	public static function error(string $message, int $code = 404): array
	{
		return [
			'code'  => $code,
			'error' => $message
		];
	}

	/**
	 * Prepares the JSON response for the Panel
	 */
	public static function response($data, array $options = []): Response
	{
		// handle redirects
		if ($data instanceof Redirect) {
			$data = [
				'redirect' => $data->location(),
				'code'     => $data->code()
			];

		// handle Kirby exceptions
		} elseif ($data instanceof Exception) {
			$data = static::error($data->getMessage(), $data->getHttpCode());

		// handle exceptions
		} elseif ($data instanceof Throwable) {
			$data = static::error($data->getMessage(), 500);

		// only expect arrays from here on
		} elseif (is_array($data) === false) {
			$data = static::error('Invalid response', 500);
		}

		if (empty($data) === true) {
			$data = static::error('The response is empty', 404);
		}

		// always inject the response code
		$data['code']   ??= 200;
		$data['path']     = $options['path'] ?? null;
		$data['referrer'] = Panel::referrer();

		return Panel::json([static::$key => $data], $data['code']);
	}
}
