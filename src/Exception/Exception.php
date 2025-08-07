<?php

namespace Kirby\Exception;

use Kirby\Http\Environment;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Exception
 * Thrown for general exceptions and extended by
 * other exception classes
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @todo remove $arg array once all exception throws have been refactored
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
	 * Whether the exception message could be translated
	 * into the user's language
	 */
	protected bool $isTranslated = true;

	/**
	 * Defaults that can be overridden by specific
	 * exception classes
	 */
	protected static string $defaultKey = 'general';
	protected static string $defaultFallback = 'An error occurred';
	protected static array $defaultData = [];
	protected static int $defaultHttpCode = 500;
	protected static array $defaultDetails = [];

	/**
	 * Prefix for the exception key (e.g. 'error.general')
	 */
	private static string $prefix = 'error';

	public function __construct(
		array|string $args = [], // @deprecated

		string|null $key = null,
		array|null $data = null,
		array|null $details = null,
		string|null $fallback = null,
		int|null $httpCode = null,
		string|null $message = null,
		Throwable|null $previous = null,
		bool $translate = true
	) {
		$key      ??= $args['key'] ?? null;
		$fallback ??= $args['fallback'] ?? null;
		$previous ??= $args['previous'] ?? null;

		$this->data =
			$data ??
			$args['data'] ??
			static::$defaultData;

		$this->httpCode =
			$httpCode ??
			$args['httpCode'] ??
			static::$defaultHttpCode;

		$this->details =
			$details ??
			$args['details'] ??
			static::$defaultDetails;

		// set the Exception code to the key
		$this->code = $key ?? static::$defaultKey;

		if (Str::startsWith($this->code, self::$prefix . '.') === false) {
			$this->code = self::$prefix . '.' . $this->code;
		}

		if (is_string($args) === true) {
			$message ??= $args;
		}

		if ($message !== null) {
			$this->isTranslated = false;
			parent::__construct($message);
			return;
		}

		// define whether message can/should be translated
		$translate = $args['translate'] ?? $translate;

		// a. translation for provided key in current language
		// b. translation for provided key in default language
		if ($translate === true && $key !== null) {
			$message = I18n::translate(self::$prefix . '.' . $key);
			$this->isTranslated = true;
		}

		// c. provided fallback message
		if ($message === null) {
			$message = $fallback;
			$this->isTranslated = false;
		}

		// d. translation for default key in current language
		// e. translation for default key in default language
		if ($translate === true && $message === null) {
			$message = I18n::translate(self::$prefix . '.' . static::$defaultKey);
			$this->isTranslated = true;
		}

		// f. default fallback message
		if ($message === null) {
			$message = static::$defaultFallback;
			$this->isTranslated = false;
		}

		// format message with passed data
		$message = Str::template($message, $this->data, ['fallback' => '-']);

		// hand over to native Exception class constructor
		parent::__construct($message, 0, $previous);
	}

	/**
	 * Returns the file in which the Exception was created
	 * relative to the document root
	 */
	final public function getFileRelative(): string
	{
		$file = $this->getFile();
		$root = Environment::getGlobally('DOCUMENT_ROOT');

		if (empty($root) === true) {
			return $file;
		}

		return ltrim(Str::after($file, $root), '/');
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
	 * Returns the exception key (error type)
	 */
	final public function getKey(): string
	{
		return $this->getCode();
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
	 * Returns whether the exception message could
	 * be translated into the user's language
	 */
	final public function isTranslated(): bool
	{
		return $this->isTranslated;
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
}
