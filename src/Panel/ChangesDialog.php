<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\Find;
use Kirby\Content\Changes;
use Kirby\Http\Uri;
use Kirby\Toolkit\Escape;
use Throwable;

class ChangesDialog
{
	public function changes(): Changes
	{
		return App::instance()->site()->changes();
	}

	public function files(): array
	{
		return $this->items($this->changes()->files());
	}

	public function items(Collection $models): array
	{
		$items = [];

		foreach ($models as $model) {
			$items[] = $model->panel()->dropdownOption();
		}

		return $items;
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
		return $this->items($this->changes()->pages());
	}

	public function users(): array
	{
		return $this->items($this->changes()->users());
	}

}
