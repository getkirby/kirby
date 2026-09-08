<?php

namespace Kirby\Blueprint;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;

/**
 * A collection of Tab objects
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends Collection<Tab>
 */
class Tabs extends Collection
{
	protected ModelWithContent $model;

	public function __construct(
		array $tabs = [],
		ModelWithContent|null $model = null
	) {
		$this->model = $model ?? App::instance()->site();

		foreach ($tabs as $name => $tab) {
			$this->__set($name, $tab);
		}
	}

	/**
	 * Internal setter for each object in the collection.
	 * This converts the normalized props of a tab into
	 * a proper Tab object.
	 *
	 * @param Tab|array $tab
	 */
	public function __set(string $name, $tab): void
	{
		if (is_array($tab) === true) {
			$tab = new Tab(
				model: $this->model,
				name: $tab['name'] ?? $name,
				columns: $tab['columns'] ?? [],
				icon: $tab['icon'] ?? null,
				label: $tab['label'] ?? null,
				props: A::without($tab, ['columns', 'icon', 'label', 'link', 'name'])
			);
		}

		parent::__set($tab->name(), $tab);
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Converts the collection to an array and also
	 * does that for every included tab
	 */
	public function toArray(Closure|null $map = null): array
	{
		return A::map($this->data, $map ?? fn ($tab) => $tab->toViewProps());
	}

	/**
	 * Returns the reduced props of each tab
	 * for the Panel tab bar
	 */
	public function toButtonsProps(): array
	{
		return array_values(
			A::map($this->data, fn ($tab) => $tab->toButtonProps())
		);
	}
}
