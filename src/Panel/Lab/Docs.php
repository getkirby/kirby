<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
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
	public function __construct(
		protected array $json
	) {
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

	protected function kt(string $text): string
	{
		return App::instance()->kirbytext($text, [
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
		return 'k-' . Str::camelToKebab($this->json['displayName']);
	}

	public function prop(string|int $key): array|null
	{
		$prop = $this->json['props'][$key];

		if (($prop['tags']['access'][0]['description'] ?? null) === 'private') {
			return null;
		}

		return [
			'name'        => $prop['name'],
			'type'        => $prop['type']['name'] ?? null,
			'description' => $this->kt($prop['description'] ?? ''),
			'default'     => $this->propDefault($prop),
			'deprecated'  => $prop['tags']['deprecated'][0]['description'] ?? null,
		];
	}

	protected function propDefault(array $prop): string|null
	{
		if ($default = $prop['defaultValue']['value'] ?? null) {
			// normalize empty object default
			if ($default === '() => ({})') {
				$default = '{}';
			}

			return $default;
		}

		// if type is boolean primarily and no default
		// value has been set, add `false` as default
		// for clarity
		$type = $prop['type']['name'] ?? null;
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
			'methods'     => $this->methods(),
			'props'       => $this->props(),
			'slots'       => $this->slots(),
		];
	}
}
