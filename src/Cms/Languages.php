<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\F;

/**
 * A collection of all defined site languages
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Languages extends Collection
{
	/**
	 * All registered languages methods
	 */
	public static array $methods = [];

	/**
	 * Creates a new collection with the given language objects
	 *
	 * @param null $parent
	 * @throws \Kirby\Exception\DuplicateException
	 */
	public function __construct(
		array $objects = [],
		$parent = null
	) {
		$defaults = array_filter(
			$objects,
			fn ($language) => $language->isDefault() === true
		);

		if (count($defaults) > 1) {
			throw new DuplicateException('You cannot have multiple default languages. Please check your language config files.');
		}

		parent::__construct($objects, null);
	}

	/**
	 * Returns all language codes as array
	 */
	public function codes(): array
	{
		return App::instance()->multilang() ? $this->keys() : ['default'];
	}

	/**
	 * Creates a new language with the given props
	 * @internal
	 */
	public function create(array $props): Language
	{
		return Language::create($props);
	}

	/**
	 * Returns the default language
	 */
	public function default(): Language|null
	{
		return $this->findBy('isDefault', true) ?? $this->first();
	}

	/**
	 * Convert all defined languages to a collection
	 * @internal
	 */
	public static function load(): static
	{
		$languages = [];
		$files     = glob(App::instance()->root('languages') . '/*.php');

		foreach ($files as $file) {
			$props = F::load($file, allowOutput: false);

			if (is_array($props) === true) {
				// inject the language code from the filename
				// if it does not exist
				$props['code'] ??= F::name($file);

				$languages[] = new Language($props);
			}
		}

		if (count($languages) === 0) {
			$languages[] = new SingleLanguage();
		}

		return new static($languages);
	}
}
