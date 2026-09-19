<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Mixin;
use Kirby\Toolkit\A;
use Kirby\Toolkit\HtmlString;

/**
 * Toggle Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ToggleField extends InputField
{
	use Mixin\After;
	use Mixin\Before;
	use Mixin\Icon;

	/**
	 * Sets the text next to the toggle. The text can be a string
	 * or an array of two options. The first one is the negative text
	 * and the second one the positive. The text will automatically
	 * switch when the toggle is triggered.
	 */
	protected array|string|null $text;

	protected bool|null $value = null;

	public function __construct(
		array|string|null $after = null,
		array|string|null $before = null,
		string|null $icon = null,
		array|string|null $text = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->after  = $after;
		$this->before = $before;
		$this->icon   = $icon;
		$this->text   = $text;
	}

	public function fill(mixed $value): static
	{
		return parent::fill(
			value: static::toBool($value)
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'after'  => $this->after(),
			'before' => $this->before(),
			'icon'   => $this->icon(),
			'text'   => $this->text(),
		];
	}

	public function text(): array|HtmlString|null
	{
		$text = $this->text;

		if ($text === null || $text === [] || $text === '') {
			return null;
		}

		if (
			is_string($text) === true ||
			A::isAssociative($text) === true
		) {
			return new HtmlString($this->stringTemplateI18n($text));
		}

		return A::map(
			$text,
			fn ($value) => new HtmlString($this->stringTemplateI18n($value))
		);
	}

	public function toFormValue(): bool
	{
		return $this->value ?? $this->default() ?? false;
	}

	public static function toBool(mixed $value): bool
	{
		return in_array($value, [true, 'true', 1, '1', 'on'], true) === true;
	}

	protected function validations(): array
	{
		return [
			'boolean',
			'required' => fn () => $this->validateRequired()
		];
	}

	protected function validateRequired(): void
	{
		if ($this->isRequired() === false) {
			return;
		}

		if (
			$this->value === false ||
			$this->isEmptyValue($this->value) === true
		) {
			throw new InvalidArgumentException(
				message: $this->i18n('field.required')
			);
		}
	}
}
