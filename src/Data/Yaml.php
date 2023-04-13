<?php

namespace Kirby\Data;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;

/**
 * Simple Wrapper around the Symfony or Spyc YAML class
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Yaml extends Handler
{
	/**
	 * Converts an array to an encoded YAML string
	 */
	public static function encode($data): string
	{
		return match (static::handler()) {
			'symfony' => YamlSymfony::encode($data),
			default   => YamlSpyc::encode($data),
		};
	}

	/**
	 * Parses an encoded YAML string and returns a multi-dimensional array
	 */
	public static function decode($string): array
	{
		if ($string === null || $string === '') {
			return [];
		}

		if (is_array($string) === true) {
			return $string;
		}

		if (is_string($string) === false) {
			throw new InvalidArgumentException('Invalid YAML data; please pass a string');
		}

		return match (static::handler()) {
			'symfony' => YamlSymfony::decode($string),
			default   => YamlSpyc::decode($string)
		};
	}

	/**
	 * Returns which YAML parser (`spyc` or `symfony`)
	 * is configured to be used
	 * @internal
	 */
	public static function handler(): string
	{
		return App::instance(null, true)?->option('yaml.handler') ?? 'spyc';
	}
}
