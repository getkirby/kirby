<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Collection;
use Kirby\Panel\Controller\SearchController;
use Kirby\Panel\Ui\Item\ModelItem;
use Override;

/**
 * Controls the search requests for a model type
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelsSearchController extends SearchController
{
	#[Override]
	public function load(): array
	{
		$models = $this->models();

		if ($this->limit !== null) {
			$models = $models->paginate($this->limit, $this->page);
		}

		return [
			'pagination' => $models->pagination()?->toArray(),
			'results'    => $models->values(
				fn ($model) => $this->item($model)->props()
			),
		];
	}

	abstract public function item($model): ModelItem;

	abstract public function models(): Collection;
}
