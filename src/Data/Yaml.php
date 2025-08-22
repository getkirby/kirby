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
	#[\Override]
	public static function encode($data): string
	{
		return match (static::handler()) {
			'spyc'  => YamlSpyc::encode($data),
			default => YamlSymfony::encode($data),
		};
	}

	/**
	 * Parses an encoded YAML string and returns a multi-dimensional array
	 */
	#[\Override]
	public static function decode($string): array
	{
		if ($string === null || $string === '') {
			return [];
		}

		if (is_array($string) === true) {
			return $string;
		}

		if (is_string($string) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid YAML data; please pass a string'
			);
		}

		return match (static::handler()) {
			'spyc'  => YamlSpyc::decode($string),
			default => YamlSymfony::decode($string),
		};
	}

	/**
	 * Returns YAML parser configured to be used
	 */
	public static function handler(): string|null
	{
		return App::instance(lazy: true)?->option('yaml.handler');
	}
}
