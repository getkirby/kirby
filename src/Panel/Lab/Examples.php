<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;

class Examples
{
	protected array $props = [];
	protected string $root;

	public function __construct(
		protected string $id,
	)
	{
		$this->root = static::base() . '/' . $this->id;

		if (file_exists($this->root . '/index.php') === true) {
			$this->props = require $this->root . '/index.php';
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
		return A::map(
			Dir::inventory(static::base())['children'],
			fn ($props) => (new static($props['dirname']))->toArray()
		);
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

