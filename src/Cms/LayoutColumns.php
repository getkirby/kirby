<?php

namespace Kirby\Cms;

/**
 * A collection of layout columns
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Items<\Kirby\Cms\LayoutColumn>
 */
class LayoutColumns extends Items
{
	public const ITEM_CLASS = LayoutColumn::class;

	/**
	 * All registered layout columns methods
	 */
	public static array $methods = [];
}
