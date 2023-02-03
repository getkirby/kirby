<?php

namespace Kirby\Toolkit;

use Error;

/**
 * Base for Fluent classes, actually containing the logic
 * of the fluency
 *
 * @package   Kirby Toolkit
 * @author    Adam Kiss <iam@adamkiss.com>
 * @link      https://getkirby.com
 * @copyright Adam Kiss
 * @license   https://opensource.org/licenses/MIT
 */
class Fluent
{
	protected $value = null;

	public function __construct($value = null){
		$this->value = $value;
	}

	/**
	 * Static wrapper for the FluentString constructor
	 *
	 * @param string $value
	 * @return FluentString
	 */
	public static function string(string $value = ''): FluentString
	{
		return new FluentString($value);
	}

	/**
	 * Static wrapper for the FluentArray constructor
	 *
	 * @param array $value
	 * @return FluentArray
	 */
	public static function array(array $value = []): FluentArray
	{
		return new FluentArray($value);
	}

	/**
	 * Proxy for all instance calls
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments): mixed
	{
		$return = match (true) {
			is_array($this->value)
			|| $this::class === FluentArray::class => A::{$name}($this->value, ...$arguments),
			is_string($this->value)
			|| $this::class === FluentString::class => Str::{$name}($this->value, ...$arguments),
			default => throw new Error('Fluent can only be used with arrays or strings'),
		};

		return match (true) {
			is_array($return) => new FluentArray($return),
			is_string($return) => new FluentString($return),
			default => $return,
		};
	}

	/**
	 * Gives access to self and the returns self without change
	 */
	public function tap(callable $callback): self
	{
		$callback($this);
		return $this;
	}
}
