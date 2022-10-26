<?php

namespace Kirby\Option;

use Kirby\Blueprint\Collection;
use Kirby\Cms\ModelWithContent;

/**
 * Options
 *
 * @package   Kirby Option
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Options extends Collection
{
	public const TYPE = Option::class;

	public function __construct(array $objects = [])
	{
		foreach ($objects as $object) {
			$this->__set($object->value, $object);
		}
	}

	public static function factory(array $items = []): static
	{
		$collection = new static();

		foreach ($items as $key => $option) {
			// skip if option is already an array of option props
			if (
				is_array($option) === false ||
				array_key_exists('value', $option) === false
			) {
				$option = match (true) {
					is_string($key) => ['value' => $key, 'text' => $option],
					default         => ['value' => $option]
				};
			}

			$option = Option::factory($option);
			$collection->__set($option->id(), $option);
		}

		return $collection;
	}

	public function render(ModelWithContent $model): array
	{
		return array_values(parent::render($model));
	}
}
