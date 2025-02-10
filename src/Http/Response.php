<?php

namespace Kirby\Http;

use Closure;
use Exception;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Stringable;

/**
 * Representation of an Http response,
 * to simplify sending correct headers
 * and Http status codes.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Response implements Stringable
{
	/**
	 * Store for all registered headers,
	 * which will be sent with the response
	 */
	protected array $headers = [];

	/**
	 * The response body
	 */
	protected string $body;

	/**
	 * The HTTP response code
	 */
	protected int $code;

	/**
	 * The content type for the response
	 */
	protected string $type;

	/**
	 * The content type charset
	 */
	protected string $charset = 'UTF-8';

	/**
	 * Creates a new response object
	 */
	public function __construct(
		string|array $body = '',
		string|null $type = null,
		int|null $code = null,
		array|null $headers = null,
		string|null $charset = null
	) {
		// array construction
		if (is_array($body) === true) {
			$params  = $body;
			$body    = $params['body'] ?? '';
			$type    = $params['type'] ?? $type;
			$code    = $params['code'] ?? $code;
			$headers = $params['headers'] ?? $headers;
			$charset = $params['charset'] ?? $charset;
		}

		// regular construction
		$this->body    = $body;
		$this->type    = $type ?? 'text/html';
		$this->code    = $code ?? 200;
		$this->headers = $headers ?? [];
		$this->charset = $charset ?? 'UTF-8';

		// automatic mime type detection
		if (str_contains($this->type, '/') === false) {
			$this->type = F::extensionToMime($this->type) ?? 'text/html';
		}
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Makes it possible to convert the
	 * entire response object to a string
	 * to send the headers and print the body
	 */
	public function __toString(): string
	{
		return $this->send();
	}

	/**
	 * Getter for the body
	 */
	public function body(): string
	{
		return $this->body;
	}

	/**
	 * Getter for the content type charset
	 */
	public function charset(): string
	{
		return $this->charset;
	}

	/**
	 * Getter for the HTTP status code
	 */
	public function code(): int
	{
		return $this->code;
	}

	/**
	 * Creates a response that triggers
	 * a file download for the given file
	 *
	 * @param array $props Custom overrides for response props (e.g. headers)
	 */
	public static function download(
		string $file,
		string|null $filename = null,
		array $props = []
	): static {
		if (file_exists($file) === false) {
			throw new Exception(message: 'The file could not be found');
		}

		$filename ??= basename($file);
		$modified   = filemtime($file);
		$body       = file_get_contents($file);
		$size       = strlen($body);

		$props = array_replace_recursive([
			'body'    => $body,
			'type'    => F::mime($file),
			'headers' => [
				'Pragma'                    => 'public',
				'Cache-Control'             => 'no-cache, no-store, must-revalidate',
				'Last-Modified'             => gmdate('D, d M Y H:i:s', $modified) . ' GMT',
				'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
				'Content-Transfer-Encoding' => 'binary',
				'Content-Length'            => $size,
				'Connection'                => 'close'
			]
		], $props);

		return new static($props);
	}

	/**
	 * Creates a response for a file and
	 * sends the file content to the browser
	 *
	 * @param array $props Custom overrides for response props (e.g. headers)
	 */
	public static function file(string $file, array $props = []): static
	{
		$props = [
			'body' => F::read($file),
			'type' => F::extensionToMime(F::extension($file)),
			...$props
		];

		// if we couldn't serve a correct MIME type, force
		// the browser to display the file as plain text to
		// harden against attacks from malicious file uploads
		if ($props['type'] === null) {
			if (isset($props['headers']) !== true) {
				$props['headers'] = [];
			}

			$props['type'] = 'text/plain';
			$props['headers']['X-Content-Type-Options'] = 'nosniff';
		}

		return new static($props);
	}


	/**
	 * Redirects to the given Urls
	 * Urls can be relative or absolute.
	 * @since 3.7.0
	 *
	 * @codeCoverageIgnore
	 */
	public static function go(string $url = '/', int $code = 302): never
	{
		die(static::redirect($url, $code));
	}

	/**
	 * Ensures that the callback does not produce the first body output
	 * (used to show when loading a file creates side effects)
	 */
	public static function guardAgainstOutput(Closure $callback, ...$args): mixed
	{
		$before = headers_sent();
		$result = $callback(...$args);
		$after  = headers_sent($file, $line);

		if ($before === false && $after === true) {
			throw new LogicException("Disallowed output from file $file:$line, possible accidental whitespace?");
		}

		return $result;
	}

	/**
	 * Getter for single headers
	 *
	 * @param string $key Name of the header
	 */
	public function header(string $key): string|null
	{
		return $this->headers[$key] ?? null;
	}

	/**
	 * Getter for all headers
	 */
	public function headers(): array
	{
		return $this->headers;
	}

	/**
	 * Creates a json response with appropriate
	 * header and automatic conversion of arrays.
	 */
	public static function json(
		string|array $body = '',
		int|null $code = null,
		bool|null $pretty = null,
		array $headers = []
	): static {
		if (is_array($body) === true) {
			$body = json_encode($body, $pretty === true ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0);
		}

		return new static([
			'body'    => $body,
			'code'    => $code,
			'type'    => 'application/json',
			'headers' => $headers
		]);
	}

	/**
	 * Creates a redirect response,
	 * which will send the visitor to the
	 * given location.
	 */
	public static function redirect(string $location = '/', int $code = 302): static
	{
		return new static([
			'code' => $code,
			'headers' => [
				'Location' => Url::unIdn($location)
			]
		]);
	}

	/**
	 * Sends all registered headers and
	 * returns the response body
	 */
	public function send(): string
	{
		// send the status response code
		http_response_code($this->code());

		// send all custom headers
		foreach ($this->headers() as $key => $value) {
			header($key . ': ' . $value);
		}

		// send the content type header
		header('Content-Type: ' . $this->type() . '; charset=' . $this->charset());

		// print the response body
		return $this->body();
	}

	/**
	 * Converts all relevant response attributes
	 * to an associative array for debugging,
	 * testing or whatever.
	 */
	public function toArray(): array
	{
		return [
			'type'    => $this->type(),
			'charset' => $this->charset(),
			'code'    => $this->code(),
			'headers' => $this->headers(),
			'body'    => $this->body()
		];
	}

	/**
	 * Getter for the content type
	 */
	public function type(): string
	{
		return $this->type;
	}
}
