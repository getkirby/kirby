<?php

namespace Kirby\Exception;

use Closure;
use Error;
use Kirby\Http\Environment;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Thrown for general exceptions and extended by
 * other exception classes
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Exception extends \Exception
{
	/**
	 * Data variables that can be used inside the exception message
	 */
	protected array $data;

	/**
	 * Additional details that are not included in the exception message
	 */
	protected array $details;

	/**
	 * HTTP code that corresponds with the exception
	 */
	protected int $httpCode;

	/**
	 * The message key, fallback and translation flag
	 * needed to resolve a translated message form i18n strings
	 */
	private string|null $key = null;
	private string|null $fallback = null;
	private bool $translate = true;

	/**
	 * Defaults that can be overridden by specific exception classes
	 */
	protected static string $defaultKey = 'general';
	protected static string $defaultFallback = 'An error occurred';
	protected static array $defaultData = [];
	protected static int $defaultHttpCode = 500;
	protected static array $defaultDetails = [];

	/**
	 * Prefix for the exception key (e.g. 'error.general')
	 */
	private const PREFIX = 'error.';

	public function __construct(
		string|null $message = null,
		string|null $key = null,
		array|null $data = null,
		array|null $details = null,
		string|null $fallback = null,
		int|null $httpCode = null,
		Throwable|null $previous = null,
		bool $translate = true
	) {
		$this->data     = $data ?? static::$defaultData;
		$this->httpCode = $httpCode ?? static::$defaultHttpCode;
		$this->details  = $details ?? static::$defaultDetails;
		$this->key      = $key;

		// hand over to native Exception class constructor
		parent::__construct($message ?? '', 0, $previous);

		// the code and the message are both built from the key and
		// are unset here, so that reading either goes through
		// `::__get()`. Prefixing the code, translating the key and
		// templating the result are by far the most expensive part
		// of building an exception, and an exception that is used
		// as control flow is dropped without reading either.
		unset($this->code);

		// a message that was passed in needs no resolution
		if ($message === null) {
			$this->fallback  = $fallback;
			$this->translate = $translate;

			unset($this->message);
		}
	}

	public function __debugInfo(): array
	{
		$this->resolve();
		return (array)$this;
	}

	public function __get(string $name): mixed
	{
		return match ($name) {
			'code'    => $this->code = $this->code(),
			'message' => $this->message = $this->message(),
			default   => throw new Error(
				'Cannot access property ' . static::class . '::$' . $name
			)
		};
	}

	public function __serialize(): array
	{
		$this->resolve();
		return (array)$this;
	}

	protected function code(): string
	{
		$code = $this->key ?? static::$defaultKey;

		if (str_starts_with($code, self::PREFIX) === true) {
			return $code;
		}

		return self::PREFIX . $code;
	}

	/**
	 * Returns the data variables from the message
	 */
	final public function getData(): array
	{
		return $this->data;
	}

	/**
	 * Returns the additional details that are
	 * not included in the message
	 */
	final public function getDetails(): array
	{
		$details = $this->details;

		foreach ($details as $key => $detail) {
			if ($detail instanceof Throwable) {
				$details[$key] = [
					'label'   => $key,
					'message' => $detail->getMessage(),
				];
			}
		}

		return $details;
	}

	/**
	 * Returns the file in which the Exception was created
	 * relative to the document root
	 */
	final public function getFileRelative(): string
	{
		$file = $this->getFile();
		$root = Environment::getGlobally('DOCUMENT_ROOT');

		if ($root === null || $root === '') {
			return $file;
		}

		return ltrim(Str::after($file, $root), '/');
	}

	/**
	 * Returns the HTTP code that corresponds
	 * with the exception
	 */
	final public function getHttpCode(): int
	{
		return $this->httpCode;
	}

	/**
	 * Returns the exception key (error type)
	 */
	final public function getKey(): string
	{
		return $this->getCode();
	}

	/**
	 * Builds the message. The first source that yields one wins:
	 *
	 * a. the translation for the given key
	 * b. the fallback message of the call site
	 * c. the translation for the default key of the class
	 * d. the default fallback message of the class
	 */
	private function message(): string
	{
		$message =
			$this->translation($this->key) ??
			$this->fallback ??
			$this->translation(static::$defaultKey) ??
			static::$defaultFallback;

		// fill the placeholders of the message with the data
		return Str::template($message, $this->data, ['fallback' => '-']);
	}

	/**
	 * Puts every lazy property in place
	 */
	private function resolve(): void
	{
		if (isset($this->code) === false) {
			$this->code = $this->code();
		}

		if (isset($this->message) === false) {
			$this->message = $this->message();
		}
	}

	/**
	 * Converts the object to an array
	 */
	public function toArray(): array
	{
		return [
			'exception' => static::class,
			'message'   => $this->getMessage(),
			'key'       => $this->getKey(),
			'file'      => $this->getFileRelative(),
			'line'      => $this->getLine(),
			'details'   => $this->getDetails(),
			'code'      => $this->getHttpCode()
		];
	}

	/**
	 * Translates a single message key
	 */
	private function translation(string|null $key): string|array|Closure|null
	{
		if ($this->translate === false || $key === null) {
			return null;
		}

		return I18n::translate(self::PREFIX . $key);
	}
}
