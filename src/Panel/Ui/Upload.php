<?php

namespace Kirby\Panel\Ui;

/**
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
		protected string|null $blueprint = null,
		protected int|null $max = null,
		protected bool $multiple = true,
		protected array|bool|null $preview = null,
		protected int|null $sort = null,
	) {
	}

	protected function attributes(): array
	{
		// the upload API expects the blueprint as `template`
		return [
			...$this->attributes,
			'sort'     => $this->sort,
			'template' => $this->blueprint()
		];
	}

	protected function blueprint(): string|null
	{
		return $this->blueprint === 'default' ? null : $this->blueprint;
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
}
