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
	 *
	 * @var array
	 */
	public static $methods = [];

	/**
	 * Creates a new collection with the given language objects
	 *
	 * @param array $objects `Kirby\Cms\Language` objects
	 * @param null $parent
	 * @throws \Kirby\Exception\DuplicateException
	 */
	public function __construct($objects = [], $parent = null)
	{
		$defaults = array_filter(
			$objects,
			fn ($language) => $language->isDefault() === true
		);

		if (count($defaults) > 1) {
			throw new DuplicateException('You cannot have multiple default languages. Please check your language config files.');
		}

		parent::__construct($objects, $parent);
	}

	/**
	 * Returns all language codes as array
	 *
	 * @return array
	 */
	public function codes(): array
	{
		return $this->keys();
	}

	/**
	 * Creates a new language with the given props
	 *
	 * @internal
	 * @param array $props
	 * @return \Kirby\Cms\Language
	 */
	public function create(array $props)
	{
		return Language::create($props);
	}

	/**
	 * Returns the default language
	 *
	 * @return \Kirby\Cms\Language|null
	 */
	public function default()
	{
		return $this->findBy('isDefault', true) ?? $this->first();
	}

	/**
	 * Convert all defined languages to a collection
	 *
	 * @internal
	 * @return static
	 */
	public static function load()
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

		return new static($languages);
	}
}
