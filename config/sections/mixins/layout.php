<?php

use Kirby\Cms\ModelWithContent;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

return [
	'props' => [
		/**
		 * Columns config for `layout: table`
		 */
		'columns' => function (array $columns = null) {
			return $columns ?? [];
		},
		/**
		 * Section layout.
		 * Available layout methods: `list`, `cardlets`, `cards`, `table`.
		 */
		'layout' => function (string $layout = 'list') {
			$layouts = ['list', 'cardlets', 'cards', 'table'];
			return in_array($layout, $layouts) ? $layout : 'list';
		},
		/**
		 * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`, `full`
		 */
		'size' => function (string $size = 'auto') {
			return $size;
		},
	],
	'computed' => [
		'columns' => function () {
			$columns   = [];

			if ($this->layout !== 'table') {
				return [];
			}

			if ($this->image !== false) {
				$columns['image'] = [
					'label'  => ' ',
					'mobile' => true,
					'type'   => 'image',
					'width'  => 'var(--table-row-height)'
				];
			}

			if ($this->text) {
				$columns['title'] = [
					'label'  => I18n::translate('title'),
					'mobile' => true,
					'type'   => 'url',
				];
			}

			if ($this->info) {
				$columns['info'] = [
					'label' => I18n::translate('info'),
					'type'  => 'text',
				];
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

			if ($this->type === 'pages') {
				$columns['flag'] = [
					'label'  => ' ',
					'mobile' => true,
					'type'   => 'flag',
					'width'  => 'var(--table-row-height)',
				];
			}

			return $columns;
		},
	],
	'methods' => [
		'columnsWithTypes' => function () {
			$columns = $this->columns;

			// add the type to the columns for the table layout
			if ($this->layout === 'table') {
				$blueprint = $this->models->first()?->blueprint();

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
		},
		'columnsValues' => function (array $item, ModelWithContent $model) {
			$item['title'] = [
				// override toSafeString() coming from `$item`
				// because the table cells don't use v-html
				'text' => $model->toString($this->text),
				'href' => $model->panel()->url(true)
			];

			if ($this->info) {
				// override toSafeString() coming from `$item`
				// because the table cells don't use v-html
				$item['info'] = $model->toString($this->info);
			}

			// Use form to get the proper values for the columns
			$form = Form::for($model)->values();

			foreach ($this->columns as $columnName => $column) {
				$item[$columnName] = match (empty($column['value'])) {
					// if column value defined, resolve the query
					false   => $model->toString($column['value']),
					// otherwise use the form value,
					// but don't overwrite columns
					default =>
						$item[$columnName] ??
						$form[$column['id'] ?? $columnName] ??
						null,
				};
			}

			return $item;
		}
	],
];
