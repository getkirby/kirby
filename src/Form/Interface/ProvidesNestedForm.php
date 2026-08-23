<?php

namespace Kirby\Form\Interface;

use Kirby\Form\Form;

/**
 * Implemented by fields that hold their own set of fields
 * and expose them as a single nested form
 * (e.g. the object or structure field)
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
interface ProvidesNestedForm
{
	public function form(): Form;
}
