<?php

namespace Kirby\Toolkit;

/**
 * FluentString - Fluent extension for proper intelisense in IDEs
 *
 * @package   Kirby Toolkit
 * @author    Adam Kiss <iam@adamkiss.com>
 * @link      https://getkirby.com
 * @copyright Adam Kiss
 * @license   https://opensource.org/licenses/MIT
 *
 * TODO: Add @method calls for IDE understanding of the code
 */
class FluentArray extends Fluent
{
	public function value(): array
	{
		return $this->value;
	}
}
