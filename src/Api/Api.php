<?php

namespace Kirby\Api;

use Closure;
use Exception;
use Kirby\Cms\User;
use Kirby\Exception\Exception as ExceptionException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Http\Route;
use Kirby\Http\Router;
use Kirby\Toolkit\Collection as BaseCollection;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Pagination;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The API class is a generic container
 * for API routes, models and collections and is used
 * to run our REST API. You can find our API setup
 * in `kirby/config/api.php`.
 *
 * @package   Kirby Api
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Api
{
	use Properties;

	/**
	 * Authentication callback
	 */
	protected Closure|null $authentication = null;

	/**
	 * Debugging flag
	 */
	protected bool $debug = false;

	/**
	 * Collection definition
	 */
	protected array $collections = [];

	/**
	 * Injected data/dependencies
	 */
	protected array $data = [];

	/**
	 * Model definitions
	 */
	protected array $models = [];

	/**
	 * The current route
	 */
	protected Route|null $route = null;

	/**
	 * The Router instance
	 */
	protected Router|null $router = null;

	/**
	 * Route definition
	 */
	protected array $routes = [];

	/**
	 * Request data
	 * [query, body, files]
	 */
	protected array $requestData = [];

	/**
	 * The applied request method
	 * (GET, POST, PATCH, etc.)
	 */
	protected string|null $requestMethod = null;

	/**
	 * Magic accessor for any given data
	 *
	 * @throws \Kirby\Exception\NotFoundException
	 */
	public function __call(string $method, array $args = [])
	{
		return $this->data($method, ...$args);
	}

	/**
	 * Creates a new API instance
	 */
	public function __construct(array $props)
	{
		$this->setProperties($props);
	}

	/**
	 * Runs the authentication method
	 * if set
	 */
	public function authenticate()
	{
		return $this->authentication()?->call($this) ?? true;
	}

	/**
	 * Returns the authentication callback
	 *
	 * @return \Closure|null
	 */
	public function authentication(): Closure|null
	{
		return $this->authentication;
	}

	/**
	 * Execute an API call for the given path,
	 * request method and optional request data
	 *
	 * @throws \Kirby\Exception\NotFoundException
	 * @throws \Exception
	 */
	public function call(string|null $path = null, string $method = 'GET', array $requestData = [])
	{
		$path = rtrim($path ?? '', '/');

		$this->setRequestMethod($method);
		$this->setRequestData($requestData);

		$this->router = new Router($this->routes());
		$this->route  = $this->router->find($path, $method);
		$auth = $this->route?->attributes()['auth'] ?? true;

		if ($auth !== false) {
			$user = $this->authenticate();

			// set PHP locales based on *user* language
			// so that e.g. strftime() gets formatted correctly
			if ($user instanceof User) {
				$language = $user->language();

				// get the locale from the translation
				$locale = $user->kirby()->translation($language)->locale();

				// provide some variants as fallbacks to be
				// compatible with as many systems as possible
				$locales = [
					$locale . '.UTF-8',
					$locale . '.UTF8',
					$locale . '.ISO8859-1',
					$locale,
					$language,
					setlocale(LC_ALL, 0) // fall back to the previously defined locale
				];

				// set the locales that are relevant for string formatting
				// *don't* set LC_CTYPE to avoid breaking other parts of the system
				setlocale(LC_MONETARY, $locales);
				setlocale(LC_NUMERIC, $locales);
				setlocale(LC_TIME, $locales);
			}
		}

		// don't throw pagination errors if pagination
		// page is out of bounds
		$validate = Pagination::$validate;
		Pagination::$validate = false;

		$output = $this->route?->action()->call(
			$this,
			...$this->route->arguments()
		);

		// restore old pagination validation mode
		Pagination::$validate = $validate;

		if (
			is_object($output) === true &&
			$output instanceof Response === false
		) {
			return $this->resolve($output)->toResponse();
		}

		return $output;
	}

	/**
	 * Setter and getter for an API collection
	 *
	 * @throws \Kirby\Exception\NotFoundException If no collection for `$name` exists
	 * @throws \Exception
	 */
	public function collection(string $name, array|BaseCollection|null $collection = null): Collection
	{
		if (isset($this->collections[$name]) === false) {
			throw new NotFoundException(sprintf('The collection "%s" does not exist', $name));
		}

		return new Collection($this, $collection, $this->collections[$name]);
	}

	/**
	 * Returns the collections definition
	 */
	public function collections(): array
	{
		return $this->collections;
	}

	/**
	 * Returns the injected data array
	 * or certain parts of it by key
	 *
	 * @throws \Kirby\Exception\NotFoundException If no data for `$key` exists
	 */
	public function data(string|null $key = null, ...$args)
	{
		if ($key === null) {
			return $this->data;
		}

		if ($this->hasData($key) === false) {
			throw new NotFoundException(sprintf('Api data for "%s" does not exist', $key));
		}

		// lazy-load data wrapped in Closures
		if ($this->data[$key] instanceof Closure) {
			return $this->data[$key]->call($this, ...$args);
		}

		return $this->data[$key];
	}

	/**
	 * Returns the debugging flag
	 */
	public function debug(): bool
	{
		return $this->debug;
	}

	/**
	 * Checks if injected data exists for the given key
	 */
	public function hasData(string $key): bool
	{
		return isset($this->data[$key]) === true;
	}

	/**
	 * Matches an object with an array item
	 * based on the `type` field
	 *
	 * @param array models or collections
	 * @return string|null key of match
	 */
	protected function match(array $array, $object = null): string|null
	{
		foreach ($array as $definition => $model) {
			if ($object instanceof $model['type']) {
				return $definition;
			}
		}

		return null;
	}

	/**
	 * Returns an API model instance by name
	 *
	 * @throws \Kirby\Exception\NotFoundException If no model for `$name` exists
	 */
	public function model(string|null $name = null, $object = null): Model
	{
		// Try to auto-match object with API models
		$name ??= $this->match($this->models, $object);

		if (isset($this->models[$name]) === false) {
			throw new NotFoundException(sprintf('The model "%s" does not exist', $name ?? 'NULL'));
		}

		return new Model($this, $object, $this->models[$name]);
	}

	/**
	 * Returns all model definitions
	 */
	public function models(): array
	{
		return $this->models;
	}

	/**
	 * Getter for request data
	 * Can either get all the data
	 * or certain parts of it.
	 *
	 * @param string|null $type
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function requestData(
		string|null $type = null,
		string|null $key = null,
		$default = null
	) {
		if ($type === null) {
			return $this->requestData;
		}

		if ($key === null) {
			return $this->requestData[$type] ?? [];
		}

		$data = array_change_key_case($this->requestData($type));
		$key  = strtolower($key);

		return $data[$key] ?? $default;
	}

	/**
	 * Returns the request body if available
	 */
	public function requestBody(string|null $key = null, $default = null)
	{
		return $this->requestData('body', $key, $default);
	}

	/**
	 * Returns the files from the request if available
	 */
	public function requestFiles(string|null $key = null, $default = null)
	{
		return $this->requestData('files', $key, $default);
	}

	/**
	 * Returns all headers from the request if available
	 */
	public function requestHeaders(string|null $key = null, $default = null)
	{
		return $this->requestData('headers', $key, $default);
	}

	/**
	 * Returns the request method
	 */
	public function requestMethod(): string|null
	{
		return $this->requestMethod;
	}

	/**
	 * Returns the request query if available
	 */
	public function requestQuery(string|null $key = null, $default = null)
	{
		return $this->requestData('query', $key, $default);
	}

	/**
	 * Turns a Kirby object into an
	 * API model or collection representation
	 *
	 * @throws \Kirby\Exception\NotFoundException If `$object` cannot be resolved
	 */
	public function resolve($object): Model|Collection
	{
		if (
			$object instanceof Model ||
			$object instanceof Collection
		) {
			return $object;
		}

		if ($model = $this->match($this->models, $object)) {
			return $this->model($model, $object);
		}

		if ($collection = $this->match($this->collections, $object)) {
			return $this->collection($collection, $object);
		}

		throw new NotFoundException(sprintf('The object "%s" cannot be resolved', get_class($object)));
	}

	/**
	 * Returns all defined routes
	 */
	public function routes(): array
	{
		return $this->routes;
	}

	/**
	 * Setter for the authentication callback
	 * @return $this
	 */
	protected function setAuthentication(Closure|null $authentication = null): static
	{
		$this->authentication = $authentication;
		return $this;
	}

	/**
	 * Setter for the collections definition
	 * @return $this
	 */
	protected function setCollections(array|null $collections = null): static
	{
		if ($collections !== null) {
			$this->collections = array_change_key_case($collections);
		}
		return $this;
	}

	/**
	 * Setter for the injected data
	 * @return $this
	 */
	protected function setData(array|null $data = null): static
	{
		$this->data = $data ?? [];
		return $this;
	}

	/**
	 * Setter for the debug flag
	 * @return $this
	 */
	protected function setDebug(bool $debug = false): static
	{
		$this->debug = $debug;
		return $this;
	}

	/**
	 * Setter for the model definitions
	 * @return $this
	 */
	protected function setModels(array|null $models = null): static
	{
		if ($models !== null) {
			$this->models = array_change_key_case($models);
		}

		return $this;
	}

	/**
	 * Setter for the request data
	 * @return $this
	 */
	protected function setRequestData(array|null $requestData = null): static
	{
		$defaults = [
			'query' => [],
			'body'  => [],
			'files' => []
		];

		$this->requestData = array_merge($defaults, (array)$requestData);
		return $this;
	}

	/**
	 * Setter for the request method
	 * @return $this
	 */
	protected function setRequestMethod(string|null $requestMethod = null): static
	{
		$this->requestMethod = $requestMethod ?? 'GET';
		return $this;
	}

	/**
	 * Setter for the route definitions
	 * @return $this
	 */
	protected function setRoutes(array|null $routes = null): static
	{
		$this->routes = $routes ?? [];
		return $this;
	}

	/**
	 * Renders the API call
	 */
	public function render(string $path, string $method = 'GET', array $requestData = [])
	{
		try {
			$result = $this->call($path, $method, $requestData);
		} catch (Throwable $e) {
			$result = $this->responseForException($e);
		}

		$result = match ($result) {
			null    => $this->responseFor404(),
			false   => $this->responseFor400(),
			true    => $this->responseFor200(),
			default => $result
		};

		if (is_array($result) === false) {
			return $result;
		}

		// pretty print json data
		$pretty = (bool)($requestData['query']['pretty'] ?? false) === true;

		if (($result['status'] ?? 'ok') === 'error') {
			$code = $result['code'] ?? 400;

			// sanitize the error code
			if ($code < 400 || $code > 599) {
				$code = 500;
			}

			return Response::json($result, $code, $pretty);
		}

		return Response::json($result, 200, $pretty);
	}

	/**
	 * Returns a 200 - ok
	 * response array.
	 */
	public function responseFor200(): array
	{
		return [
			'status'  => 'ok',
			'message' => 'ok',
			'code'    => 200
		];
	}

	/**
	 * Returns a 400 - bad request
	 * response array.
	 */
	public function responseFor400(): array
	{
		return [
			'status'  => 'error',
			'message' => 'bad request',
			'code'    => 400,
		];
	}

	/**
	 * Returns a 404 - not found
	 * response array.
	 */
	public function responseFor404(): array
	{
		return [
			'status'  => 'error',
			'message' => 'not found',
			'code'    => 404,
		];
	}

	/**
	 * Creates the response array for
	 * an exception. Kirby exceptions will
	 * have more information
	 */
	public function responseForException(Throwable $e): array
	{
		if (isset($this->kirby) === true) {
			$docRoot = $this->kirby->environment()->get('DOCUMENT_ROOT');
		} else {
			$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
		}

		// prepare the result array for all exception types
		$result = [
			'status'    => 'error',
			'message'   => $e->getMessage(),
			'code'      => empty($e->getCode()) === true ? 500 : $e->getCode(),
			'exception' => get_class($e),
			'key'       => null,
			'file'      => F::relativepath($e->getFile(), $docRoot),
			'line'      => $e->getLine(),
			'details'   => [],
			'route'     => $this->route?->pattern()
		];

		// extend the information for Kirby Exceptions
		if ($e instanceof ExceptionException) {
			$result['key']     = $e->getKey();
			$result['details'] = $e->getDetails();
			$result['code']    = $e->getHttpCode();
		}

		// remove critical info from the result set if
		// debug mode is switched off
		if ($this->debug !== true) {
			unset(
				$result['file'],
				$result['exception'],
				$result['line'],
				$result['route']
			);
		}

		return $result;
	}

	/**
	 * Upload helper method
	 *
	 * move_uploaded_file() not working with unit test
	 * Added debug parameter for testing purposes as we did in the Email class
	 *
	 * @throws \Exception If request has no files or there was an error with the upload
	 */
	public function upload(Closure $callback, bool $single = false, bool $debug = false): array
	{
		$trials  = 0;
		$uploads = [];
		$errors  = [];
		$files   = $this->requestFiles();

		// get error messages from translation
		$errorMessages = [
			UPLOAD_ERR_INI_SIZE   => I18n::translate('upload.error.iniSize'),
			UPLOAD_ERR_FORM_SIZE  => I18n::translate('upload.error.formSize'),
			UPLOAD_ERR_PARTIAL    => I18n::translate('upload.error.partial'),
			UPLOAD_ERR_NO_FILE    => I18n::translate('upload.error.noFile'),
			UPLOAD_ERR_NO_TMP_DIR => I18n::translate('upload.error.tmpDir'),
			UPLOAD_ERR_CANT_WRITE => I18n::translate('upload.error.cantWrite'),
			UPLOAD_ERR_EXTENSION  => I18n::translate('upload.error.extension')
		];

		if (empty($files) === true) {
			$postMaxSize       = Str::toBytes(ini_get('post_max_size'));
			$uploadMaxFileSize = Str::toBytes(ini_get('upload_max_filesize'));

			if ($postMaxSize < $uploadMaxFileSize) {
				throw new Exception(I18n::translate('upload.error.iniPostSize'));
			}

			throw new Exception(I18n::translate('upload.error.noFiles'));
		}

		foreach ($files as $upload) {
			if (
				isset($upload['tmp_name']) === false &&
				is_array($upload) === true
			) {
				continue;
			}

			$trials++;

			try {
				if ($upload['error'] !== 0) {
					$errorMessage = $errorMessages[$upload['error']] ?? I18n::translate('upload.error.default');
					throw new Exception($errorMessage);
				}

				// get the extension of the uploaded file
				$extension = F::extension($upload['name']);

				// try to detect the correct mime and add the extension
				// accordingly. This will avoid .tmp filenames
				if (
					empty($extension) === true ||
					in_array($extension, ['tmp', 'temp']) === true
				) {
					$mime      = F::mime($upload['tmp_name']);
					$extension = F::mimeToExtension($mime);
					$filename  = F::name($upload['name']) . '.' . $extension;
				} else {
					$filename = basename($upload['name']);
				}

				$source = dirname($upload['tmp_name']) . '/' . uniqid() . '.' . $filename;

				// move the file to a location including the extension,
				// for better mime detection
				if (
					$debug === false &&
					move_uploaded_file($upload['tmp_name'], $source) === false
				) {
					throw new Exception(I18n::translate('upload.error.cantMove'));
				}

				$data = $callback($source, $filename);

				if (is_object($data) === true) {
					$data = $this->resolve($data)->toArray();
				}

				$uploads[$upload['name']] = $data;
			} catch (Exception $e) {
				$errors[$upload['name']] = $e->getMessage();
			}

			if ($single === true) {
				break;
			}
		}

		// return a single upload response
		if ($trials === 1) {
			if (empty($errors) === false) {
				return [
					'status'  => 'error',
					'message' => current($errors)
				];
			}

			return [
				'status' => 'ok',
				'data'   => current($uploads)
			];
		}

		if (empty($errors) === false) {
			return [
				'status' => 'error',
				'errors' => $errors
			];
		}

		return [
			'status' => 'ok',
			'data'   => $uploads
		];
	}
}
