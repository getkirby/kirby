<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
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
		$data = static::responseData($data);

		// always inject the response code
		$data['code']   ??= 200;
		$data['path']     = $options['path'] ?? null;
		$data['query']    = App::instance()->request()->query()->toArray();
		$data['referrer'] = Panel::referrer();

		return Panel::json([static::$key => $data], $data['code']);
	}

	public static function responseData(mixed $data): array
	{
		// handle redirects
		if ($data instanceof Redirect) {
			return [
				'redirect' => $data->location(),
			];
		}

		// handle Kirby exceptions
		if ($data instanceof Exception) {
			return static::error($data->getMessage(), $data->getHttpCode());
		}

		// handle exceptions
		if ($data instanceof Throwable) {
			return static::error($data->getMessage(), 500);
		}

		// only expect arrays from here on
		if (is_array($data) === false) {
			return static::error('Invalid response', 500);
		}

		if ($data === []) {
			return static::error('The response is empty', 404);
		}

		return $data;
	}
}
