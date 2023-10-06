<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;

class Examples
{
	protected string $root;

	public function __construct(
		protected string $id,
		string|null $root = null,
		protected array $props = []
	)
	{
		$this->root = $root ?? static::base() . '/' . $this->id;

		if (file_exists($this->root . '/index.php') === true) {
			$this->props = array_merge(
				require $this->root . '/index.php',
				$this->props
			);
		}
	}

	public static function base(): string
	{
		return App::instance()->root('panel') . '/lab';
	}

	public function example(string $id, string|null $tab = null): Example
	{
		return new Example(
			parent: $this,
			id:     $id,
			tab:    $tab
		);
	}

	public function examples(): array
	{
		return A::map(
			Dir::inventory($this->root)['children'],
			fn ($props) => $this->example($props['dirname'])->toArray()
		);
	}

	public static function factory(string $id) {
		return match ($id) {
			'site'  => new static(
				'site',
				App::instance()->root('site') . '/lab',
				[
					'name' => 'Your examples',
					'icon' => 'live'
				]
			),
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

	public static function index(): array
	{
		// all core lab examples from `kirby/panel/lab`
		$examples = A::map(
			Dir::inventory(static::base())['children'],
			fn ($props) => (new static($props['dirname']))->toArray()
		);

		// all custom lab examples from `site/lab`
		$custom = Examples::factory('site')->toArray();

		array_unshift($examples, $custom);

		return $examples;
	}

	public function name(): string
	{
		return $this->props['name'] ?? ucfirst($this->id);
	}

	public function root(): string
	{
		return $this->root;
	}

	public function toArray(): array
	{
		return [
			'name'     => $this->name(),
			'examples' => $this->examples(),
		];
	}
}

