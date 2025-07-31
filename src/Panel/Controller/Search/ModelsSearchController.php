<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Collection;
use Kirby\Panel\Controller\SearchController;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelsSearchController extends SearchController
{
	public function load(): array
	{
		$models = $this->models();

		if ($this->limit !== null) {
			$models = $models->paginate($this->limit, $this->page);
		}

		return [
			'results'    => $models->values($this->result(...)),
			'pagination' => $models->pagination()?->toArray()
		];
	}

	abstract public function models(): Collection;

	abstract public function result($model): array;
}
