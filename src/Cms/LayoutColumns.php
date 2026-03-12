<?php

namespace Kirby\Cms;

/**
 * A collection of layout columns
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.5.0
 *
 * @extends \Kirby\Cms\Items<\Kirby\Cms\LayoutColumn>
 */
class LayoutColumns extends Items
{
	public const string ITEM_CLASS = LayoutColumn::class;

	/**
	 * All registered layout columns methods
	 */
	public static array $methods = [];
}
