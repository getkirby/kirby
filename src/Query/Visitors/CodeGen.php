<?php

namespace Kirby\Query\Visitors;

use Closure;
use Exception;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\Runners\Runtime;

/**
 * Visitor that generates code representations from query structures.
 *
 * The `CodeGen` class traverses query nodes and generates
 * corresponding PHP code. It extends the base `Visitor` class,
 * but adds implementations specific to code generation.
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class CodeGen extends Visitor
{
	/**
	 * If we need something from a namespace,
	 * we'll add the namespace here into the array key
	 * @var array<string,true>
	 */
	public array $uses = [];

	/**
	 * @var array<string,string>
	 */
	public array $mappings = [];

	/**
	 * CodeGen constructor.
	 *
	 * @param array<string,Closure> $validGlobalFunctions An array of valid global function closures.
	 */
	public function __construct(
		public array $validGlobalFunctions = [],
		public array $directAccessFor = []
	) {
	}

	/**
	 * Converts list of arguments to string representation
	 */
	public function argumentList(array $arguments): string
	{
		return join(', ', $arguments);
	}

	/**
	 * Converts array to string representation
	 */
	public function arrayList(array $elements): string
	{
		return '[' . join(', ', $elements) . ']';
	}

	/**
	 * Converts closure node to string representation
	 */
	public function closure(ClosureNode $node): string
	{
		$this->uses[Runtime::class] = true;

		$args = array_map($this::phpName(...), $node->arguments);
		$args = join(', ', $args);

		$newDirectAccessFor = [
			...$this->directAccessFor,
			...array_fill_keys($node->arguments, true)
		];

		$nestedVisitor = new static($this->validGlobalFunctions, $newDirectAccessFor);
		$code = $node->body->resolve($nestedVisitor);

		// promote the nested visitor's uses and mappings to the current visitor
		$this->uses     += $nestedVisitor->uses;
		$this->mappings += $nestedVisitor->mappings;

		return "fn($args) => $code";
	}

	/**
	 * Converts coalescence operator to string representation
	 */
	public function coalescence(mixed $left, mixed $right): string
	{
		return "($left ?? $right)";
	}

	/**
	 * Creates string representation for (global) function
	 */
	public function function(string $name, $arguments): string
	{
		if (isset($this->validGlobalFunctions[$name]) === false) {
			throw new Exception("Invalid global function $name");
		}

		$name = var_export($name, true);
		return "\$functions[$name]($arguments)";
	}

	public function intercept(string $value): string
	{
		return "(\$intercept($value))";
	}

	/**
	 * Converts literals to string representation
	 */
	public function literal(mixed $value): string
	{
		return var_export($value, true);
	}

	/**
	 * Creates string representation for member access
	 */
	public function memberAccess(
		mixed $object,
		array|string|null $arguments,
		string|int $member,
		bool $nullSafe
	): string {
		$this->uses[Runtime::class] = true;

		$params = array_filter([
			$this->intercept($object),
			var_export($member, true),
			$nullSafe ? 'true' : 'false',
			$arguments
		]);

		return 'Runtime::access(' . implode(', ', $params) . ')';
	}

	/**
	 * Variable names in Query Language are different from PHP variable names,
	 * they can start with a number and may contain escaped dots.
	 *
	 * This method returns a sanitized PHP variable name.
	 */
	public static function phpName(string $name): string
	{
		return '$_' . crc32($name);
	}

	/**
	 * Converts ternary operator to string representation
	 */
	public function ternary(
		mixed $condition,
		mixed $true,
		mixed $false,
		bool $elvis
	): string {
		if ($elvis === true) {
			return "($condition ?: $false)";
		}

		return "($condition ? $true : $false)";
	}

	/**
	 * Creates string representation for variable
	 */
	public function variable(string $name): string
	{
		$key = static::phpName($name);

		if (isset($this->directAccessFor[$name]) === true) {
			return $key;
		}

		if (isset($this->mappings[$key]) === false) {
			$name                    = var_export($name, true);
			$this->mappings[$key] = <<<PHP
				match(true) {
					isset(\$context[$name]) && \$context[$name] instanceof Closure => \$context[$name](),
					isset(\$context[$name]) => \$context[$name],
					isset(\$functions[$name]) => \$functions[$name](),
					default => null
				}
				PHP;
		}

		return $key;
	}
}
