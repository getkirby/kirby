<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Throwable;

/**
 * The View response class handles Panel
 * requests to render either a JSON object
 * or a full HTML document for Panel views
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class View
{
	/**
	 * Renders the error view with provided message
	 */
	public static function error(string $message, int $code = 404)
	{
		$access = App::instance()->panel()->access()->area('*');

		return [
			'code'      => $code,
			'component' => 'k-error-view',
			'error'     => $message,
			'props'     => [
				'error'  => $message,
				'layout' => $access ? 'inside' : 'outside'
			],
			'title' => 'Error'
		];
	}

	/**
	 * Renders the main panel view either as
	 * JSON response or full HTML document based
	 * on the request header or query params
	 */
	public static function response($data, array $options = []): Response
	{
		// handle redirects
		if ($data instanceof Redirect) {
			return Response::redirect($data->location(), $data->code());
		}

		// handle Kirby exceptions
		if ($data instanceof Exception) {
			$data = static::error($data->getMessage(), $data->getHttpCode());

		// handle regular exceptions
		} elseif ($data instanceof Throwable) {
			$data = static::error($data->getMessage(), 500);

		// only expect arrays from here on
		} elseif (is_array($data) === false) {
			$data = static::error('Invalid Panel response', 500);
		}

		// get all data for the request
		$state = new State(
			view: $data,
			area: $options['area'] ?? [],
			areas: $options['areas'] ?? []
		);

		// if requested, send State data as JSON
		if (Panel::isStateRequest() === true) {
			// filter data, if only or globals headers or
			// query parameters are set
			$state = $state->toArray(globals: false);

			return Panel::json($state, $state['view']['code'] ?? 200);
		}

		// render the full HTML document
		return Document::response($state);
	}
}
