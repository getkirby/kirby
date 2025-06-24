<?php

namespace Kirby\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Option\Options;
use Kirby\Option\OptionsApi;
use Kirby\Option\OptionsProvider;
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
class FieldOptions
{
	public function __construct(
		/**
		 * The option source, either a fixed collection or
		 * a dynamic provider
		 */
		public Options|OptionsProvider $options = new Options(),

		/**
		 * Whether to escape special HTML characters in
		 * the option text for safe output in the Panel;
		 * only set to `false` if the text is later escaped!
		 */
		public bool $safeMode = true
	) {
	}

	public static function factory(array $props, bool $safeMode = true): static
	{
		$options = match ($props['type']) {
			'api'    => OptionsApi::factory($props),
			'query'  => OptionsQuery::factory($props),
			default  => Options::factory($props['options'] ?? [])
		};

		return new static($options, $safeMode);
	}

	public static function polyfill(array $props = []): array
	{
		if (is_string($props['options'] ?? null) === true) {
			$props['options'] = match ($props['options']) {
				'api' =>
					['type' => 'api'] +
					OptionsApi::polyfill($props['api'] ?? null),

				'query' =>
					['type' => 'query'] +
					OptionsQuery::polyfill($props['query'] ?? null),

				default =>
					['type' => 'query', 'query' => $props['options']]
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

	public function render(ModelWithContent $model): array
	{
		return $this->resolve($model)->render($model);
	}

	public function resolve(ModelWithContent $model): Options
	{
		// resolve OptionsProvider (OptionsApi or OptionsQuery) to Options
		if ($this->options instanceof OptionsProvider) {
			return $this->options->resolve($model, $this->safeMode);
		}

		return $this->options;
	}
}
