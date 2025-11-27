<?php

namespace Kirby\Form\Mixin;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

trait Converter
{
	/**
	 * The field value will be converted with the selected converter before the value gets saved. Available converters: `lower`, `upper`, `ucfirst`, `slug`
	 */
	protected string|null $converter;

	public function convert(string|null $value): string|null
	{
		if ($value === null) {
			return null;
		}

		if ($converter = $this->converter()) {
			return $this->converters()[$converter]->call($this, $value);
		}

		return $value;
	}

	public function converter(): string|null
	{
		if ($this->converter !== null && array_key_exists($this->converter, $this->converters()) === false) {
			throw new InvalidArgumentException(
				key: 'field.converter.invalid',
				data: ['converter' => $this->converter]
			);
		}

		return $this->converter;
	}

	public function converters(): array
	{
		return [
			'label'   => fn ($value) => Str::label($value),
			'lower'   => fn ($value) => Str::lower($value),
			'slug'    => fn ($value) => Str::slug($value),
			'ucfirst' => fn ($value) => Str::ucfirst($value),
			'upper'   => fn ($value) => Str::upper($value),
		];
	}
}
