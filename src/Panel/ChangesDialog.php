<?php

namespace Kirby\Panel;

use Kirby\Cms\Collection;
use Kirby\Content\Changes;

class ChangesDialog
{
	protected Changes $changes;

	public function __construct()
	{
		$this->changes = new Changes();
	}

	public function files(): array
	{
		return $this->items($this->changes->files());
	}

	public function items(Collection $models): array
	{
		return $models->values(
			fn ($model) => $model->panel()->dropdownOption()
		);
	}

	public function load(): array
	{
		return [
			'component' => 'k-changes-dialog',
			'props'     => [
				'files' => $this->files(),
				'pages' => $this->pages(),
				'users' => $this->users(),
			]
		];
	}

	public function pages(): array
	{
		return $this->items($this->changes->pages());
	}

	public function users(): array
	{
		return $this->items($this->changes->users());
	}
}
