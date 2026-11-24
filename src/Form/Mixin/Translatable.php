<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;
use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `translate` prop to mark the field as translatable
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Translatable
{
	/**
	 * Should the field be translatable?
	 */
	#[Prop(export: false)]
	protected bool $translate = true;

	/**
	 * Should the field be translatable into the given language?
	 *
	 * @since 5.0.0
	 */
	public function isTranslatable(Language $language): bool
	{
		return $this->translate() === true || $language->isDefault() === true;
	}

	public function translate(): bool
	{
		return $this->translate;
	}
}
