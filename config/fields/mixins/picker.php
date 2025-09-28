<?php

use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuids;

return [
	'props' => [
		/**
		 * The placeholder text if none have been selected yet
		 */
		'empty' => function ($empty = null) {
			return I18n::translate($empty, $empty);
		},

		/**
		 * Image settings for each item
		 */
		'image' => function ($image = null) {
			return $image;
		},

		/**
		 * Info text for each item
		 */
		'info' => function (string|null $info = null) {
			return $info;
		},

		/**
		 * Whether each item should be clickable
		 */
		'link' => function (bool $link = true) {
			return $link;
		},

		/**
		 * The minimum number of required selected
		 */
		'min' => function (int|null $min = null) {
			return $min;
		},

		/**
		 * The maximum number of allowed selected
		 */
		'max' => function (int|null $max = null) {
			return $max;
		},

		/**
		 * If `false`, only a single one can be selected
		 */
		'multiple' => function (bool $multiple = true) {
			return $multiple;
		},

		/**
		 * Additional picker dialog props
		 * @since 6.0.0
		 * @values { image, layout, size }
		 */
		'picker' => function (array $picker = []) {
			return $picker;
		},

		/**
		 * Query for the items to be included in the picker
		 */
		'query' => function (string|null $query = null) {
			return $query;
		},

		/**
		 * Enable/disable the search field in the picker
		 */
		'search' => function (bool $search = true) {
			return $search;
		},

		/**
		 * Whether to store UUID or ID in the
		 * content file of the model
		 *
		 * @param string $store 'uuid'|'id'
		 */
		'store' => function (string $store = 'uuid') {
			// fall back to ID, if UUIDs globally disabled
			return match (Uuids::enabled()) {
				false   => 'id',
				default => Str::lower($store)
			};
		},

		/**
		 * Main text for each item
		 */
		'text' => function (string|null $text = null) {
			return $text;
		},
	],
	'methods' => [
		'emptyValue' => function () {
			return [];
		},
		'getIdFromArray' => function (array $array) {
			return $array['uuid'] ?? $array['id'] ?? null;
		},
		'itemsFromRequest' => function () {
			$ids    = $this->kirby()->request()->get('items', '');
			$ids    = Str::split($ids);
			$models = $this->toModels($ids);
			return $this->toItems($models);
		},

		'toFormValues' => function ($value = null) {
			$ids = [];

			foreach (Data::decode($value, 'yaml') as $id) {
				if (is_array($id) === true) {
					$id = $this->getIdFromArray($id);
				}

				if ($id !== null && ($model = $this->toModel($id))) {
					$ids[] = $this->toId($model);
				}
			}

			return $ids;
		},
		'toId' => function (ModelWithContent $model) {
			return $model->uuid()?->toString() ?? $model->id();
		},
		'toItem' => function (ModelWithContent $model) {
			$item = $model->panel()->pickerData([
				'image'  => $this->image,
				'info'   => $this->info ?? false,
				'layout' => $this->layout,
				'model'  => $this->model(),
				'text'   => $this->text,
			]);

			return [
				...$item,
				'id' => $item['uuid'] ?? $item['id']
			];
		},
		'toItems' => function (array $models = []) {
			return A::map(
				$models,
				fn ($model) => $this->toItem($model)
			);
		},
		'toModel' => function (string $id) {
			throw new Exception(message: 'toModel() is not implemented on ' . $this->type() . ' field');
		},
		'toModels' => function (array $ids = []) {
			return A::map(
				$ids,
				fn ($id) => $this->toModel($id)
			);
		},
		'toStoredValues' => function ($value = null) {
			return A::map(
				$value ?? [],
				fn (string $id) => (string)$this->toModel($id)?->{$this->store}()
			);
		}
	]
];
