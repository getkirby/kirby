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

	abstract public function resolve(ModelWithContent $model): Options;
}
