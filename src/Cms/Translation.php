<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Toolkit\Str;

/**
 * Wrapper around Kirby's localization files,
 * which are stored in `kirby/translations`.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Translation
{
	public function __construct(
		protected string $code,
		protected array $data
	) {
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
	 * Returns the translation author
	 */
	public function author(): string
	{
		return $this->get('translation.author', 'Kirby');
	}

	/**
	 * Returns the official translation code
	 */
	public function code(): string
	{
		return $this->code;
	}

	/**
	 * Returns an array with all
	 * translation strings
	 */
	public function data(): array
	{
		return $this->data;
	}

	/**
	 * Returns the translation data and merges
	 * it with the data from the default translation
	 */
	public function dataWithFallback(): array
	{
		if ($this->code === 'en') {
			return $this->data;
		}

		return [
			// add the fallback array
			...App::instance()->translation('en')->data(),
			...$this->data
		];
	}

	/**
	 * Returns the writing direction
	 * (ltr or rtl)
	 */
	public function direction(): string
	{
		return $this->get('translation.direction', 'ltr');
	}

	/**
	 * Returns a single translation
	 * string by key
	 */
	public function get(string $key, string|null $default = null): string|null
	{
		return $this->data[$key] ?? $default;
	}

	/**
	 * Returns the translation id,
	 * which is also the code
	 */
	public function id(): string
	{
		return $this->code;
	}

	/**
	 * Loads the translation from the
	 * json file in Kirby's translations folder
	 */
	public static function load(
		string $code,
		string $root,
		array $inject = []
	): static {
		$data = [
			...Data::read($root, fail: false),
			...$inject
		];

		return new static($code, $data);
	}

	/**
	 * Returns the PHP locale of the translation
	 */
	public function locale(): string
	{
		$default = $this->code;
		if (Str::contains($default, '_') !== true) {
			$default .= '_' . strtoupper($this->code);
		}

		return $this->get('translation.locale', $default);
	}

	/**
	 * Returns the human-readable translation name.
	 */
	public function name(): string
	{
		return $this->get('translation.name', $this->code);
	}

	/**
	 * Converts the most important
	 * properties to an array
	 */
	public function toArray(): array
	{
		return [
			'code'   => $this->code(),
			'data'   => $this->data(),
			'name'   => $this->name(),
			'author' => $this->author(),
		];
	}
}
