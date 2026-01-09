<?php

namespace Kirby\Template;

use Stringable;

/**
 * Simple stack storage for template output
 *
 * @package   Kirby Template
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Stack
{
	/**
	 * Render depth counter
	 */
	protected static int $depths = 0;

	/**
	 * Names of stacks currently capturing output
	 */
	protected static array $active = [];

	/**
	 * Deferred stack placeholders
	 */
	protected static array $placeholders = [];

	/**
	 * Stored stack contents
	 */
	protected static array $stacks = [];

	/**
	 * Starts capturing output for a stack
	 */
	public static function begin(string $name): void
	{
		static::$active[] = $name;
		ob_start();
	}

	/**
	 * Marks the end of a render cycle
	 */
	public static function close(): void
	{
		if (static::$depths > 0) {
			static::$depths--;
		}
	}

	/**
	 * Ends the last started stack capture
	 */
	public static function end(): void
	{
		$name = array_pop(static::$active);

		if ($name === null) {
			return;
		}

		static::push($name, ob_get_clean());
	}

	/**
	 * Returns whether stacks are currently rendered
	 */
	public static function isOpen(): bool
	{
		return static::$depths > 0;
	}

	/**
	 * Marks the beginning of a render cycle
	 */
	public static function open(): void
	{
		static::$depths++;
	}

	/**
	 * Creates a placeholder for deferred rendering
	 */
	public static function placeholder(
		string $name,
		string $glue = '',
		bool $clear = true
	): string {
		$id    = count(static::$placeholders) + 1;
		$token = '<!--kirby-stack:' . $id . '-->';

		static::$placeholders[$token] = [
			'name'  => $name,
			'glue'  => $glue,
			'clear' => $clear
		];

		return $token;
	}

	/**
	 * Pushes content to a stack
	 */
	public static function push(
		string $name,
		string|Stringable $content
	): void {
		static::$stacks[$name][] = (string)$content;
	}

	/**
	 * Renders a stack and optionally clears it
	 */
	public static function render(
		string $name,
		string $glue = '',
		bool $clear = true
	): string {
		$content = implode($glue, static::$stacks[$name] ?? []);

		if ($clear === true) {
			unset(static::$stacks[$name]);
		}

		return $content;
	}

	/**
	 * Replaces placeholders with rendered stack contents
	 */
	public static function replace(string $content): string
	{
		if (static::$placeholders === []) {
			return $content;
		}

		foreach (static::$placeholders as $token => $config) {
			$content = str_replace(
				$token,
				static::render(...$config),
				$content
			);
		}

		static::$placeholders = [];

		return $content;
	}

	/**
	 * Resets all stacks
	 */
	public static function reset(): void
	{
		static::$active = [];
		static::$stacks = [];
		static::$placeholders = [];
		static::$depths = 0;
	}
}
