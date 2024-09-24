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
 *
 * @extends \Kirby\Cms\Collection<\Kirby\Cms\Language>
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
			throw new DuplicateException(
				message: 'You cannot have multiple default languages. Please check your language config files.'
			);
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
	 * Provides a collection of installed languages, even
	 * if in single-language mode. In single-language mode
	 * `Language::single()` is used to create the default language
	 *
	 * @internal
	 */
	public static function ensure(): static
	{
		$kirby = App::instance();

		if ($kirby->multilang() === true) {
			return $kirby->languages();
		}

		return new static([Language::single()]);
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

		return new static($languages);
	}
}
