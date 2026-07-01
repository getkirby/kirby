<?php

namespace Kirby\Text\Markdown;

/**
 * Shared, type-keyed store for the data a parse run collects
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Data
{
	/**
	 * @var array<string, array<string, mixed>>
	 */
	protected array $data = [];

	public function get(string $type, string|null $id = null): mixed
	{
		if ($id === null) {
			return $this->data[$type] ?? [];
		}

		return $this->data[$type][$id] ?? null;
	}

	public function reset(): void
	{
		$this->data = [];
	}

	public function set(string $type, string $id, mixed $definition): void
	{
		$this->data[$type][$id] = $definition;
	}
}
