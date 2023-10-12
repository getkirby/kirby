<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Docs for a single Vue component
 *
 * @internal
 * @since 4.0.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Docs
{
	protected array $json;
	protected App $kirby;

	public function __construct(
		protected string $name
	) {
		$this->kirby = App::instance();
		$this->json  = Data::read($this->file());
	}

	public function description(): string
	{
		return $this->kt($this->json['description'] ?? '');
	}

	public function events(): array
	{
		return A::map(
			$this->json['events'] ?? [],
			fn ($event) => [
				'name'        => $event['name'],
				'description' => $this->kt($event['description'] ?? ''),
			]
		);
	}

	public function examples(): array
	{
		if (empty($this->json['tags']['examples']) === false) {
			return $this->json['tags']['examples'];
		}

		return [];
	}

	public function file(): string
	{
		$name = Str::after($this->name, 'k-');
		$name = Str::kebabToCamel($name);
		return $this->kirby->root('panel') . '/dist/ui/' . $name . '.json';
	}

	public function github(): string
	{
		return 'https://github.com/getkirby/kirby/tree/main/panel/' . $this->json['sourceFile'];
	}

	protected function kt(string $text): string
	{
		return $this->kirby->kirbytext($text, [
			'markdown' => [
				'breaks' => false
			]
		]);
	}

	public function methods(): array
	{
		return A::map(
			$this->json['methods'] ?? [],
			fn ($method) => [
				'name'        => $method['name'],
				'description' => $this->kt($method['description'] ?? ''),
			]
		);
	}

	public function name(): string
	{
		return $this->name;
	}

	public function prop(string|int $key): array|null
	{
		$prop = $this->json['props'][$key];

		if (($prop['tags']['access'][0]['description'] ?? null) === 'private') {
			return null;
		}

		$default    = $prop['defaultValue']['value'] ?? null;
		$deprecated = $prop['tags']['deprecated'][0]['description'] ?? null;

		return [
			'name'        => $prop['name'],
			'type'        => $type = $prop['type']['name'] ?? null,
			'description' => $this->kt($prop['description'] ?? ''),
			'default'     => $this->propDefault($default, $type),
			'deprecated'  => $deprecated,
			'values'      => $prop['values'] ?? null,
		];
	}

	protected function propDefault(
		string|null $default,
		string|null $type
	): string|null {
		if ($default !== null) {
			// normalize empty object default
			if ($default === '() => ({})') {
				$default = '{}';
			}

			// normalize empty array default
			if ($default === '() => []') {
				$default = '[]';
			}

			return $default;
		}

		// if type is boolean primarily and no default
		// value has been set, add `false` as default
		// for clarity
		if (Str::startsWith($type, 'boolean')) {
			return 'false';
		}

		return null;
	}

	public function props(): array
	{
		$props = A::map(
			array_keys($this->json['props'] ?? []),
			fn ($key) => $this->prop($key)
		);

		// always return an array
		return array_values($props);
	}

	public function slots(): array
	{
		return A::map(
			$this->json['slots'] ?? [],
			fn ($slot) => [
				'name'        => $slot['name'],
				'description' => $this->kt($slot['description'] ?? ''),
			]
		);
	}

	public function toArray(): array
	{
		return [
			'component'   => $this->name(),
			'description' => $this->description(),
			'events'      => $this->events(),
			'examples'    => $this->examples(),
			'github'      => $this->github(),
			'methods'     => $this->methods(),
			'props'       => $this->props(),
			'slots'       => $this->slots(),
		];
	}
}
