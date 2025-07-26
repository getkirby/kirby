<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuids;

/**
 * Picker functionality for various content types
 *
 * @since 6.0.0
 */
trait Picker
{
	use EmptyState;
	use Max;
	use Min;

	/**
	 * Image settings for each item
	 */
	protected mixed $image;

	/**
	 * Info text for each item
	 */
	protected string|null $info;

	/**
	 * Whether each item should be clickable
	 */
	protected bool $link;

	/**
	 * If `false`, only a single one can be selected
	 */
	protected bool $multiple;

	/**
	 * Query for the items to be included in the picker
	 */
	protected string|null $query;

	/**
	 * Enable/disable the search field in the picker
	 */
	protected bool $search;

	/**
	 * Whether to store UUID or ID in the content file of the model
	 */
	protected string $store;

	/**
	 * Main text for each item
	 */
	protected string|null $text;

	public function image(): mixed
	{
		return $this->image;
	}

	public function info(): string|null
	{
		return $this->info;
	}

	public function link(): bool
	{
		return $this->link;
	}

	public function multiple(): bool
	{
		return $this->multiple;
	}

	public function query(): string|null
	{
		return $this->query;
	}

	public function search(): bool
	{
		return $this->search;
	}

	public function store(): string
	{
		return $this->store;
	}

	public function text(): string|null
	{
		return $this->text;
	}

	protected function setImage(mixed $image = null): void
	{
		$this->image = $image;
	}

	protected function setInfo(string|null $info = null): void
	{
		$this->info = $info;
	}

	protected function setLink(bool $link = true): void
	{
		$this->link = $link;
	}

	protected function setMultiple(bool $multiple = true): void
	{
		$this->multiple = $multiple;
	}

	protected function setQuery(string|null $query = null): void
	{
		$this->query = $query;
	}

	protected function setSearch(bool $search = true): void
	{
		$this->search = $search;
	}

	protected function setStore(string $store = 'uuid'): void
	{
		// fall back to ID, if UUIDs globally disabled
		$this->store = match (Uuids::enabled()) {
			false   => 'id',
			default => Str::lower($store)
		};
	}

	protected function setText(string|null $text = null): void
	{
		$this->text = $text;
	}
}
