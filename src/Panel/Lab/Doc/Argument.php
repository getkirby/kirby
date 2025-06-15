<?php

namespace Kirby\Panel\Lab\Doc;

use Kirby\Panel\Lab\Doc;

/**
 * Documentation for a single argument for an event, slot or method
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 * @codeCoverageIgnore
 */
class Argument
{
	public function __construct(
		public string $name,
		public string|null $type = null,
		public string|null $description = null,
	) {
		$this->description = Doc::kt($this->description ?? '', true);
	}

	public static function factory(array $data): static
	{
		return new static(
			name:        $data['name'],
			type:        $data['type']['names'][0] ?? null,
			description: $data['description'] ?? null,
		);
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'description' => $this->description,
			'type'        => $this->type,
		];
	}
}
