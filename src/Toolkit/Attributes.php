<?php

namespace Kirby\Toolkit;

/**
 * The `Attribute` class represents a set of HTML attributes for
 * usage within templates.
 *
 * @package   Kirby Toolkit
 * @author    Fabian Michael <hallo@fabianmichael.de>
 * @link      https://fabianmichael.de
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Attributes
{
	protected array $data = [];
	protected string|null $before = null;
	protected string|null $after = null;

	public static array $prepends = [
		'class',
	];

	protected function __construct(array|self $data = [])
	{
		foreach ($data as $name => $value) {
			$this->set($name, $value);
		}
	}

	public static function from(array|self $data = []): static
	{
		return is_a($data, static::class) ? $data : new static($data);
	}

	public function get(string $name): ?Attribute
	{
		return $this->data[$name] ?? null;
	}

	public function merge(array|self $data = []): static
	{
		$data = is_a($data, static::class) ? $data->data : $data;

		foreach ($data as $name => $value) {
			$this->set($name, $value);
		}

		return $this;
	}

	public function set(string $name, mixed $value): static
	{
		if ($name === 'class') {
			$value = static::resolveClasses($value);
		}

		if (isset($this->data[$name])) {
			$this->data[$name] = $this->data[$name]->merge($value);
		} else {
			$this->data[$name] = in_array($name, static::$prepends)
				? Attribute::prepends($value)
				: Attribute::from($value);
		}

		return $this;
	}

	public function class(array|string $classes): static
	{
		$this->set('class', $classes);

		return $this;
	}

	protected static function resolveClasses(array|string $classes): string
	{
		$value = [];

		foreach (A::wrap($classes) as $key => $class) {
			if (is_numeric($key)) {
				$value[] = $class;
			} elseif ($class) {
				$value[] = $key;
			}
		}

		return implode(' ', array_unique($value));
	}

	public function toArray(): array
	{
		return array_map(fn ($item) => $item->value(), $this->data);
	}

	public function before(string|null $before): static
	{
		$this->before = $before;

		return $this;
	}

	public function after(string|null $after): static
	{
		$this->before = $after;

		return $this;
	}

	public function __toString()
	{
		return (string) Html::attr(
			$this->toArray(),
			before: $this->before,
			after: $this->after
		);
	}
}
