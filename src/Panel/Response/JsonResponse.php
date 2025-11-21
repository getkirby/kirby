<?php

namespace Kirby\Panel\Response;

use Kirby\Data\Json;
use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Kirby\Panel\Area;
use Kirby\Panel\Redirect;
use Kirby\Panel\Ui\Component;
use Throwable;

/**
 * The Json abstract response class provides
 * common framework for Panel requests
 * to render the JSON object for, e.g.
 * Panel dialogs, dropdowns etc.
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class JsonResponse extends Response
{
	protected static string $key = 'response';

	// context properties can be set with the context method
	protected Area|null $area = null;
	protected string|null $path = null;
	protected array $query = [];
	protected string $referrer = '/';

	public function __construct(
		protected array $data = [],
		protected int $code = 200,
		protected bool $pretty = false
	) {
	}

	/**
	 * Returns the data as JSON
	 */
	public function body(): string
	{
		return Json::encode([static::$key => $this->data()], $this->pretty());
	}

	/**
	 * Setter for context properties
	 */
	public function context(
		Area|null $area = null,
		string|null $path = null,
		array $query = [],
		string $referrer = '/'
	): static {
		$this->area     = $area;
		$this->path     = $path;
		$this->query    = $query;
		$this->referrer = $referrer;

		return $this;
	}

	/**
	 * Returns the full data array
	 */
	public function data(): array
	{
		// add default data keys
		return [
			'code'     => $this->code(),
			'path'     => $this->path(),
			'query'    => $this->query(),
			'referrer' => $this->referrer(),
			...$this->data
		];
	}

	/**
	 * Renders the error response with the provided message
	 */
	public static function error(
		string $message,
		int $code = 404,
		array $details = []
	): static {
		$data = ['error' => $message];

		if ($details !== []) {
			$data['details'] = $details;
		}

		return new static($data, $code);
	}

	/**
	 * Creates a response object from mixed input
	 */
	public static function from(mixed $data): Response
	{
		// pass through HTTP response objects
		if ($data instanceof Response) {
			return $data;
		}

		// handle redirects
		if ($data instanceof Redirect) {
			return new static([
				'redirect' => $data->location(),
			]);
		}

		// handle UI components
		if ($data instanceof Component) {
			return static::from($data->render());
		}

		// interpret strings as errors
		if (is_string($data) === true) {
			return static::error($data, 500);
		}

		// interpret missing/empty data as not found
		if ($data === null || $data === false) {
			return static::error('The data could not be found', 404);
		}

		// handle Kirby exceptions
		if ($data instanceof Exception) {
			return static::error(
				message: $data->getMessage(),
				code:    $data->getHttpCode(),
				details: $data->getDetails()
			);
		}

		// handle Throwables
		if ($data instanceof Throwable) {
			return static::error($data->getMessage(), 500);
		}

		// only expect arrays from here on
		if (is_array($data) === false) {
			return static::error('Invalid response', 500);
		}

		// react to empty responses
		if ($data === []) {
			return static::error('The response is empty', 404);
		}

		return new static($data);
	}

	public function headers(): array
	{
		return [
			'X-Panel'       => 'true',
			'Cache-Control' => 'no-store, private'
		];
	}

	/**
	 * Returns the response key
	 */
	public function key(): string
	{
		return static::$key;
	}

	/**
	 * Returns the request path
	 */
	public function path(): string|null
	{
		return $this->path;
	}

	/**
	 * Should the JSON in the body be pretty-printed?
	 */
	public function pretty(): bool
	{
		return $this->query()['_pretty'] ?? $this->pretty;
	}

	/**
	 * Returns the request query as array
	 */
	public function query(): array
	{
		return $this->query;
	}

	/**
	 * Returns the set referrer
	 */
	public function referrer(): string
	{
		return $this->referrer;
	}

	public function type(): string
	{
		return 'application/json';
	}
}
