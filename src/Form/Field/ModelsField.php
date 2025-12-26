<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Files;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Pages;
use Kirby\Cms\Pagination;
use Kirby\Cms\Users;
use Kirby\Exception\Exception;
use Kirby\Exception\PermissionException;
use Kirby\Form\Form;
use Kirby\Form\Mixin;
use Kirby\Panel\Collector\ModelsCollector;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Base class for fields displaying a collection of models
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelsField extends DisplayField
{
	use Mixin\Batch;
	use Mixin\EmptyState;
	use Mixin\Max;
	use Mixin\Min;
	use Mixin\SortBy;
	use Mixin\Validation;

	/**
	 * Columns config for `layout: table`
	 */
	protected array|null $columns;

	/**
	 * Enables/disables reverse sorting
	 */
	protected bool|null $flip;

	/**
	 * Image options to control the source and look of preview
	 */
	protected array|false|null $image;

	/**
	 * Optional info text setup. Info text is shown on the right
	 * (lists, cardlets) or below (cards) the title.
	 */
	protected array|string|null $info;

	/**
	 * Section layout.
	 * Available layout methods: `list`, `cardlets`, `cards`, `table`.
	 */
	protected string|null $layout;

	/**
	 * Sets the number of items per page. If there are more items the
	 * pagination navigation will be shown at the bottom of the section.
	 */
	protected int|null $limit;

	/**
	 * Sets the default page for the pagination.
	 * @todo shall this be a blueprint option or are we just using internally the request?
	 */
	protected int|null $page;

	/**
	 * Sets the query to a parent to find items for the list
	 */
	protected string|null $parent;

	/**
	 * Filters by a query. Sorting will be disabled
	 */
	protected string|null $query;

	/**
	 * Whether the raw content file values should be used for the table column previews. Should not be used unless it eases performance issues in your setup introduced with Kirby 4.2
	 *
	 * @todo remove when Form classes have been refactored
	 */
	protected bool|null $rawvalues;

	/**
	 * Enable/disable the search in the sections
	 * @todo Rename to searchable
	 */
	protected bool|null $search;

	/**
	 * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`, `full`
	 */
	protected string|null $size;

	/**
	 * Enables/disables manual sorting
	*/
	protected bool|null $sortable;

	/**
	 * Setup for the main text in the list or cards.
	 * By default this will display the title.
	 */
	protected array|string|null $text;

	/**
	 * Caches
	 */
	protected ModelsCollector $collector;

	public function __construct(
		bool|null $batch = null,
		array|null $columns = null,
		array|string|null $empty = null,
		bool|null $flip = null,
		array|false|null $image = null,
		array|string|null $info = null,
		array|string|null $label = null,
		string|null $layout = null,
		int|null $limit = null,
		int|null $max = null,
		int|null $min = null,
		array|string|null $help = null,
		string|null $name = null,
		int|null $page = null,
		string|null $parent = null,
		string|null $query = null,
		bool|null $rawvalues = null,
		bool|null $search = null,
		string|null $size = null,
		bool|null $sortable = null,
		string|null $sortBy = null,
		array|string|null $text = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			label: $label,
			help:  $help,
			name:  $name,
			when:  $when,
			width: $width
		);

		$this->batch     = $batch;
		$this->columns   = $columns;
		$this->empty     = $empty;
		$this->flip      = $flip;
		$this->image     = $image;
		$this->info      = $info;
		$this->layout    = $layout;
		$this->limit     = $limit;
		$this->max       = $max;
		$this->min       = $min;
		$this->page      = $page;
		$this->parent    = $parent;
		$this->query     = $query;
		$this->rawvalues = $rawvalues;
		$this->search    = $search;
		$this->size      = $size;
		$this->sortable  = $sortable;
		$this->sortBy    = $sortBy;
		$this->text      = $text;
	}

	public function api(): array
	{
		$field = $this;

		return [
			[
				'pattern' => 'models',
				'method'  => 'GET',
				'action'  => fn () => [
					'models'     => $field->items(),
					'pagination' => $field->pagination()
				]
			],
			[
				'pattern' => 'delete',
				'method'  => 'DELETE',
				'action'  => function () use ($field) {
					/**
					 * @var \Kirby\Api\Api $this
					 */
					$ids = (array)$this->requestBody('ids');
					$field->batchDelete($ids);
					return true;
				}
			]
		];
	}

	public function batchDelete(array $ids): void
	{
		if ($ids === []) {
			return;
		}

		// check if batch deletion is allowed
		if ($this->batch() === false) {
			throw new PermissionException(
				message: 'The section does not support batch actions'
			);
		}

		$min = $this->min();

		// check if the section has enough items after the deletion
		if ($this->total() - count($ids) < $min) {
			throw new Exception(
				message: $this->i18n('error.section.' . $this->type() . '.min.' . I18n::form($min), [
					'min'     => $min,
					'section' => $this->label()
				])
			);
		}

		$this->models()->delete($ids);
	}

	abstract public function collector(): ModelsCollector;

	public function columns(): array
	{
		$columns = [];

		if ($this->layout() !== 'table') {
			return [];
		}

		if ($this->image() !== false) {
			$columns['image'] = [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'image',
				'width'  => 'var(--table-row-height)'
			];
		}

		if ($this->text()) {
			$columns['title'] = [
				'label'  => $this->i18n('title'),
				'mobile' => true,
				'type'   => 'url',
			];
		}

		if ($this->info()) {
			$columns['info'] = [
				'label' => $this->i18n('info'),
				'type'  => 'text',
			];
		}

		foreach ($this->columns ?? [] as $columnName => $column) {
			if ($column === true) {
				$column = [];
			}

			if ($column === false) {
				continue;
			}

			// fallback for labels
			$column['label'] ??= Str::label($columnName);

			// make sure to translate labels
			$column['label'] = $this->i18n($column['label']);

			// keep the original column name as id
			$column['id'] = $columnName;

			// add the custom column to the array
			// allowing to extend/overwrite existing columns
			$columns[$columnName] = [
				...$columns[$columnName] ?? [],
				...$column
			];
		}

		return $columns;
	}

	public function columnsWithTypes(): array
	{
		$columns = $this->columns();

		// add the type to the columns for the table layout
		if ($this->layout() === 'table') {
			$blueprint = $this->models()->first()?->blueprint();

			if ($blueprint === null) {
				return $columns;
			}

			foreach ($columns as $columnName => $column) {
				if ($id = $column['id'] ?? null) {
					$columns[$columnName]['type'] ??= $blueprint->field($id)['type'] ?? null;
				}
			}
		}

		return $columns;
	}

	public function columnsValues(array $item, ModelWithContent $model): array
	{
		$item['title'] = [
			// override toSafeString() coming from `$item`
			// because the table cells don't use v-html
			'text' => $model->toString($this->text()),
			'href' => $model->panel()->url(true)
		];

		if ($this->info) {
			// override toSafeString() coming from `$item`
			// because the table cells don't use v-html
			$item['info'] = $model->toString($this->info());
		}

		// if forcing raw values, get those directly from content file
		// TODO: remove once Form classes have been refactored
		// @codeCoverageIgnoreStart
		if ($this->rawvalues() === true) {
			foreach ($this->columns() as $columnName => $column) {
				$item[$columnName] = match (empty($column['value'])) {
					// if column value defined, resolve the query
					false   => $model->toString($column['value']),
					// otherwise use the form value,
					// but don't overwrite columns
					default => $item[$columnName] ?? $model->content()->get($column['id'] ?? $columnName)->value()
				};
			}

			return $item;
		}
		// @codeCoverageIgnoreEnd

		// Use form to get the proper values for the columns
		$form = Form::for($model)->values();

		foreach ($this->columns() as $columnName => $column) {
			$item[$columnName] = match (empty($column['value'])) {
				// if column value defined, resolve the query
				false   => $model->toString($column['value']),
				// otherwise use the form value,
				// but don't overwrite columns
				default => $item[$columnName] ?? $form[$column['id'] ?? $columnName] ?? null
			};
		}

		return $item;
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

	abstract public function items(): array;

	public function layout(): string
	{
		$layouts = ['list', 'cardlets', 'cards', 'table'];
		return in_array($this->layout, $layouts, true) ? $this->layout : 'list';
	}

	public function limit(): int
	{
		return $this->limit ?? 20;
	}

	public function link(): string|null
	{
		$model  = $this->model()->panel()->url(true);
		$parent = $this->parentModel()->panel()->url(true);

		if ($model === $parent) {
			return null;
		}

		return $parent;
	}

	public function models(bool $paginated = false): Files|Pages|Users
	{
		return $this->collector()->models(paginated: $paginated);
	}

	public function page(): int|null
	{
		return $this->kirby()->request()->get('page', $this->page);
	}

	public function pagination(): array
	{
		$pagination = new Pagination([
			'limit' => $this->limit(),
			'page'  => $this->page(),
			'total' => $this->total()
		]);

		return [
			'limit'  => $pagination->limit(),
			'offset' => $pagination->offset(),
			'page'   => $pagination->page(),
			'total'  => $pagination->total(),
		];
	}

	public function parent(): string|null
	{
		return $this->parent;
	}

	public function parentModel(): ModelWithContent
	{
		$parent = $this->parent();

		if (is_string($parent) === true) {
			$query  = $parent;
			$parent = $this->model->query($query);

			if (!$parent) {
				throw new Exception(
					message: 'The parent for the query "' . $query . '" cannot be found in the section "' . $this->name() . '"'
				);
			}

			if ($parent instanceof ModelWithContent === false) {
				throw new Exception(
					message: 'The parent for the field "' . $this->name() . '" has to be a page, site or user object'
				);
			}
		}

		return $parent ?? $this->model();
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'batch'      => $this->batch(),
			'columns'    => $this->columnsWithTypes(),
			'empty'      => $this->empty(),
			'layout'     => $this->layout(),
			'link'       => $this->link(),
			'max'        => $this->max(),
			'min'        => $this->min(),
			'searchable' => $this->search(),
			'size'       => $this->size(),
			'sortable'   => $this->sortable(),
		];
	}

	public function query(): string|null
	{
		return $this->query;
	}

	public function rawvalues(): bool
	{
		return $this->rawvalues ?? false;
	}

	public function search(): bool
	{
		return $this->search ?? false;
	}

	public function searchterm(): string|null
	{
		if ($this->search() === true) {
			return $this->kirby()->request()->get('searchterm');
		}

		return null;
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

		if ($this->query() !== null) {
			return false;
		}

		if ($this->sortBy() !== null) {
			return false;
		}

		if ($this->flip() === true) {
			return false;
		}

		return true;
	}

	public function text(): string
	{
		return $this->i18n($this->text ?? '{{ model.title }}');
	}

	public function total(): int
	{
		return $this->models()->count();
	}

	public function type(): string
	{
		return parent::type() . 'section';
	}

	protected function validations(): array
	{
		return [
			'max',
			'min'
		];
	}
}
