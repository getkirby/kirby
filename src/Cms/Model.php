<?php

namespace Kirby\Cms;

use Kirby\Guards\ModelGuards;
use Stringable;

/**
 * Model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class Model implements Stringable
{
	/**
	 * Must return the guards object for the model
	 */
	abstract public function guards(): ModelGuards;
}
