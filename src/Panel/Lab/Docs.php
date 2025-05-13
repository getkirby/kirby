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
 * @codeCoverageIgnore
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
		// protect against path traversal
		$this->name  = basename($name);
		$this->kirby = App::instance();
		$this->json  = $this->read();
	}

	public static function all(): array
	{
		$dist  = static::root();
		$tmp   = static::root(true);
		$files = Dir::inventory($dist)['files'];

		if (Dir::exists($tmp) === true) {
			$files = [...Dir::inventory($tmp)['files'], ...$files];
		}

		$docs = A::map(
			$files,
			function ($file) {
				$component = 'k-' . Str::camelToKebab(F::name($file['filename']));

				return [
					'image' => [
						'icon' => 'book',
						'back' => 'light-dark(white, var(--color-gray-800))',
					],
					'text' => $component,
					'link' => '/lab/docs/' . $component,
				];
			}
		);

		usort($docs, fn ($a, $b) => $a['text'] <=> $b['text']);

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

	public function docBlock(): string
	{
		return $this->kt($this->json['docsBlocks'][0] ?? '');
	}

	public function events(): array
	{
		$events = A::map(
			$this->json['events'] ?? [],
			fn ($event) => [
				'name'        => $event['name'],
				'description' => $this->kt($event['description'] ?? ''),
				'deprecated'  => $this->kt($event['tags']['deprecated'][0]['description'] ?? ''),
				'since'       => $event['tags']['since'][0]['description'] ?? null,
				'properties'  => A::map(
					$event['properties'] ?? [],
					fn ($property) => [
						'name'        => $property['name'],
						'type'        => $property['type']['names'][0] ?? '',
						'description' => $this->kt($property['description'] ?? '', true),
					]
				),
			]
		);

		usort($events, fn ($a, $b) => $a['name'] <=> $b['name']);

		return $events;
	}

	public function examples(): array
	{
		if (empty($this->json['tags']['examples']) === false) {
			return $this->json['tags']['examples'];
		}

		return [];
	}

	public static function exists(string $name): bool
	{
		return
			file_exists(static::file($name, 'dist')) ||
			file_exists(static::file($name, 'dev'));
	}

	public static function file(string $name, string $context): string
	{
		$root = match ($context) {
			'dev'  => App::instance()->root('panel') . '/tmp',
			'dist' => App::instance()->root('panel') . '/dist/ui',
		};

		$name = Str::after($name, 'k-');
		$name = Str::kebabToCamel($name);
		return $root . '/' . $name . '.json';
	}

	public function github(): string
	{
		return 'https://github.com/getkirby/kirby/tree/main/panel/' . $this->json['sourceFile'];
	}

	public static function isInstalled(): bool
	{
		return Dir::exists(static::root()) === true;
	}

	public function isInternal(): bool
	{
		return $this->json['tags']['internal']['description'] ?? false;
	}

	protected function kt(string $text, bool $inline = false): string
	{
		return $this->kirby->kirbytext($text, [
			'markdown' => [
				'breaks' => false,
				'inline' => $inline,
			]
		]);
	}

	public function lab(): string|null
	{
		$root = $this->kirby->root('panel') . '/lab';

		foreach (glob($root . '/{,*/,*/*/,*/*/*/}index.php', GLOB_BRACE) as $example) {
			$props = require $example;

			if (($props['docs'] ?? null) === $this->name) {
				return Str::before(Str::after($example, $root), 'index.php');
			}
		}

		return null;
	}

	public function methods(): array
	{
		$methods = A::map(
			$this->json['methods'] ?? [],
			fn ($method) => [
				'name'        => $method['name'],
				'description' => $this->kt($method['description'] ?? ''),
				'deprecated'  => $this->kt($method['tags']['deprecated'][0]['description'] ?? ''),
				'since'       => $method['tags']['since'][0]['description'] ?? null,
				'params'      => A::map(
					$method['params'] ?? [],
					fn ($param) => [
						'name'        => $param['name'],
						'type'        => $param['type']['name'] ?? '',
						'description' => $this->kt($param['description'] ?? '', true),
					]
				),
				'returns'     => $method['returns']['type']['name'] ?? null,
			]
		);

		usort($methods, fn ($a, $b) => $a['name'] <=> $b['name']);

		return $methods;
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
			'name'        => Str::camelToKebab($prop['name']),
			'type'        => $type,
			'description' => $this->kt($prop['description'] ?? ''),
			'default'     => $this->propDefault($default, $type),
			'deprecated'  => $deprecated,
			'example'     => $prop['tags']['example'][0]['description'] ?? null,
			'required'    => $prop['required'] ?? false,
			'since'       => $prop['tags']['since'][0]['description'] ?? null,
			'value'       => $prop['tags']['value'][0]['description'] ?? null,
			'values'      => $prop['values'] ?? null,
		];
	}

	protected function propDefault(
		string|null $default,
		string|null $type
	): string|null {
		if ($default !== null) {
			// normalize longform function
			if (preg_match('/function\(\) {.*return (.*);.*}/si', $default, $matches) === 1) {
				return $matches[1];
			}

			// normalize object shorthand function
			if (preg_match('/\(\) => \((.*)\)/si', $default, $matches) === 1) {
				return $matches[1];
			}

			// normalize all other defaults from shorthand function
			if (preg_match('/\(\) => (.*)/si', $default, $matches) === 1) {
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

		usort($props, fn ($a, $b) => $a['name'] <=> $b['name']);

		// always return an array
		return array_values($props);
	}

	protected function read(): array
	{
		$file = static::file($this->name, 'dev');

		if (file_exists($file) === false) {
			$file = static::file($this->name, 'dist');
		}

		return Data::read($file);
	}

	public static function root(bool $tmp = false): string
	{
		return App::instance()->root('panel') . '/' . match ($tmp) {
			true    => 'tmp',
			default => 'dist/ui',
		};
	}

	public function since(): string|null
	{
		return $this->json['tags']['since'][0]['description'] ?? null;
	}

	public function slots(): array
	{
		$slots = A::map(
			$this->json['slots'] ?? [],
			fn ($slot) => [
				'name'        => $slot['name'],
				'description' => $this->kt($slot['description'] ?? ''),
				'deprecated'  => $this->kt($slot['tags']['deprecated'][0]['description'] ?? ''),
				'since'       => $slot['tags']['since'][0]['description'] ?? null,
				'bindings'    => A::map(
					$slot['bindings'] ?? [],
					fn ($binding) => [
						'name'        => $binding['name'],
						'type'        => $binding['type']['name'] ?? '',
						'description' => $this->kt($binding['description'] ?? '', true),
					]
				),
			]
		);

		usort($slots, fn ($a, $b) => $a['name'] <=> $b['name']);

		return $slots;
	}

	public function toArray(): array
	{
		return [
			'component'   => $this->name(),
			'deprecated'  => $this->deprecated(),
			'description' => $this->description(),
			'docBlock'    => $this->docBlock(),
			'events'      => $this->events(),
			'examples'    => $this->examples(),
			'github'      => $this->github(),
			'methods'     => $this->methods(),
			'props'       => $this->props(),
			'since'       => $this->since(),
			'slots'       => $this->slots(),
		];
	}
}
