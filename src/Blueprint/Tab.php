<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;
use Kirby\Form\Fields;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Represents a single tab of a blueprint. Tabs are
 * the top level of the blueprint layout. They hold
 * columns, which hold fields.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Tab
{
	public function __construct(
		protected ModelWithContent $model,
		protected string $name,
		protected array $columns = [],
		protected string|null $icon = null,
		protected array|string|null $label = null,
		protected array $props = []
	) {
	}

	/**
	 * Returns all normalized columns of the tab.
	 *
	 * If a `Fields` collection is passed, the blueprint field
	 * definitions of each column are replaced by the resolved
	 * field props for the Panel.
	 */
	public function columns(Fields|null $fields = null): array
	{
		if ($fields === null) {
			return $this->columns;
		}

		$columns = $this->columns;

		foreach ($columns as $key => $column) {
			$names = array_map(
				'strtolower',
				array_keys($column['fields'] ?? [])
			);

			// only resolve the props of the fields in this tab,
			// as that can be expensive for list fields
			$columns[$key]['fields'] = $fields
				->filter(fn ($field) => in_array($field->name(), $names, true))
				->toProps();
		}

		return $columns;
	}

	/**
	 * Returns a flat list of the lowercase names of all
	 * fields in the tab. The Panel uses this list to count
	 * the unsaved changes per tab.
	 */
	public function fieldNames(): array
	{
		$names = [];

		foreach ($this->columns as $column) {
			foreach (array_keys($column['fields'] ?? []) as $name) {
				$names[] = Str::lower($name);
			}
		}

		return $names;
	}

	/**
	 * Returns the icon of the tab
	 */
	public function icon(): string|null
	{
		return $this->icon;
	}

	/**
	 * Returns the translated label of the tab and
	 * falls back to an automatic label
	 */
	public function label(): string
	{
		$label = I18n::translate($this->label, $this->label);

		if (is_string($label) === true) {
			return $label;
		}

		return Str::label($this->name);
	}

	/**
	 * Returns the Panel link to the tab
	 */
	public function link(): string
	{
		return $this->model->panel()->url(true) . '/?tab=' . $this->name;
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Returns the name of the tab
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Returns the reduced props for the Panel tab bar.
	 * The columns are not included here, as the tab bar
	 * only needs to render a button per tab.
	 */
	public function toButtonProps(): array
	{
		return [
			'fields' => $this->fieldNames(),
			'icon'   => $this->icon(),
			'label'  => $this->label(),
			'link'   => $this->link(),
			'name'   => $this->name(),
		];
	}

	/**
	 * Converts the tab to a plain array with all
	 * columns and fields.
	 *
	 * If a `Fields` collection is passed, the columns
	 * carry the resolved field props for the Panel.
	 */
	public function toViewProps(Fields|null $fields = null): array
	{
		return [
			...$this->props,
			'columns' => $this->columns($fields),
			'icon'    => $this->icon(),
			'label'   => $this->label(),
			'link'    => $this->link(),
			'name'    => $this->name(),
		];
	}
}
