<?php

namespace Kirby\Panel\Response;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Http\Response;
use Kirby\Panel\Access;
use Kirby\Panel\Fiber;
use Kirby\Panel\Redirect;

/**
 * The View response class handles Fiber
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
class ViewResponse extends JsonResponse
{
	protected Fiber $fiber;
	protected static string $key = 'view';

	public function __construct(
		protected array $view = [],
		protected int $code = 200,
		protected bool $pretty = false,
	) {
		parent::__construct(
			data: ['view' => $view],
			code: $code,
			pretty: $pretty
		);
	}

	/**
	 * Returns the data as JSON
	 * Request responses are not wrapped in a custom namespace
	 */
	public function body(): string
	{
		return Json::encode($this->data(), $this->pretty());
	}

	/**
	 * Returns the full fiber data object
	 */
	public function data(): array
	{
		return $this->fiber()->toArray(globals: false);
	}

	/**
	 * Renders the error view with provided message
	 */
	public static function error(string $message, int $code = 404): static
	{
		$access = Access::has(App::instance()->user());

		return new static(
			view: [
				'component' => 'k-error-view',
				'error'     => $message,
				'props'     => [
					'error'  => $message,
					'layout' => $access ? 'inside' : 'outside'
				],
				'title' => 'Error'
			],
			code: $code
		);
	}

	/**
	 * Provides access to fiber object
	 */
	public function fiber(): Fiber
	{
		return new Fiber(
			area: $this->area,
			areas: $this->areas,
			view: $this->view,
		);
	}

	/**
	 * Renders the main panel view either as
	 * JSON response or full HTML document based
	 * on the request header or query params
	 */
	public static function from(mixed $data): Response
	{
		// handle redirects
		if ($data instanceof Redirect) {
			return Response::redirect($data->location(), $data->code());
		}

		return parent::from($data);
	}

	/**
	 * Returns the view object
	 */
	public function view(): array
	{
		return $this->view;
	}
}
