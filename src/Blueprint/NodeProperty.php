<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;

/**
 * Represents a property for a node
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
abstract class NodeProperty
{
	abstract public static function factory($value = null): static|null;

	public function render(ModelWithContent $model)
	{
		return null;
	}
}
