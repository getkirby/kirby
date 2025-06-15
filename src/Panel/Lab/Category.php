<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Category of lab examples located in
 * `kirby/panel/lab` and `site/lab`.
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @internal
 * @codeCoverageIgnore
 */
class Category
{
	protected string $root;

	public function __construct(
		protected string $id,
		string|null $root = null,
		protected array $props = []
	) {
		$this->root = $root ?? static::base() . '/' . $this->id;

		if (F::exists($this->root . '/index.php', static::base()) === true) {
			$this->props = array_merge(
				require $this->root . '/index.php',
				$this->props
			);
		}
	}

	public static function all(): array
	{
		// all core lab examples from `kirby/panel/lab`
		$examples = A::map(
			Dir::inventory(static::base())['children'],
			fn ($props) => (new static($props['dirname']))->toArray()
		);

		// all custom lab examples from `site/lab`
		$custom = static::factory('site')->toArray();

		array_push($examples, $custom);

		return $examples;
	}

	public static function base(): string
	{
		return App::instance()->root('panel') . '/lab';
	}

	public function example(string $id, string|null $tab = null): Example
	{
		return new Example(parent: $this, id: $id, tab: $tab);
	}

	public function examples(): array
	{
		return A::map(
			Dir::inventory($this->root)['children'],
			fn ($props) => $this->example($props['dirname'])->toArray()
		);
	}

	public static function factory(string $id)
	{
		return match ($id) {
			'site'  => static::site(),
			default => new static($id)
		};
	}

	public function icon(): string
	{
		return $this->props['icon'] ?? 'palette';
	}

	public function id(): string
	{
		return $this->id;
	}

	public static function isInstalled(): bool
	{
		return Dir::exists(static::base()) === true;
	}

	public function name(): string
	{
		return $this->props['name'] ?? ucfirst($this->id);
	}

	public function root(): string
	{
		return $this->root;
	}

	public static function site(): static
	{
		return new static(
			'site',
			App::instance()->root('site') . '/lab',
			[
				'name' => 'Your examples',
				'icon' => 'live'
			]
		);
	}

	public function toArray(): array
	{
		return [
			'name'     => $this->name(),
			'examples' => $this->examples(),
			'icon'     => $this->icon(),
			'path'     => Str::after(
				$this->root(),
				App::instance()->root('index')
			),
		];
	}
}
