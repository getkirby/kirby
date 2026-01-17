<?php

namespace Kirby\Option;

use Kirby\Cms\Collection;
use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\A;

/**
 * Collection of possible options for
 * select fields, radio fields, etc.
 *
 * @package   Kirby Option
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @extends \Kirby\Cms\Collection<\Kirby\Option\Option>
 */
class Options extends Collection
{
	public function __construct(array $objects = [])
	{
		foreach ($objects as $object) {
			$this->__set($object->value, $object);
		}
	}

	/**
	 * The Kirby Collection class only shows the key to
	 * avoid huge trees when dumping, but for the options
	 * collections this is really not useful
	 *
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return A::map($this->data, fn ($item) => (array)$item);
	}

	public static function factory(array $items = []): static
	{
		$collection = new static();

		foreach ($items as $key => $option) {
			// convert an associative value => text array into props;
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

	public function render(ModelWithContent $model, bool $safeMode = true): array
	{
		$options = [];

		foreach ($this->data as $key => $option) {
			$options[$key] = $option->render($model, $safeMode);
		}

		return array_values($options);
	}
}
