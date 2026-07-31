<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Collection;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Form\Form;
use Kirby\Form\Mixin;
use Kirby\Panel\Collector\ModelsCollector;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Base class for fields that list models
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @template TModel of \Kirby\Cms\ModelWithContent
 */
abstract class ModelListField extends DisplayField
{
	use Mixin\Batch;
	use Mixin\EmptyState;
	use Mixin\Layout;
	use Mixin\Limit;
	use Mixin\Max;
	use Mixin\Sortable;
	use Mixin\SortBy;

	/**
	 * Each list must define the type of models it lists.
	 * It names the entries in the props and the error messages.
	 */
	public const string|null TYPE = null;

	protected ModelsCollector|null $collector = null;

	/**
	 * Columns config for `layout: table`
	 */
	protected array|null $columns;

	/**
	 * Cache for the resolved columns
	 */
	protected array|null $columnsCache = null;

	/**
	 * If `true`, the order of the entries is reversed
	 */
	protected bool|null $flip;

	/**
	 * Image options for each entry or `false` to disable previews
	 */
	protected array|false|null $image;

	/**
	 * Info text shown next to or below the main text of each entry
	 */
	protected array|string|null $info;

	/**
	 * Sets the minimum number of required entries
	 */
	protected int|null $min;

	/**
	 * Sets the default page for the pagination
	 */
	protected int|null $page;

	/**
	 * Query for the parent model that holds the entries
	 */
	protected string|null $parent;

	/**
	 * Filters the entries by a query. Sorting will be disabled
	 */
	protected string|null $query;

	/**
	 * Enables/disables the search field
	 */
	protected bool|null $search;

	/**
	 * The size of the cards for `layout: cards`
	 */
	protected string|null $size;

	/**
	 * Setup for the main text of each entry
	 */
	protected array|string|null $text;

	public function __construct(
		bool|null $batch = null,
		array|null $columns = null,
		array|string|null $empty = null,
		bool|null $flip = null,
		array|string|null $help = null,
		array|false|null $image = null,
		array|string|null $info = null,
		array|string|null $label = null,
		string|null $layout = null,
		int|null $limit = null,
		int|null $max = null,
		int|null $min = null,
		string|null $name = null,
		int|null $page = null,
		string|null $parent = null,
		string|null $query = null,
		bool|null $search = null,
		string|null $size = null,
		bool|null $sortable = null,
		string|null $sortBy = null,
		array|string|null $text = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			help:  $help,
			label: $label,
			name:  $name,
			when:  $when,
			width: $width
		);

