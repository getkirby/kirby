<?php

namespace Kirby\Form\Field;

use Kirby\Api\Api;
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
 * @template TModel of ModelWithContent
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
	 * Names the entries in the error messages
	 */
	public const string|null TYPE = null;

	protected ModelsCollector|null $collector = null;

	/**
	 * Columns config for `layout: table`
	 */
	protected array|null $columns;

	protected array|null $columnsCache = null;
	protected array|null $columnFieldsCache = null;

	/**
	 * If `true`, the order of the entries is reversed
	 */
	protected bool|null $flip;

	/**
	 * Image options for each entry, a query string for the preview
	 * image or `false` to disable previews
	 */
	protected array|string|false|null $image;

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
	 * Enables/disables the search
	 */
	protected bool|null $searchable;

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
		array|string|false|null $image = null,
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
		$this->query      = $query;
		$this->searchable = $search;
		$this->size       = $size;
		$this->sortable = $sortable;
		$this->sortBy   = $sortBy;
		$this->text     = $text;
	}

	/**
	 * The list is not part of the form values, so it refreshes
	 * itself through its own endpoint
	 */
	public function api(): array
	{
		$field = $this;

		return [
			[
				'pattern' => '',
				'method'  => 'GET',
				'action'  => fn (): array => $field->state()
			],
			[
				'pattern' => 'delete',
				'method'  => 'DELETE',
				'action'  => function () use ($field): bool {
					/** @var Api $api */
					$api = $this;

					return $field->deleteSelected(
						ids: $api->requestBody('ids')
					);
				}
			]
		];
	}

	abstract public function collector(): ModelsCollector;

	/**
	 * Column definitions for `layout: table`, with the type of
	 * each column resolved from the blueprint of the entries
	 */
	public function columns(): array
	{
		if ($this->columnsCache !== null) {
			return $this->columnsCache;
		}

		$columns = $this->defineColumns();

		if ($columns === []) {
			return $this->columnsCache = [];
		}

		if ($blueprint = $this->models()->first()?->blueprint()) {
			foreach ($columns as $name => $column) {
				if ($id = $column['id'] ?? null) {
					$columns[$name]['type'] ??= $blueprint->field($id)['type'] ?? null;
				}
			}
		}

		return $this->columnsCache = $columns;
	}

	/**
	 * Blueprint field names behind all columns that read their
	 * value from the model instead of from their own query
	 */
	protected function columnFields(): array
	{
		if ($this->columnFieldsCache !== null) {
			return $this->columnFieldsCache;
		}

		$fields = [];

		foreach ($this->columns() as $column) {
			// only columns from the `columns` prop carry an id;
			// the built-in ones are filled by the item itself
			if (($id = $column['id'] ?? null) === null) {
				continue;
			}

			if (($column['value'] ?? '') === '') {
				$fields[] = $id;
			}
		}

		return $this->columnFieldsCache = $fields;
	}

	/**
	 * Values of the column fields for a single entry.
	 *
	 * Only the blueprint fields the columns actually read are
	 * instantiated. Building the full form for every row makes the
	 * table layout scale with the size of the blueprint instead of
	 * the number of columns.
	 *
	 * @param TModel $model
	 */
	protected function columnValues(ModelWithContent $model): array
	{
		$form = new Form(
			fields: array_intersect_key(
				$model->blueprint()->fields(),
				array_flip($this->columnFields())
			),
			model: $model
		);

		// values for columns without a matching field are passed through
		$form->fill(input: $model->content($form->language())->toArray());

		return $form->toFormValues();
	}

	/**
	 * The props for all entries of the current page
	 */
	public function data(): array
	{
		$table = $this->layout() === 'table';
		$data  = [];

		foreach ($this->collector()->models(paginated: true) as $model) {
			/** @var TModel $model */
			$data[] = $table === true ? $this->row($model) : $this->toItem($model);
		}

		return $data;
	}

	/**
	 * Builds the column definitions for `layout: table`.
	 * Subclasses extend this instead of `columns()`,
	 * so that the cache holds the final set.
	 */
	protected function defineColumns(): array
	{
		if ($this->layout() !== 'table') {
			return [];
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

		return $columns;
	}

	/**
	 * @throws Exception if too few entries would be left
	 * @throws PermissionException if batch mode is off
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
				message: $this->i18n('error.field.' . $this->type() . '.min.' . I18n::form($min), [
					'min'   => $min,
					'field' => $this->label()
				])
			);
		}

		$this->models()->delete($ids);

		return true;
	}

	/**
	 * Errors are only reported for the publish gate. The entries
	 * themselves are not stored in the content of the model.
	 */
	public function errors(): array
	{
		if ($this->isActive() === false) {
			return [];
		}

		$errors = [];
		$key    = 'error.field.' . $this->type();

		if ($this->validateMax() === false) {
			$errors['max'] = $this->i18n($key . '.max.' . I18n::form($this->max), [
				'max'   => $this->max,
				'field' => $this->label()
			]);
		}

		if ($this->validateMin() === false) {
			$errors['min'] = $this->i18n($key . '.min.' . I18n::form($this->min), [
				'min'   => $this->min,
				'field' => $this->label()
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
		if (is_string($this->image) === true) {
			return ['query' => $this->image];
		}

		return $this->image ?? [];
	}

	public function info(): string|null
	{
		return $this->i18n($this->info);
	}

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
	 * The page from the request, with the prop as fallback
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
	 * @throws InvalidArgumentException
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
			'empty'      => $this->empty(),
			'initial'    => $this->state(),
			'layout'     => $this->layout(),
			'link'       => $this->link(),
			'max'        => $this->max(),
			'min'        => $this->min(),
			'searchable' => $this->searchable(),
			'size'       => $this->size(),
		];
	}

	public function query(): string|null
	{
		return $this->query;
	}

	/**
	 * The props for a single entry in `layout: table`
	 *
	 * @param TModel $model
	 */
	protected function row(ModelWithContent $model): array
	{
		$item   = $this->toItem($model);
		$values = null;

		// the title cell carries the link to the model
		$item['title'] = [
			'text' => $item['text'],
			'href' => $item['link']
		];

		foreach ($this->columns() as $name => $column) {
			// a column query is resolved against the model
			if (($query = $column['value'] ?? '') !== '') {
				$item[$name] = $model->toString($query);
				continue;
			}

			// otherwise fall back to the form value,
			// without overwriting what the item already provides
			$item[$name] ??= ($values ??= $this->columnValues($model))[$column['id'] ?? $name] ?? null;
		}

		return $item;
	}

	public function searchable(): bool
	{
		return $this->searchable ?? false;
	}

	/**
	 * The search term from the request, if the search is on
	 */
	public function searchterm(): string|null
	{
		if ($this->searchable() === false) {
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


	public function state(): array
	{
		return [
			'columns'    => $this->columns(),
			'models'     => $this->data(),
			'pagination' => $this->pagination(),
			'sortable'   => $this->sortable(),
		];
	}

	public function text(): string|null
	{
		return $this->i18n($this->text);
	}

	/**
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
