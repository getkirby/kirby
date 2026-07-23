<?php

namespace Kirby\Text;

use Closure;
use Kirby\Exception\BadMethodCallException;

/**
 * Wraps a KirbyTag that is registered in the legacy array form,
 * e.g. tags defined by plugins.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
final class LegacyKirbyTag extends KirbyTag
{
	protected array $props = [];

	public function __construct(
		protected array $definition
	) {
	}

	public function __get(string $name): mixed
	{
		return $this->props[strtolower($name)] ?? null;
	}

	public function __isset(string $name): bool
	{
		return isset($this->props[strtolower($name)]);
	}

	public function __set(string $name, mixed $value): void
	{
		$this->props[strtolower($name)] = $value;
	}

	public function __unset(string $name): void
	{
		unset($this->props[strtolower($name)]);
	}

	/**
	 * Injects the shared state and, as legacy tags cannot receive
	 * their attributes as constructor arguments, applies the
	 * attributes the array definition declares
	 */
	protected function bind(
		string $type,
		string|null $value,
		array $data,
		array $attrs
	): static {
		parent::bind($type, $value, $data, $attrs);

		// only keep attributes that the definition actually declares
		$defined = $this->definition['attr'] ?? [];

		foreach ($attrs as $name => $attrValue) {
			$name = strtolower($name);

			if (in_array($name, $defined, true) === true) {
				$this->$name = $attrValue;
			}
		}

		// make the tag value available under its type name (e.g. $tag->test)
		$this->{strtolower($type)} = $this->value;

		return $this;
	}

	public function render(): string
	{
		$html = $this->definition['html'] ?? null;

		if ($html instanceof Closure) {
			return (string)$html($this);
		}

		throw new BadMethodCallException(
			message: 'Invalid tag render function in tag: ' . $this->type
		);
	}
}
