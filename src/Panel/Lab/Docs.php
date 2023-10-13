<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
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

	public static function all(): array
	{
		$docs = A::map(
			Dir::inventory(App::instance()->root('panel') . '/dist/ui')['files'],
			function ($file) {
				$component = 'k-' . Str::camelToKebab(F::name($file['filename']));

				return [
					'image' => [
						'icon' => 'book',
						'back' => 'white',
					],
					'text' => $component,
					'link' => '/lab/docs/' . $component,
				];
			}
		);

		return array_values($docs);
	}

	public function deprecated(): string|null
	{
		return $this->kt($this->json['tags']['deprecated'][0]['description'] ?? '');
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

	public function lab(): string|null
	{
		return $this->json['tags']['lab'][0]['description'] ?? null;
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

		// filter private props
		if (($prop['tags']['access'][0]['description'] ?? null) === 'private') {
			return null;
		}

		// filter unset props
		if (($type = $prop['type']['name'] ?? null) === 'null') {
			return null;
		}

		$default    = $prop['defaultValue']['value'] ?? null;
		$deprecated = $this->kt($prop['tags']['deprecated'][0]['description'] ?? '');

		return [
			'name'        => $prop['name'],
			'type'        => $type,
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
				return '{}';
			}

			// normalize all other defaults from shorthand function
			if (Str::startsWith($default, '() => ')) {
				return Str::after($default, '() => ');
			}

			// normalize all other defaults from longform function
			if (preg_match('/function\(\) {.*return (.*);.*}/si', $default, $matches) === 1) {
				return $matches[1];
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

		// remove empty props
		$props = array_filter($props);

		// always return an array
		return array_values($props);
	}

	public function since(): string|null
	{
		return $this->json['tags']['since'][0]['description'] ?? null;
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
			'deprecated'  => $this->deprecated(),
			'description' => $this->description(),
			'events'      => $this->events(),
			'examples'    => $this->examples(),
			'lab'         => $this->lab(),
			'github'      => $this->github(),
			'methods'     => $this->methods(),
			'props'       => $this->props(),
			'since'       => $this->since(),
			'slots'       => $this->slots(),
		];
	}
}
