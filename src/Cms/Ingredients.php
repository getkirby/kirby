<?php

namespace Kirby\Cms;

use Closure;

/**
 * The Ingredients class is the foundation for
 * `$kirby->urls()` and `$kirby->roots()` objects.
 * Those are configured in `kirby/config/urls.php`
 * and `kirby/config/roots.php`
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Ingredients
{
	/**
	 * @var array
	 */
	protected $ingredients = [];

	/**
	 * Creates a new ingredient collection
	 *
	 * @param array $ingredients
	 */
	public function __construct(array $ingredients)
	{
		$this->ingredients = $ingredients;
	}

	/**
	 * Magic getter for single ingredients
	 *
	 * @param string $method
	 * @param array|null $args
	 * @return mixed
	 */
	public function __call(string $method, array $args = null)
	{
		return $this->ingredients[$method] ?? null;
	}

	/**
	 * Improved `var_dump` output
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return $this->ingredients;
	}

	/**
	 * Get a single ingredient by key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		return $this->ingredients[$key] ?? null;
	}

	/**
	 * Resolves all ingredient callbacks
	 * and creates a plain array
	 *
	 * @internal
	 * @param array $ingredients
	 * @return static
	 */
	public static function bake(array $ingredients)
	{
		foreach ($ingredients as $name => $ingredient) {
			if ($ingredient instanceof Closure) {
				$ingredients[$name] = $ingredient($ingredients);
			}
		}

		return new static($ingredients);
	}

	/**
	 * Returns all ingredients as plain array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->ingredients;
	}
}
