<?php

namespace Kirby\Panel\Lab\Doc;

use Kirby\Panel\Lab\Doc;
use Kirby\Toolkit\A;

/**
 * Documentation for a single Vue emittable event
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
class Event
{
	public function __construct(
		public string $name,
		public string|null $description = null,
		public string|null $deprecated = null,
		public string|null $since = null,
		public array $properties = [],
	) {
		$this->description = Doc::kt($this->description ?? '');
		$this->deprecated  = Doc::kt($this->deprecated ?? '');
	}

	public static function factory(array $data): static
	{
		return new static(
			name:        $data['name'],
			description: $data['description'] ?? null,
			deprecated:  $data['tags']['deprecated'][0]['description'] ?? null,
			since:       $data['tags']['since'][0]['description'] ?? null,
			properties:  A::map(
				$data['properties'] ?? [],
				fn ($property) => Argument::factory($property)
			)
		);
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'description' => $this->description,
			'deprecated'  => $this->deprecated,
			'properties'  => $this->properties,
			'since'       => $this->since,
		];
	}
}
