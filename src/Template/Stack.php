<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\Str;
use Stringable;

/**
 * Simple stack storage for template output
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Stack
{
	/**
	 * Render depth counter
	 */
	protected static int $depths = 0;

	/**
	 * Stacks currently capturing output
	 *
	 * @var list<array{name: string, unique: bool, level: int}>
	 */
	protected static array $active = [];

	/**
	 * Deferred stack placeholders by their token
	 *
	 * @var array<string, array{name: string, glue: string, clear: bool}>
	 */
	protected static array $placeholders = [];

	/**
	 * Stored stack contents
	 *
	 * @var array<string, list<string>>
	 */
	protected static array $stacks = [];

	/**
	 * Starts capturing output for a stack
	 */
	public static function begin(string $name, bool $unique = false): void
	{
		ob_start();

		static::$active[] = [
			'name'   => $name,
			'unique' => $unique,
			'level'  => ob_get_level()
		];
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
		$active = array_pop(static::$active);

		if ($active === null) {
			return;
		}

		// otherwise the stack would capture or close the wrong buffer
		if (ob_get_level() > $active['level']) {
			throw new LogicException(
				message: 'The "' . $active['name'] . '" stack cannot be closed: an output buffer started inside of it with `ob_start()` is still open'
			);
		}

		if (ob_get_level() < $active['level']) {
			throw new LogicException(
				message: 'The "' . $active['name'] . '" stack cannot be closed: its output was already ended inside of it, e.g. with `ob_end_clean()`'
			);
		}

		static::push(
			$active['name'],
			ob_get_clean(),
			$active['unique']
		);
	}

	/**
	 * Returns whether stacks are currently rendered
	 */
	public static function isRendering(): bool
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
		string $glue = PHP_EOL,
		bool $clear = true
	): string {
		$token = '<!--kirby-stack:' . Str::uuid() . '-->';

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
		string|Stringable $content,
		bool $unique = false
	): void {
		$content = (string)$content;

		if (
			$unique === true &&
			in_array($content, static::$stacks[$name] ?? [], true) === true
		) {
			return;
		}

		static::$stacks[$name][] = $content;
	}

	/**
	 * Renders a stack and optionally clears it
	 */
	public static function render(
		string $name,
		string $glue = PHP_EOL,
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
		static::$active       = [];
		static::$depths       = 0;
		static::$placeholders = [];
		static::$stacks       = [];
	}
}
