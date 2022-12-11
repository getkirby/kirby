<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;

/**
 * The text node is translatable
 * and will parse query template strings
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage in 3.10
 * @codeCoverageIgnore
 */
class NodeText extends NodeI18n
{
	public function render(ModelWithContent $model): ?string
	{
		if ($text = parent::render($model)) {
			return $model->toSafeString($text);
		}

		return $text;
	}
}
