<?php

namespace Kirby\Field;

use Kirby\Blueprint\Node;
use Kirby\Cms\ModelWithContent;
use Kirby\Option\Options;
use Kirby\Option\OptionsApi;
use Kirby\Option\OptionsQuery;

/**
 * Foundation for radio and select
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class FieldOptions extends Node
{
	public function __construct(
		public Options|OptionsApi|OptionsQuery|null $options = null
	) {
	}

	public function defaults(): static
	{
		$this->options ??= new Options();

		return parent::defaults();
	}

	public static function factory(array $props): static
	{
		$options = match ($props['type']) {
			'api'    => OptionsApi::factory($props),
			'query'  => OptionsQuery::factory($props),
			default  => Options::factory($props['options'] ?? [])
		};

		return new static($options);
	}

	public static function polyfill(array $props = []): array
	{
		if (is_string($props['options'] ?? null) === true) {
			$props['options'] = match ($props['options']) {
				'api'   => ['type' => 'api'] +
						   OptionsApi::polyfill($props['api'] ?? null),

				'query' => ['type' => 'query'] +
						   OptionsQuery::polyfill($props['query'] ?? null),

				default  => [ 'type' => 'query', 'query' => $props['options']]
			};
		}

		unset($props['api'], $props['query']);

		if (($props['options']['type'] ?? null) !== null) {
			return $props;
		}

		if (($props['options'] ?? null) !== null) {
			$props['options'] = [
				'type'    => 'array',
				'options' => $props['options']
			];
		}

		return $props;
	}

	public function resolve(ModelWithContent $model): Options
	{
		// apply default values
		$this->defaults();

		// already Options, return
		if (is_a($this->options, Options::class) === true) {
			return $this->options;
		}

		// resolve OptionsApi or OptionsQuery to Options
		return $this->options = $this->options->resolve($model);
	}

	public function render(ModelWithContent $model): array
	{
		return $this->resolve($model)->render($model);
	}
}
