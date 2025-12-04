<?php

namespace Kirby\Panel\Response;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Panel\Redirect;
use Kirby\Panel\State;

/**
 * The View response class handles state
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
	protected State $state;
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
	 * Returns the full state data object
	 */
	public function data(bool $globals = false): array
	{
		$data = $this->state()->toArray(globals: $globals);

		// make sure that the context is added
		// correctly to the view object
		$data['view']['code']     ??= $this->code();
		$data['view']['path']     ??= $this->path();
		$data['view']['query']    ??= $this->query();
		$data['view']['referrer'] ??= $this->referrer();

		return $data;
	}

	/**
	 * Renders the error view with provided message
	 */
	public static function error(
		string $message,
		int $code = 404,
		array $details = []
	): static {
		$kirby  = App::instance();
		$access = $kirby->panel()->access()->area($kirby->user());

		$view = [
			...JsonResponse::error($message, $code, $details)->data(),
			'component' => 'k-error-view',
			'props'     => [
				'error'  => $message,
				'layout' => $access ? 'inside' : 'outside'
			],
			'title' => 'Error'
		];

		ksort($view);

		return new static(
			view: $view,
			code: $code
		);
	}

	/**
	 * Provides access to state object
	 */
	public function state(): State
	{
		return new State(
			area: $this->area,
			view: $this->view(),
		);
	}

	/**
	 * Renders the main panel view either as
	 * JSON response or full HTML document based
	 * on the request header or query params
	 */
	public static function from(mixed $data): Response
	{
		// Create an error view for any route that throws a not found exception.
		// This will make sure that users can navigate to such views and will get a
		// useful response instead of the debugger or the fatal screen.
		if ($data instanceof NotFoundException) {
			return static::error($data->getMessage());
		}

		// handle redirects
		if ($data instanceof Redirect) {
			// if the redirect is a refresh, return a refresh response
			if ($data->refresh() !== false) {
				return Response::refresh($data->location(), $data->code(), $data->refresh());
			}

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
