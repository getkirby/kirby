<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Panel\Lab\Doc\Event;
use Kirby\Panel\Lab\Doc\Method;
use Kirby\Panel\Lab\Doc\Prop;
use Kirby\Panel\Lab\Doc\Slot;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Documentation for a single Vue component
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
class Doc
{
	protected array $data;

	public function __construct(
		public string $name,
		public string $source,
		public string|null $description = null,
		public string|null $deprecated = null,
		public string|null $docBlock = null,
		public array $events = [],
		public array $examples = [],
		public bool $isUnstable = false,
		public array $methods = [],
		public array $props = [],
		public string|null $since = null,
		public array $slots = [],
	) {
		$this->description = Doc::kt($this->description ?? '');
		$this->deprecated  = Doc::kt($this->deprecated ?? '');
		$this->docBlock    = Doc::kt($this->docBlock ?? '');
	}

	/**
	 * Checks if a documentation file exists for the component
	 */
	public static function exists(string $name): bool
	{
		return
			file_exists(static::file($name, 'dist')) ||
			file_exists(static::file($name, 'dev'));
	}

	public static function factory(string $name): static|null
	{
		// protect against path traversal
		$name = basename($name);

		// read data
		$file = static::file($name, 'dev');

		if (file_exists($file) === false) {
			$file = static::file($name, 'dist');
		}

		$data = Data::read($file);

		// filter internal components
		if (isset($data['tags']['internal']) === true) {
			return null;
		}

		// helper function for gathering parts
		$gather = function (string $part, string $class) use ($data) {
			$parts = A::map(
				$data[$part] ?? [],
				fn ($x) => $class::factory($x)?->toArray()
			);

			$parts = array_filter($parts);
			usort($parts, fn ($a, $b) => $a['name'] <=> $b['name']);

			return $parts;
		};

		return new static(
			name:        $name,
			source:      $data['sourceFile'],
			description: $data['description'] ?? null,
			deprecated:  $data['tags']['deprecated'][0]['description'] ?? null,
			docBlock:    $data['docsBlocks'][0] ?? null,
			examples:    $data['tags']['examples'] ?? [],
			events:      $gather('events', Event::class),
			isUnstable:  isset($data['tags']['unstable']) === true,
			methods:     $gather('methods', Method::class),
			props:       $gather('props', Prop::class),
			since:       $data['tags']['since'][0]['description'] ?? null,
			slots:       $gather('slots', Slot::class)
		);
	}

	/**
	 * Returns the path to the documentation file for the component
	 */
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

	/**
	 * Helper to resolve KirbyText
	 */
	public static function kt(string $text, bool $inline = false): string
	{
		return App::instance()->kirbytext($text, [
			'markdown' => [
				'breaks' => false,
				'inline' => $inline,
			]
		]);
	}

	/**
	 * Returns the path to the Lab examples, if available
	 */
	public function lab(): string|null
	{
		$root = App::instance()->root('panel') . '/lab';

		foreach (glob($root . '/{,*/,*/*/,*/*/*/}index.php', GLOB_BRACE) as $example) {
			$props = require $example;

			if (($props['docs'] ?? null) === $this->name) {
				return Str::before(Str::after($example, $root), 'index.php');
			}
		}

		return null;
	}

	public function source(): string
	{
		return 'https://github.com/getkirby/kirby/tree/main/panel/' . $this->source;
	}

	/**
	 * Returns the data for this documentation
	 */
	public function toArray(): array
	{
		return [
			'component'   => $this->name,
			'deprecated'  => $this->deprecated,
			'description' => $this->description,
			'docBlock'    => $this->docBlock,
			'events'      => $this->events,
			'examples'    => $this->examples,
			'isUnstable'  => $this->isUnstable,
			'methods'     => $this->methods,
			'props'       => $this->props,
			'since'       => $this->since,
			'slots'       => $this->slots,
			'source'      => $this->source(),
		];
	}

	/**
	 * Returns the information to display as
	 * entry in a collection (e.g. on the Lab index view)
	 */
	public function toItem(): array
	{
		return [
			'image' => [
				'icon' => $this->isUnstable ? 'lab' : 'book',
				'back' => 'light-dark(white, var(--color-gray-800))',
			],
			'text' => $this->name,
			'link' => '/lab/docs/' . $this->name,
		];
	}
}
