<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;

class PagesTable extends ModelsTable
{
	public function columns(): array
	{
		$columns = parent::columns();
		$columns['flag'] = $this->flagColumn();

		return $columns;
	}

	/**
	 * @param Page $model
	 */
	public function defaultCells(ModelWithContent $model, array $columns): array
	{
		$cells = parent::defaultCells($model, $columns);
		$cells['status'] = $model->status();

		return $cells;
	}

	public function flagColumn(): array
	{
		return [
			'label'  => ' ',
			'mobile' => true,
			'type'   => 'flag',
			'width'  => 'var(--table-row-height)',
		];
	}

	public function rowPermissions(ModelWithContent $model): array
	{
		$permissions = $model->permissions();

		return [
			'changeStatus' => $permissions->can('changeStatus'),
			'delete'       => $permissions->can('delete'),
			'sort'         => $permissions->can('sort'),
		];
	}
}