		$this->batch    = $batch;
		$this->columns  = $columns;
		$this->empty    = $empty;
		$this->flip     = $flip;
		$this->image    = $image;
		$this->info     = $info;
		$this->layout   = $layout;
		$this->limit    = $limit;
		$this->max      = $max;
		$this->min      = $min;
		$this->page     = $page;
		$this->parent   = $parent;
		$this->query    = $query;
		$this->search   = $search;
		$this->size     = $size;
		$this->sortable = $sortable;
		$this->sortBy   = $sortBy;
		$this->text     = $text;
	}

	/**
	 * The list is not part of the form values, so it has to
	 * refresh itself through its own endpoint whenever the
	 * page, the search or the entries change
	 */
	public function api(): array
	{
		$field = $this;

		return [
			[
				'pattern' => '',
				'method'  => 'GET',
				'action'  => fn (): array => $field->props()
			],
			[
				'pattern' => 'delete',
				'method'  => 'DELETE',
				'action'  => function () use ($field): bool {
					/** @var \Kirby\Api\Api $api */
					$api = $this;

					return $field->deleteSelected(
						ids: $api->requestBody('ids')
					);
				}
			]
		];
	}

	/**
	 * The collector that resolves the entries
	 */
	abstract public function collector(): ModelsCollector;

	/**
	 * Columns for `layout: table`
	 */
	public function columns(): array
	{
		if ($this->columnsCache !== null) {
			return $this->columnsCache;
		}

		if ($this->layout() !== 'table') {
			return $this->columnsCache = [];
		}

		$columns = [];

		if ($this->image() !== false) {
			$columns['image'] = [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'image',
				'width'  => 'var(--table-row-height)'
			];
		}

		// an empty text or info disables the column
		if (($text = $this->text()) !== null && $text !== '') {
			$columns['title'] = [
				'label'  => I18n::translate('title'),
				'mobile' => true,
				'type'   => 'url',
			];
		}

		if (($info = $this->info()) !== null && $info !== '') {
			$columns['info'] = [
				'label' => I18n::translate('info'),
				'type'  => 'text',
			];
		}

		foreach ($this->columns ?? [] as $name => $column) {
			if ($column === false) {
				continue;
			}

			if (is_array($column) === false) {
				$column = [];
			}

			$column['label'] ??= Str::ucfirst($name);
			$column['label']   = $this->i18n($column['label']);
			$column['id']      = $name;

			// allow to extend/overwrite the columns above
			$columns[$name] = [...$columns[$name] ?? [], ...$column];
		}

		return $this->columnsCache = $columns;
	}

	/**
	 * Adds the field types from the model blueprint to the columns
	 */
	public function columnsWithTypes(): array
	{
		$columns   = $this->columns();
		$blueprint = $this->models()->first()?->blueprint();

		if ($blueprint === null) {
			return $columns;
		}

		foreach ($columns as $name => $column) {
			if ($id = $column['id'] ?? null) {
				$columns[$name]['type'] ??= $blueprint->field($id)['type'] ?? null;
			}
		}

		return $columns;
	}

	/**
	 * Adds the column values for a single entry
	 *
	 * @param TModel $model
	 */
	public function columnsValues(array $item, ModelWithContent $model): array
	{
		$item['title'] = [
			// override toSafeString() coming from `$item`
			// because the table cells don't use v-html
			'text' => $model->toString($this->text()),
			'href' => $model->panel()->url(true)
		];

		if ($info = $this->info()) {
			$item['info'] = $model->toString($info);
		}

		$values = Form::for($model)->values();

		foreach ($this->columns() as $name => $column) {
			$item[$name] = match (empty($column['value'])) {
				// if a column value is defined, resolve the query
				false   => $model->toString($column['value']),
				// otherwise use the form value, but don't overwrite columns
				default => $item[$name] ?? $values[$column['id'] ?? $name] ?? null
			};
		}

		return $item;
	}

	/**
	 * Deletes the given entries in batch mode
	 *
	 * @throws \Kirby\Exception\Exception if too few entries would be left
	 * @throws \Kirby\Exception\PermissionException if batch mode is off
	 */
	public function deleteSelected(array $ids): bool
	{
		if ($ids === []) {
			return true;
		}

		if ($this->batch() === false) {
			throw new PermissionException(
				message: 'The field does not support batch actions'
			);
		}

		$min = $this->min() ?? 0;

		if ($this->total() - count($ids) < $min) {
			throw new Exception(
				message: $this->i18n('error.section.' . static::TYPE . '.min.' . I18n::form($min), [
					'min'     => $min,
					'section' => $this->label()
				])
			);
		}

		$this->models()->delete($ids);

		return true;
	}

	/**
	 * The props for all entries of the current page
	 */
	public function data(): array
	{
		$data = [];

		foreach ($this->collector()->models(paginated: true) as $model) {
			/** @var TModel $model */
			$item = $this->toItem($model);

			if ($this->layout() === 'table') {
				$item = $this->columnsValues($item, $model);
			}

			$data[] = $item;
		}

		return $data;
	}

	/**
	 * Errors are only reported for the publish gate. The entries
	 * themselves are not stored in the content of the model.
	 *
	 * @todo switch to dedicated field keys once the sections are gone
	 */
	public function errors(): array
	{
		if ($this->isActive() === false) {
			return [];
		}

		$errors = [];
		$key    = 'error.section.' . static::TYPE;

		if ($this->validateMax() === false) {
			$errors['max'] = $this->i18n($key . '.max.' . I18n::form($this->max), [
				'max'     => $this->max,
				'section' => $this->label()
			]);
		}

		if ($this->validateMin() === false) {
			$errors['min'] = $this->i18n($key . '.min.' . I18n::form($this->min), [
				'min'     => $this->min,
				'section' => $this->label()
			]);
		}

		return $errors;
	}

	public function flip(): bool
	{
		return $this->flip ?? false;
	}

	public function image(): array|false
	{
		return $this->image ?? [];
	}

	public function info(): string|null
	{
		return $this->i18n($this->info);
	}

	/**
	 * Whether the maximum number of entries has been reached
	 */
	public function isFull(): bool
	{
		if ($this->max === null) {
			return false;
		}

		return $this->total() >= $this->max;
	}

	public function layout(): string
	{
		return match ($this->layout) {
			'cardlets' => 'cardlets',
			'cards'    => 'cards',
			'table'    => 'table',
			default    => 'list'
		};
	}

	public function limit(): int
	{
		return $this->limit ?? 20;
	}

	/**
	 * Panel link to the parent, unless it is the field's own model
	 */
	public function link(): string|null
	{
		$model  = $this->model()->panel()->url(true);
		$parent = $this->parentModel()->panel()->url(true);

		if ($model !== $parent) {
			return $parent;
		}

		return null;
	}

	public function min(): int|null
	{
		return $this->min;
	}

	/**
	 * All entries, without pagination
	 */
	public function models(): Collection
	{
		return $this->collector()->models();
	}

	/**
	 * The page from the request, with the `page` prop as fallback
	 */
	public function page(): int|string|null
	{
		return $this->kirby()->request()->get('page', $this->page);
	}

	public function pagination(): array
	{
		$pagination = $this->collector()->pagination();

		return [
			'limit'  => $pagination->limit(),
			'offset' => $pagination->offset(),
			'page'   => $pagination->page(),
			'total'  => $pagination->total()
		];
	}

	/**
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function parentModel(): ModelWithContent
	{
		if ($this->parent === null) {
			return $this->model();
		}

		$parent = $this->model()->query($this->parent);

		if ($parent === null) {
			throw new InvalidArgumentException(
				message: 'The parent for the query "' . $this->parent . '" cannot be found in the field "' . $this->name() . '"'
			);
		}

		if ($parent instanceof ModelWithContent === false) {
			throw new InvalidArgumentException(
				message: 'The parent for the field "' . $this->name() . '" has to be a page, site, file or user object'
			);
		}

		return $parent;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'batch'      => $this->batch(),
			'columns'    => $this->columnsWithTypes(),
			'data'       => $this->data(),
			'empty'      => $this->empty(),
			'layout'     => $this->layout(),
			'min'        => $this->min(),
			'pagination' => $this->pagination(),
			'search'     => $this->search(),
			'size'       => $this->size(),
			'sortable'   => $this->sortable(),
		];
	}

	public function query(): string|null
	{
		return $this->query;
	}

	public function search(): bool
	{
		return $this->search ?? false;
	}

	/**
	 * The search term from the request, if the search is enabled
	 */
	public function searchterm(): string|null
	{
		if ($this->search() === false) {
			return null;
		}

		return $this->kirby()->request()->get('searchterm');
	}

	public function size(): string
	{
		return $this->size ?? 'auto';
	}

	public function sortable(): bool
	{
		if ($this->sortable === false) {
			return false;
		}

		// manual sorting is impossible while the list is filtered
		if (empty($this->searchterm()) === false) {
			return false;
		}

		return $this->query() === null &&
			$this->sortBy() === null &&
			$this->flip() === false;
	}

	public function text(): string|null
	{
		return $this->i18n($this->text);
	}

	/**
	 * Converts a single entry to its item props
	 *
	 * @param TModel $model
	 */
	abstract public function toItem(ModelWithContent $model): array;

	public function total(): int
	{
		return $this->models()->count();
	}

	public function validateMax(): bool
	{
		return $this->max === null || $this->total() <= $this->max;
	}

	public function validateMin(): bool
	{
		return $this->min === null || $this->total() >= $this->min;
	}
}
