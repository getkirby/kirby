<?php

namespace Kirby\Http;

use Closure;

/**
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Route
{
	/**
	 * The callback action function
	 */
	protected Closure $action;

	/**
	 * Listed of parsed arguments
	 */
	protected array $arguments = [];

	/**
	 * An array of all passed attributes
	 */
	protected array $attributes = [];

	/**
	 * The registered request method
	 */
	protected string $method;

	/**
	 * The registered pattern
	 */
	protected string $pattern;

	/**
	 * Wildcards, which can be used in
	 * Route patterns to make regular expressions
	 * a little more human
	 */
	protected array $wildcards = [
		'required' => [
			'(:num)'      => '(-?[0-9]+)',
			'(:alpha)'    => '([a-zA-Z]+)',
			'(:alphanum)' => '([a-zA-Z0-9]+)',
			'(:any)'      => '([a-zA-Z0-9\.\-_%= \+\@\(\)]+)',
			'(:all)'      => '(.*)',
		],
		'optional' => [
			'/(:num?)'      => '(?:/(-?[0-9]+)',
			'/(:alpha?)'    => '(?:/([a-zA-Z]+)',
			'/(:alphanum?)' => '(?:/([a-zA-Z0-9]+)',
			'/(:any?)'      => '(?:/([a-zA-Z0-9\.\-_%= \+\@\(\)]+)',
			'/(:all?)'      => '(?:/(.*)',
		],
	];

	/**
	 * Magic getter for route attributes
	 */
	public function __call(string $key, array $arguments = null)
	{
		return $this->attributes[$key] ?? null;
	}

	/**
	 * Creates a new Route object for the given
	 * pattern(s), method(s) and the callback action
	 */
	public function __construct(string $pattern, string $method, Closure $action, array $attributes = [])
	{
		$this->action     = $action;
		$this->attributes = $attributes;
		$this->method     = $method;
		$this->pattern    = $this->regex(ltrim($pattern, '/'));
	}

	/**
	 * Getter for the action callback
	 */
	public function action(): Closure
	{
		return $this->action;
	}

	/**
	 * Returns all parsed arguments
	 */
	public function arguments(): array
	{
		return $this->arguments;
	}

	/**
	 * Getter for additional attributes
	 */
	public function attributes(): array
	{
		return $this->attributes;
	}

	/**
	 * Getter for the method
	 */
	public function method(): string
	{
		return $this->method;
	}

	/**
	 * Returns the route name if set
	 */
	public function name(): string|null
	{
		return $this->attributes['name'] ?? null;
	}

	/**
	 * Throws a specific exception to tell
	 * the router to jump to the next route
	 * @since 3.0.3
	 */
	public static function next(): void
	{
		throw new Exceptions\NextRouteException('next');
	}

	/**
	 * Getter for the pattern
	 */
	public function pattern(): string
	{
		return $this->pattern;
	}

	/**
	 * Converts the pattern into a full regular
	 * expression by replacing all the wildcards
	 */
	public function regex(string $pattern): string
	{
		$search   = array_keys($this->wildcards['optional']);
		$replace  = array_values($this->wildcards['optional']);

		// For optional parameters, first translate the wildcards to their
		// regex equivalent, sans the ")?" ending. We'll add the endings
		// back on when we know the replacement count.
		$pattern = str_replace($search, $replace, $pattern, $count);

		if ($count > 0) {
			$pattern .= str_repeat(')?', $count);
		}

		return strtr($pattern, $this->wildcards['required']);
	}

	/**
	 * Tries to match the path with the regular expression and
	 * extracts all arguments for the Route action
	 */
	public function parse(string $pattern, string $path): array|false
	{
		// check for direct matches
		if ($pattern === $path) {
			return $this->arguments = [];
		}

		// We only need to check routes with regular expression since all others
		// would have been able to be matched by the search for literal matches
		// we just did before we started searching.
		if (strpos($pattern, '(') === false) {
			return false;
		}

		// If we have a match we'll return all results
		// from the preg without the full first match.
		if (preg_match('#^' . $this->regex($pattern) . '$#u', $path, $parameters)) {
			return $this->arguments = array_slice($parameters, 1);
		}

		return false;
	}
}
