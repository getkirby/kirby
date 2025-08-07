<?php

namespace Kirby\Panel\Ui;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class Upload
{
	public function __construct(
		protected string $api,
		protected string|null $accept = null,
		protected array $attributes = [],
		protected int|null $max = null,
		protected bool $multiple = true,
		protected array|bool|null $preview = null,
		protected int|null $sort = null,
		protected string|null $template = null,
	) {
	}

	protected function attributes(): array
	{
		return [
			...$this->attributes,
			'sort'     => $this->sort,
			'template' => $this->template()
		];
	}

	protected function max(): int|null
	{
		return $this->multiple() === false ? 1 : $this->max;
	}

	protected function multiple(): bool
	{
		return $this->multiple === true && ($this->max === null || $this->max > 1);
	}

	public function props(): array
	{
		return [
			'accept'     => $this->accept,
			'api'        => $this->api,
			'attributes' => $this->attributes(),
			'max'        => $this->max(),
			'multiple'   => $this->multiple(),
			'preview'    => $this->preview,
		];
	}

	protected function template(): string|null
	{
		return $this->template === 'default' ? null : $this->template;
	}
}
