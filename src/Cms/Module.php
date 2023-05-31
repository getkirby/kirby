<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;

class Module
{
	use HasContent;

	protected string $group;
	protected array $inventory;
	protected int|null $num;
	protected App $kirby;
	protected Page|Site|self $parent;
	protected string $slug;
	protected string $type;

	/**
	 * Magic caller
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		return $this->content()->get($method);
	}

	public function __construct(
		Page|self $parent,
		string $group,
		string $slug,
		string|null $type,
		int|null $num = null
	) {
		$this->group  = $group;
		$this->num    = $num;
		$this->kirby  = $parent->kirby();
		$this->parent = $parent;
		$this->slug   = $slug;

		if ($type === null) {
			$type = $this->inventory()['template'];
		}

		$this->type = $type;
	}

	public function contentFileName(): string
	{
		return $this->type;
	}

	public function group(): string
	{
		return $this->group;
	}

	public function id(): string
	{
		return $this->parent->id() . '/' . $this->group . '/' .  $this->slug;
	}

	public function inventory(): array
	{
		return $this->inventory ??= Dir::inventory(
			$this->root(),
			$this->kirby->contentExtension(),
			$this->kirby->contentIgnore(),
			$this->kirby->multilang()
		);
	}

	public function kirby(): App
	{
		return $this->kirby;
	}

	public function num(): int|null
	{
		return $this->num;
	}

	public function parent(): Module|Page|Site
	{
		return $this->parent;
	}

	public function root(): string
	{
		$root  = $this->parent instanceof Module ? $this->parent->root() : $this->parent->root() . '/_modules';
		$root .= '/' . $this->group;

		if ($this->num !== null) {
			$root .= '/' . $this->num . '_' . $this->slug;
		} else {
			$root .= '/' . $this->slug;
		}

		return $root;
	}

	public function slug(): string
	{
		return $this->slug;
	}

	public function type(): string
	{
		return $this->type;
	}

}
