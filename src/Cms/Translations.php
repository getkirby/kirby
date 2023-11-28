<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * A collection of all available Translations.
 * Provides a factory method to convert an array
 * to a collection of Translation objects and load
 * method to load all translations from disk
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Translations extends Collection
{
	/**
	 * All registered translations methods
	 */
	public static array $methods = [];

	public static function factory(array $translations): static
	{
		$collection = new static();

		foreach ($translations as $code => $props) {
			$translation = new Translation($code, $props);
			$collection->data[$translation->code()] = $translation;
		}

		return $collection;
	}

	public static function load(string $root, array $inject = []): static
	{
		$collection = new static();

		foreach (Dir::read($root) as $filename) {
			if (F::extension($filename) !== 'json') {
				continue;
			}

			$locale      = F::name($filename);
			$translation = Translation::load($locale, $root . '/' . $filename, $inject[$locale] ?? []);

			$collection->data[$locale] = $translation;
		}

		return $collection;
	}
}
