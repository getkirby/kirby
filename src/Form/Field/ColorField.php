<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\FieldOptions;
use Kirby\Form\Mixin;
use Kirby\Toolkit\A;

/**
 * Color field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ColorField extends OptionField
{
	use Mixin\Icon;
	use Mixin\Placeholder;

	/**
	 * Whether to allow alpha transparency in the color
	 */
	protected bool|null $alpha;

	/**
	 * The CSS format (hex, rgb, hsl) to display and store the value
	 */
	protected string|null $format;

	/**
	 * Change mode to disable the color picker (`input`) or to only
	 * show the `options` as toggles
	 */
	protected string|null $mode;

	public function __construct(
		bool|null $alpha = null,
		string|null $format = null,
		string|null $icon = null,
		string|null $mode = null,
		array|string|null $placeholder = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->alpha       = $alpha;
		$this->format      = $format;
		$this->icon        = $icon;
		$this->mode        = $mode;
		$this->placeholder = $placeholder;
	}

	public function alpha(): bool
	{
		return $this->alpha ?? false;
	}

	protected function fetchOptions(): array
	{
		// resolve options to support manual arrays
		// alongside api and query options
		$props   = FieldOptions::polyfill(['options' => $this->options ?? []]);
		$options = FieldOptions::factory([
			'text'  => '{{ item.value }}',
			'value' => '{{ item.key }}',
			...$props['options'] ?? []
		]);

		$options = $options->render($this->model());

		if ($options === []) {
			return [];
		}

		if (
			is_numeric($options[0]['value']) ||
			$options[0]['value'] === (string)$options[0]['text']
		) {
			// simple array of values
			// or value=text (from Options class).
			// the text is trusted HTML, the value must stay a plain string
			$options = A::map($options, fn ($option) => [
				'value' => (string)$option['text']
			]);

		} else {
			$options = A::map($options, fn ($option) => [
				'value' => $option['value'],
				'text'  => $option['text']
			]);
		}

		return $options;
	}

	public function format(): string
	{
		if ($this->format === null) {
			return 'hex';
		}

		if (in_array($this->format, ['hex', 'hsl', 'rgb'], true) === true) {
			return $this->format;
		}

		throw new InvalidArgumentException(
			message: 'Invalid format "' . $this->format . '" in color field' . ($this->name ? ' "' . $this->name . '"' : null)
		);
	}

	public static function isColor(string $value): bool
	{
		return
			static::isHex($value) === true ||
			static::isRgb($value) === true ||
			static::isHsl($value) === true;
	}

	public static function isHex(string $value): bool
	{
		return preg_match('/^#([\da-f]{3,4}){1,2}$/i', $value) === 1;
	}

	public static function isHsl(string $value): bool
	{
		return preg_match('/^hsla?\(\s*(\d{1,3}\.?\d*)(deg|rad|grad|turn)?(?:,|\s)+(\d{1,3})%(?:,|\s)+(\d{1,3})%(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i', $value) === 1;
	}

	public static function isRgb(string $value): bool
	{
		return preg_match('/^rgba?\(\s*(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i', $value) === 1;
	}

	public function mode(): string
	{
		if ($this->mode === null) {
			return 'picker';
		}

		if (in_array($this->mode, ['picker', 'input', 'options'], true) === true) {
			return $this->mode;
		}

		throw new InvalidArgumentException(
			message: 'Invalid mode "' . $this->mode . '" in color field' . ($this->name ? ' "' . $this->name . '"' : null)
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'alpha'       => $this->alpha(),
			'format'      => $this->format(),
			'icon'        => $this->icon(),
			'mode'        => $this->mode(),
			'placeholder' => $this->placeholder(),
		];
	}

	protected function validations(): array
	{
		return [
			'color' => fn ($value) => $this->validateColor($value)
		];
	}

	protected function validateColor(string|null $value): void
	{
		if ($this->isEmptyValue($value) === true) {
			return;
		}

		$format = $this->format();
		$method = 'is' . $format;

		if (static::$method($value) === false) {
			throw new InvalidArgumentException(
				key: 'validation.color',
				data: ['format' => $format]
			);
		}
	}
}
