<?php

use Kirby\Cms\ModelWithContent;
use Kirby\Form\Form;
use Kirby\Panel\Ui\ModelsTable;
use Kirby\Panel\Ui\PagesTable;

return [
	'props' => [
		/**
		 * Columns config for `layout: table`
		 */
		'columns' => function (array|null $columns = null) {
			return $columns ?? [];
		},
		/**
		 * Section layout.
		 * Available layout methods: `list`, `cardlets`, `cards`, `table`.
		 */
		'layout' => function (string $layout = 'list') {
			$layouts = ['list', 'cardlets', 'cards', 'table'];
			return in_array($layout, $layouts, true) ? $layout : 'list';
		},
		/**
		 * Whether the raw content file values should be used for the table column previews. Should not be used unless it eases performance issues in your setup introduced with Kirby 4.2
		 *
		 * @todo remove when Form classes have been refactored
		 */
		'rawvalues' => function (bool $rawvalues = false) {
			return $rawvalues;
		},
		/**
		 * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`, `full`
		 */
		'size' => function (string $size = 'auto') {
			return $size;
		},
	],
	'methods' => [
		'columns' => function () {
			if ($this->layout !== 'table') {
				return [];
			}

			return $this->table()->columns();
		},
		'columnsWithTypes' => function () {
			return $this->table()->columns();
		},
		'columnsValues' => function (array $item, ModelWithContent $model) {
			return $this->table()->row($model, $this->columns());
		},
		'table' => function () {
			$className = $this->type === 'pages' ? PagesTable::class : ModelsTable::class;

			return $this->table ??= new $className(
				models: $this->models(),
				columns: $this->columns,
				image: $this->image,
				info: $this->info,
				rawValues: $this->rawvalues,
				text: $this->text,
			);
		},
	],
];
