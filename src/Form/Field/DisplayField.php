<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldClass;

/**
 * Base class for fields that have no value
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class DisplayField extends FieldClass
{
	public function hasValue(): bool
	{
		return false;
	}
}
