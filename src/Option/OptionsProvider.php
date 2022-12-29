<?php

namespace Kirby\Option;

use Kirby\Cms\ModelWithContent;

/**
 * Abstract class as base for dynamic options
 * providers like OptionsApi and OptionsQuery
 *
 * @package   Kirby Option
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class OptionsProvider
{
	public Options|null $options = null;

	/**
	 * Returns options as array
	 */
	public function render(ModelWithContent $model)
	{
		return $this->resolve($model)->render($model);
	}

	/**
	 * Dynamically determines the actual options and resolves
	 * them to the correct text-value entries
	 *
	 * @param bool $safeMode Whether to escape special HTML characters in
	 *                       the option text for safe output in the Panel;
	 *                       only set to `false` if the text is later escaped!
	 */
	abstract public function resolve(ModelWithContent $model, bool $safeMode = true): Options;
}
