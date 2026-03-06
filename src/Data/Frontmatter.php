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
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.4.0
 */
class Frontmatter extends Handler
{
	/**
	 * The field name used to store the body content
	 * that appears after the closing --- delimiter
	 */
	public const BODY_KEY = 'text';

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
				$fields[static::BODY_KEY] = $body;
			}

			return $fields;
		}

		// fall back to plain YAML for files without delimiters
		return Yaml::decode($string);
	}

	public static function encode($data): string
	{
		$body = $data[static::BODY_KEY] ?? null;

		// remove the body key from the frontmatter fields
		unset($data[static::BODY_KEY]);

		$frontmatter = "---\n" . rtrim(Yaml::encode($data)) . "\n---\n";

		if ($body !== null && trim($body) !== '') {
			return $frontmatter . $body . "\n";
		}

		return $frontmatter;
	}
}
