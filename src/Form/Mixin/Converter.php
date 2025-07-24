<?php

namespace Kirby\Form\Mixin;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

trait Converter
{
	/**
	 * The converter to apply to the field value
	 */
	protected string|null $converter;

	public function converter(): string|null
	{
		return $this->converter;
	}

	public function converters(): array
	{
		return [
			'lower'   => function ($value) {
				return Str::lower($value);
			},
			'slug'    => function ($value) {
				return Str::slug($value);
			},
			'ucfirst' => function ($value) {
				return Str::ucfirst($value);
			},
			'upper'   => function ($value) {
				return Str::upper($value);
			},
		];
	}

	public function convert(mixed $value): mixed
	{
		if ($this->converter() === null) {
			return $value;
		}

		$converter = $this->converters()[$this->converter()];

		if (is_array($value) === true) {
			return array_map($converter, $value);
		}

		return $converter(trim($value ?? ''));
	}

	protected function setConverter(string|null $converter = null): void
	{
		if (
			$converter !== null &&
			array_key_exists($converter, $this->converters()) === false
		) {
			throw new InvalidArgumentException(
				key: 'field.converter.invalid',
				data: ['converter' => $converter]
			);
		}

		$this->converter = $converter;
	}
}
