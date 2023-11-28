<?php

namespace Kirby\Blueprint;

/**
 * Custom emoji or icon from the Kirby iconset
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage once blueprint refactoring is done
 * @codeCoverageIgnore
 */
class NodeIcon extends NodeString
{
	public static function field()
	{
		$field = parent::field();
		$field->id = 'icon';
		$field->label->translations = ['en' => 'Icon'];

		return $field;
	}
}
