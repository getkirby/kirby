<?php

namespace Kirby\Query;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\A;

/**
 * The Expression class adds support for simple shorthand
 * comparisons (`a ? b : c`, `a ?: c` and `a ?? b`)
 *
 * @package   Kirby Query
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Expression
{
	public function __construct(
		public array $parts
	) {
	}

	/**
	 * Parses an expression string into its parts
	 */
	public static function factory(string $expression, Query $parent = null): static|Segments
	{
		// split into different expression parts and operators
		$parts = static::parse($expression);

		// shortcut: if expression has only one part, directly
		// continue with the segments chain
		if (count($parts) === 1) {
			return Segments::factory(query: $parts[0], parent: $parent);
		}

		// turn all non-operator parts into an Argument
		// which takes care of converting string, arrays booleans etc.
		// into actual types and treats all other parts as their own queries
		$parts = A::map(
			$parts,
			fn ($part) =>
				in_array($part, ['?', ':', '?:', '??'])
					? $part
					: Argument::factory($part)
		);

		return new static(parts: $parts);
	}

	/**
	 * Splits a comparison string into an array
	 * of expressions and operators
	 * @internal
	 */
	public static function parse(string $string): array
	{
		// split by multiples of `?` and `:`, but not inside skip groups
		// (parantheses, quotes etc.)
		return preg_split(
			'/\s+([\?\:]+)\s+|' . Arguments::OUTSIDE . '/',
			trim($string),
			flags: PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
		);
	}

	/**
	 * Resolves the expression by evaluating
	 * the supported comparisons and consecutively
	 * resolving the resulting query/argument
	 */
	public function resolve(array|object $data = []): mixed
	{
		$base = null;

		foreach ($this->parts as $index => $part) {
			// `a ?? b`
			// if the base/previous (e.g. `a`) isn't null,
			// stop the expression chain and return `a`
			if ($part === '??') {
				if ($base !== null) {
					return $base;
				}

				continue;
			}

			// `a ?: b`
			// if `a` isn't false, return `a`, otherwise `b`
			if ($part === '?:') {
				if ($base != false) {
					return $base;
				}

				return $this->parts[$index + 1]->resolve($data);
			}

			// `a ? b : c`
			// if `a` isn't false, return `b`, otherwise `c`
			if ($part === '?') {
				if (($this->parts[$index + 2] ?? null) !== ':') {
					throw new LogicException('Query: Incomplete ternary operator (missing matching `? :`)');
				}

				if ($base != false) {
					return $this->parts[$index + 1]->resolve($data);
				}

				return $this->parts[$index + 3]->resolve($data);
			}

			$base = $part->resolve($data);
		}

		return $base;
	}
}
