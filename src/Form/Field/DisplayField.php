<?php

namespace Kirby\Form\Field;

use Kirby\Form\Field;
use Kirby\Form\Mixin;

/**
 * Base class for fields that have no value
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class DisplayField extends Field
{
	use Mixin\Help;
	use Mixin\Label;
}
