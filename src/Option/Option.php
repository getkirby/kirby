<?php

namespace Kirby\Option;

use Kirby\Blueprint\Factory;
use Kirby\Blueprint\NodeIcon;
use Kirby\Blueprint\NodeText;
use Kirby\Cms\ModelWithContent;

/**
 * Option for select fields, radio fields, etc
 *
 * @package   Kirby Option
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Option
{
	public function __construct(
		public float|int|string|null $value,
		public bool $disabled = false,
		public NodeIcon|null $icon = null,
		public NodeText|null $info = null,
		public NodeText|null $text = null
	) {
		$this->text ??= new NodeText(['en' => $this->value]);
	}

	public static function factory(float|int|string|null|array $props): static
	{
		if (is_array($props) === false) {
			$props = ['value' => $props];
		}

		$props = Factory::apply($props, [
			'icon' => NodeIcon::class,
			'info' => NodeText::class,
			'text' => NodeText::class
		]);

		return new static(...$props);
	}

	public function id(): string|int|float
	{
		return $this->value ?? '';
	}

	/**
	 * Renders all data for the option
	 */
	public function render(ModelWithContent $model): array
	{
		return [
			'disabled' => $this->disabled,
			'icon'     => $this->icon?->render($model),
			'info'     => $this->info?->render($model),
			'text'     => $this->text?->render($model),
			'value'    => $this->value
		];
	}
}
