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
		protected array $definition,
		string $type,
		string|null $value = null,
		array $attrs = [],
		array $data = []
	) {
		parent::__construct($type, $value, $attrs, $data);

		// make the tag value available under its type name (e.g. $tag->test)
		$this->{strtolower($type)} = $this->value;
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

	protected function definedAttrs(): array
	{
		return $this->definition['attr'] ?? [];
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
