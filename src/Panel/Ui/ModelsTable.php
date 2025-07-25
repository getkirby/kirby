<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\Blueprint;
use Kirby\Cms\Files;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Pages;
use Kirby\Cms\Users;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

class ModelsTable
{
	public function __construct(
		public Files|Pages|Users $models,
		public array $columns = [],
		public string $component = 'k-table',
		public array|bool|null $image = null,
		public string|null $info = null,
		public bool $rawValues = false,
		public string|null $text = '{{ model.title }}',
	) {
	}

	public function blueprint(): Blueprint|null
	{
		return $this->models->first()?->blueprint();
	}

	public function columns(): array
	{
		$columns = [];

		if ($this->image !== false) {
			$columns['image'] = $this->imageColumn();
		}

		if ($this->text) {
			$columns['title'] = $this->titleColumn();
		}

		if ($this->info) {
			$columns['info'] = $this->infoColumn();
		}

		foreach ($this->columns as $columnName => $column) {
			if ($column === true) {
				$column = [];
			}

			if ($column === false) {
				continue;
			}

			// fallback for labels
			$column['label'] ??= Str::ucfirst($columnName);

			// make sure to translate labels
			$column['label'] = I18n::translate($column['label'], $column['label']);

			// keep the original column name as id
			$column['id'] = $columnName;

			// add the custom column to the array
			// allowing to extend/overwrite existing columns
			$columns[$columnName] = [
				...$columns[$columnName] ?? [],
				...$column
			];
		}

		if ($this->rawValues === false) {
			$columns = $this->injectColumnTypes($columns);
		}

		return $columns;
	}

	public function defaultCells(ModelWithContent $model, array $columns): array
	{
		$cells = [];

		if ($this->image !== false) {
			$cells['image'] = $this->imageCell($model);
		}

		if ($this->text) {
			$cells['title'] = $this->titleCell($model);
		}

		if ($this->info) {
			$cells['info'] = $this->infoCell($model);
		}

		$cells['link']        = $model->panel()->url(true);
		$cells['permissions'] = $this->rowPermissions($model);

		return $cells;
	}

	public function imageCell(ModelWithContent $model): array
	{
		return $model->panel()->image($this->image, 'table');
	}

	public function imageColumn(): array
	{
		return [
			'label'  => ' ',
			'mobile' => true,
			'type'   => 'image',
			'width'  => 'var(--table-row-height)'
		];
	}

	public function infoCell(ModelWithContent $model): string
	{
		return $model->toString($this->info);
	}

	public function infoColumn(): array
	{
		return [
			'label'  => I18n::translate('info'),
			'mobile' => true,
			'type'   => 'text'
		];
	}

	public function injectColumnTypes(array $columns): array
	{
		$blueprint = $this->blueprint();

		if ($blueprint === null) {
			return $columns;
		}

		foreach ($columns as $columnName => $column) {
			$columns[$columnName]['type'] ??= $blueprint->field($column['id'])['type'] ?? null;
		}

		return $columns;
	}

	public function props(): array
	{
		return [
			'columns' => $this->columns(),
			'rows'    => $this->rows(),
		];
	}

	public function queryCell(ModelWithContent $model, string $query): mixed
	{
		return $model->toString($query);
	}

	public function rawCell(ModelWithContent $model, string $columnName, array $column): mixed
	{
		// if a column value query is defined, resolve the query
		if (isset($column['value']) === true) {
			return $this->queryCell($model, $column['value']);
		}

		return $model->content()->get($columnName)->value();
	}

	public function rawRow(ModelWithContent $model, array $columns): array
	{
		$row = [];

		foreach ($columns as $columnName => $column) {
			$row[$columnName] = $this->rawCell($model, $columnName, $column);
		}

		return $row;
	}

	public function row(ModelWithContent $model, array $columns): array
	{
		$row = [];

		if ($this->rawValues === true) {
			$row = $this->rawRow($model, $columns);
		} else {
			$row = $this->typedRow($model, $columns);
		}

		return [
			...$row,
			...$this->defaultCells($model, $columns)
		];
	}

	public function rowPermissions(ModelWithContent $model): array
	{
		$permissions = $model->permissions();

		return [
			'delete' => $permissions->can('delete'),
			'sort'   => $permissions->can('sort'),
		];
	}

	public function rows(): array
	{
		$columns = $this->columns();
		$rows    = [];

		foreach ($this->models as $model) {
			$rows[] = $this->row($model, $columns);
		}

		return $rows;
	}

	public function titleCell(ModelWithContent $model): array
	{
		return [
			'text' => $model->toString($this->text),
			'href' => $model->panel()->url(true)
		];
	}

	public function titleColumn(): array
	{
		return [
			'label' => I18n::translate('title'),
			'mobile' => true,
			'type'   => 'url',
		];
	}

	public function typedCell(ModelWithContent $model, string $columnName, array $column, array $formValues): mixed
	{
		// if a column value query is defined, resolve the query
		if (isset($column['value']) === true) {
			return $this->queryCell($model, $column['value']);
		}

		return $formValues[$columnName] ?? null;
	}

	public function typedRow(ModelWithContent $model, array $columns): array
	{
		$form       = Form::for($model);
		$formValues = $form->toFormValues();
		$row        = [];

		foreach ($columns as $columnName => $column) {
			$row[$columnName] = $this->typedCell($model, $columnName, $column, $formValues);
		}

		return $row;
	}
}
