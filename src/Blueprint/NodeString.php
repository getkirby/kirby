<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;

/**
 * Simple string blueprint node
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage in 3.9
 * @codeCoverageIgnore
 */
class NodeString extends NodeProperty
{
	public function __construct(
		public string $value,
	) {
	}

	public static function factory($value = null): static|null
	{
		if ($value === null) {
			return $value;
		}

		return new static($value);
	}

	public function render(ModelWithContent $model): string|null
	{
		return $this->value;
	}
}
