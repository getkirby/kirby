<?php

namespace Kirby\Option;

use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\I18n;

/**
 * Option for select fields, radio fields, etc.
 *
 * @package   Kirby Option
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Option
{
	public string|array $text;

	public function __construct(
		public string|int|float|null $value,
		public bool $disabled = false,
		public string|null $icon = null,
		public string|array|null $info = null,
		string|array|null $text = null
	) {
		$this->text = $text ?? ['en' => $this->value];
	}

	public static function factory(string|int|float|array|null $props): static
	{
		if (is_array($props) === false) {
			$props = ['value' => $props];
		}

		// Normalize info to be an array
		if (isset($props['info']) === true) {
			$props['info'] = match (true) {
				is_array($props['info']) => $props['info'],
				$props['info'] === null,
				$props['info'] === false => null,
				default                  => ['en' => $props['info']]
			};
		}

		// Normalize text to be an array
		if (isset($props['text']) === true) {
			$props['text'] = match (true) {
				is_array($props['text']) => $props['text'],
				$props['text'] === null,
				$props['text'] === false => null,
				default                  => ['en' => $props['text']]
			};
		}

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
		$info = I18n::translate($this->info, $this->info);
		$text = I18n::translate($this->text, $this->text);

		return [
			'disabled' => $this->disabled,
			'icon'     => $this->icon,
			'info'     => $info ? $model->toSafeString($info) : $info,
			'text'     => $text ? $model->toSafeString($text) : $text,
			'value'    => $this->value
		];
	}
}
