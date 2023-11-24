<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Http\Uri;
use Kirby\Toolkit\Escape;
use Throwable;

class ChangesDialog
{
	public function changes(array $ids = []): array
	{
		$kirby     = App::instance();
		$multilang = $kirby->multilang();
		$changes   = [];

		foreach ($ids as $id) {
			try {
				// parse the given ID to extract
				// the path and an optional query
				$uri   = new Uri($id);
				$path  = $uri->path()->toString();
				$query = $uri->query();
				$model = Find::parent($path);
				$item  = $model->panel()->dropdownOption();

				// add the language to each option, if it is included in the query
				// of the given ID and the language actually exists
				if (
					$multilang &&
					$query->language &&
					$language = $kirby->language($query->language)
				) {
					$item['text'] .= ' (' . $language->code() . ')';
					$item['link'] .= '?language=' . $language->code();
				}

				$item['text'] = Escape::html($item['text']);

				$changes[] = $item;
			} catch (Throwable) {
				continue;
			}
		}

		return $changes;
	}

	public function load(): array
	{
		return $this->state();
	}

	public function state(bool $loading = true, array $changes = [])
	{
		return [
			'component' => 'k-changes-dialog',
			'props'     => [
				'changes' => $changes,
				'loading' => $loading
			]
		];
	}

	public function submit(array $ids): array
	{
		return $this->state(false, $this->changes($ids));
	}
}
