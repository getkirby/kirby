<?php

namespace Kirby\Http;

use CurlHandle;
use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use stdClass;

/**
 * A handy little class to handle
 * all kinds of remote requests
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Remote
{
	public const CA_INTERNAL = 1;
	public const CA_SYSTEM   = 2;

	public static array $defaults = [
		'agent'     => null,
		'basicAuth' => null,
		'body'      => true,
		'ca'        => self::CA_INTERNAL,
		'data'      => [],
		'encoding'  => 'utf-8',
		'file'      => null,
		'headers'   => [],
		'method'    => 'GET',
		'progress'  => null,
		'test'      => false,
		'timeout'   => 10,
	];

	public string|null $content = null;
	public CurlHandle|false $curl;
	public array $curlopt = [];
	public int $errorCode;
	public string $errorMessage;
	public array $headers = [];
	public array $info = [];
	public array $options = [];

	/**
	 * @throws \Exception when the curl request failed
	 */
	public function __construct(string $url, array $options = [])
	{
		$defaults = static::$defaults;

		// use the system CA store by default if
		// one has been configured in php.ini
		$cainfo = ini_get('curl.cainfo');

		// Suppress warnings e.g. if system CA is outside of open_basedir (See: issue #6236)
		if (empty($cainfo) === false && @is_file($cainfo) === true) {
			$defaults['ca'] = self::CA_SYSTEM;
		}

		// update the defaults with App config if set;
		// request the App instance lazily
		if ($app = App::instance(null, true)) {
			$defaults = [...$defaults, ...$app->option('remote', [])];
		}

		// set all options
		$this->options = [...$defaults, ...$options];

		// add the url
		$this->options['url'] = $url;

		// send the request
		$this->fetch();
	}

	/**
	 * Magic getter for request info data
	 */
	public function __call(string $method, array $arguments = [])
	{
		$method = str_replace('-', '_', Str::kebab($method));
		return $this->info[$method] ?? null;
	}

	public static function __callStatic(
		string $method,
		array $arguments = []
	): static {
		return new static(
			url:     $arguments[0],
			options: [
				'method' => strtoupper($method),
				...$arguments[1] ?? []
			]
		);
	}

	/**
	 * Returns the http status code
	 */
	public function code(): int|null
	{
		return $this->info['http_code'] ?? null;
	}

	/**
	 * Returns the response content
	 */
	public function content(): string|null
	{
		return $this->content;
	}

	/**
	 * Sets up all curl options and sends the request
	 *
	 * @return $this
	 * @throws \Exception when the curl request failed
	 */
	public function fetch(): static
	{
		// curl options
		$this->curlopt = [
			CURLOPT_URL              => $this->options['url'],
			CURLOPT_ENCODING         => $this->options['encoding'],
			CURLOPT_CONNECTTIMEOUT   => $this->options['timeout'],
			CURLOPT_TIMEOUT          => $this->options['timeout'],
			CURLOPT_AUTOREFERER      => true,
			CURLOPT_RETURNTRANSFER   => $this->options['body'],
			CURLOPT_FOLLOWLOCATION   => true,
			CURLOPT_MAXREDIRS        => 10,
			CURLOPT_HEADER           => false,
			CURLOPT_HEADERFUNCTION   => function ($curl, $header): int {
				$parts = Str::split($header, ':');

				if (empty($parts[0]) === false && empty($parts[1]) === false) {
					$key = array_shift($parts);
					$this->headers[$key] = implode(':', $parts);
				}

				return strlen($header);
			}
		];

		// determine the TLS CA to use
		if ($this->options['ca'] === self::CA_INTERNAL) {
			$this->curlopt[CURLOPT_SSL_VERIFYPEER] = true;
			$this->curlopt[CURLOPT_CAINFO] = dirname(__DIR__, 2) . '/cacert.pem';
		} elseif ($this->options['ca'] === self::CA_SYSTEM) {
			$this->curlopt[CURLOPT_SSL_VERIFYPEER] = true;
		} elseif ($this->options['ca'] === false) {
			$this->curlopt[CURLOPT_SSL_VERIFYPEER] = false;
		} elseif (
			is_string($this->options['ca']) === true &&
			is_file($this->options['ca']) === true
		) {
			$this->curlopt[CURLOPT_SSL_VERIFYPEER] = true;
			$this->curlopt[CURLOPT_CAINFO] = $this->options['ca'];
		} elseif (
			is_string($this->options['ca']) === true &&
			is_dir($this->options['ca']) === true
		) {
			$this->curlopt[CURLOPT_SSL_VERIFYPEER] = true;
			$this->curlopt[CURLOPT_CAPATH] = $this->options['ca'];
		} else {
			throw new InvalidArgumentException(
				message: 'Invalid "ca" option for the Remote class'
			);
		}

		// add the progress
		if (is_callable($this->options['progress']) === true) {
			$this->curlopt[CURLOPT_NOPROGRESS]       = false;
			$this->curlopt[CURLOPT_PROGRESSFUNCTION] = $this->options['progress'];
		}

		// add all headers
		if (empty($this->options['headers']) === false) {
			// convert associative arrays to strings
			$headers = [];
			foreach ($this->options['headers'] as $key => $value) {
				if (is_string($key) === true) {
					$value = $key . ': ' . $value;
				}

				$headers[] = $value;
			}

			$this->curlopt[CURLOPT_HTTPHEADER] = $headers;
		}

		// add HTTP Basic authentication
		if (empty($this->options['basicAuth']) === false) {
			$this->curlopt[CURLOPT_USERPWD] = $this->options['basicAuth'];
		}

		// add the user agent
		if (empty($this->options['agent']) === false) {
			$this->curlopt[CURLOPT_USERAGENT] = $this->options['agent'];
		}

		// do some request specific stuff
		switch (strtoupper($this->options['method'])) {
			case 'POST':
				$this->curlopt[CURLOPT_POST]          = true;
				$this->curlopt[CURLOPT_CUSTOMREQUEST] = 'POST';
				$this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
				break;
			case 'PUT':
				$this->curlopt[CURLOPT_CUSTOMREQUEST] = 'PUT';
				$this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);

				// put a file
				if ($this->options['file']) {
					$this->curlopt[CURLOPT_INFILE]     = fopen($this->options['file'], 'r');
					$this->curlopt[CURLOPT_INFILESIZE] = F::size($this->options['file']);
				}
				break;
			case 'PATCH':
				$this->curlopt[CURLOPT_CUSTOMREQUEST] = 'PATCH';
				$this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
				break;
			case 'DELETE':
				$this->curlopt[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				$this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
				break;
			case 'HEAD':
				$this->curlopt[CURLOPT_CUSTOMREQUEST] = 'HEAD';
				$this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
				$this->curlopt[CURLOPT_NOBODY]        = true;
				break;
		}

		if ($this->options['test'] === true) {
			return $this;
		}

		// start a curl request
		$this->curl = curl_init();

		curl_setopt_array($this->curl, $this->curlopt);

		$this->content      = curl_exec($this->curl);
		$this->info         = curl_getinfo($this->curl);
		$this->errorCode    = curl_errno($this->curl);
		$this->errorMessage = curl_error($this->curl);

		if ($this->errorCode) {
			throw new Exception($this->errorMessage, $this->errorCode);
		}

		curl_close($this->curl);

		return $this;
	}

	/**
	 * Static method to send a GET request
	 *
	 * @throws \Exception when the curl request failed
	 */
	public static function get(string $url, array $params = []): static
	{
		$options = [
			'method' => 'GET',
			'data'   => [],
			...$params
		];

		$query = http_build_query($options['data']);

		if (empty($query) === false) {
			$url = match (Url::hasQuery($url)) {
				true    => $url . '&' . $query,
				default => $url . '?' . $query
			};
		}

		// remove the data array from the options
		unset($options['data']);

		return new static($url, $options);
	}

	/**
	 * Returns all received headers
	 */
	public function headers(): array
	{
		return $this->headers;
	}

	/**
	 * Returns the request info
	 */
	public function info(): array
	{
		return $this->info;
	}

	/**
	 * Decode the response content
	 *
	 * @param bool $array decode as array or object
	 * @psalm-return ($array is true ? array|null : stdClass|null)
	 */
	public function json(bool $array = true): array|stdClass|null
	{
		return json_decode($this->content(), $array);
	}

	/**
	 * Returns the request method
	 */
	public function method(): string
	{
		return $this->options['method'];
	}

	/**
	 * Returns all options which have been
	 * set for the current request
	 */
	public function options(): array
	{
		return $this->options;
	}

	/**
	 * Internal method to handle post field data
	 */
	protected function postfields($data)
	{
		if (is_object($data) || is_array($data)) {
			return http_build_query($data);
		}

		return $data;
	}

	/**
	 * Static method to init this class and send a request
	 *
	 * @throws \Exception when the curl request failed
	 */
	public static function request(string $url, array $params = []): static
	{
		return new static($url, $params);
	}

	/**
	 * Returns the request Url
	 */
	public function url(): string
	{
		return $this->options['url'];
	}
}
