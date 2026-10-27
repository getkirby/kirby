<?php

namespace Kirby\Data;

/**
 * Reads and writes YAML frontmatter format:
 *
 * ---
 * title: My Title
 * uuid: abc123
 * ---
 * Optional body stored as `text` field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Frontmatter extends Handler
{
	/**
	 * The field name used to store the body content
	 * that appears after the closing --- delimiter
	 */
	public static string $body = 'text';

	/**
	 * Parses a frontmatter string and returns a multi-dimensional array
	 */
	public static function decode($string): array
	{
		if ($string === null || $string === '') {
			return [];
		}

		if (is_array($string) === true) {
			return $string;
		}

		// parse standard frontmatter block: opening ---, YAML, closing ---,
		// and optional body text after the closing delimiter
		if (preg_match('/^---\r?\n(.*?)\r?\n---\r?\n?(.*)?$/s', $string, $matches)) {
			$fields = Yaml::decode($matches[1]);
			$body   = trim($matches[2] ?? '');

			if ($body !== '') {
				$fields[static::$body] = $body;
			}

			return $fields;
		}

		// fall back to plain YAML for files without delimiters
		return Yaml::decode($string);
	}

	/**
	 * Converts an array to a frontmatter string
	 */
	public static function encode($data): string
	{
		$body = $data[static::$body] ?? null;
		unset($data[static::$body]);

		$frontmatter = "---\n" . rtrim(Yaml::encode($data)) . "\n---\n";

		if ($body !== null && trim($body) !== '') {
			return $frontmatter . $body . "\n";
		}

		return $frontmatter;
	}
}
