<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Collection;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Form\Mixin;
use Kirby\Panel\Ui\Item\ProtectedItem;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuids;

/**
 * Input class for fields that pick models as value
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @template TModel of ModelWithContent
 */
abstract class ModelPickerField extends InputField
{
	use Mixin\ItemLayout;
	use Mixin\ItemSize;
	use Mixin\Max;
	use Mixin\Min;

	/**
	 * The placeholder text if none have been selected yet
	 */
	#[Prop]
	protected array|string|null $empty = null;

	/**
	 * Image settings for each item, a query string for the
	 * preview image or `false` to disable previews
	 */
	#[Prop]
	protected array|string|false|null $image = null;

	/**
	 * Info text for each item
	 */
	#[Prop]
	protected string|null $info = null;

	/**
	 * Whether each item should be clickable
	 */
	#[Prop]
	protected bool $link = true;

	/**
	 * If `false`, only a single one can be selected
	 */
	#[Prop]
	protected bool $multiple = true;

	/**
	 * Additional picker dialog props { image, layout, size }
	 * @since 6.0.0
	 */
	#[Prop(export: false)]
	protected array $picker = [];

	/**
	 * Query for the items to be included in the picker
	 */
	#[Prop]
	protected string|null $query = null;

	/**
	 * Enable/disable the search field in the picker
	 */
	#[Prop]
	protected bool $search = true;

	/**
	 * Whether to store `uuid` or `id` in the content file of the model
	 */
	#[Prop(computed: true)]
	protected string $store = 'uuid';

	/**
	 * Main text for each item
	 */
	#[Prop]
	protected string|null $text = null;

	protected array $value = [];

	public function api(): array
	{
		$field = $this;

		return [
			[
				'pattern' => 'items',
				'action'  => function () use ($field): array {
					$ids = $field->kirby()->request()->get('items', '');
					$ids = Str::split($ids);

					return A::map(
						$ids,
						function ($id) use ($field): array|null {
							$model = $field->toModel($id);

							if ($model === null) {
								return null;
							}

							// never disclose a model the current user must not
							// list, but keep its ID to not drop it from the value
							return match ($model->isListable()) {
								true  => $field->toItem($model),
								false => (new ProtectedItem(
									$id,
									layout: $field->layout()
								))->props()
							};
						}
					);
				}
			]
		];
	}

	public function default(): array
	{
		if ($this->default === false) {
			return [];
		}

		return parent::default() ?? [];
	}

	public function empty(): string|null
	{
		return $this->i18n($this->empty);
	}

	public function fill(mixed $value): static
	{
		if ($value instanceof ModelWithContent) {
			$value = [$value];
		}

		if ($value instanceof Collection) {
			$value = $value->values();
		}

		$value = Data::decode($value, 'yaml');

		// resolve models to an ID that `::toModel()` can find
		foreach ($value as $key => $item) {
			if ($item instanceof ModelWithContent) {
				$value[$key] = $item->uuid()?->toString() ?? $item->id();
			}
		}

		return parent::fill(value: $value);
	}

	public function image(): array|string|false|null
	{
		return $this->image;
	}

	public function info(): string|null
	{
		return $this->info;
	}

	public function layout(): string
	{
		return match ($this->layout) {
			'cards', 'cardlets' => $this->layout,
			default             => 'list'
		};
	}

	public function link(): bool
	{
		return $this->link;
	}

	public function multiple(): bool
	{
		return $this->multiple;
	}

	public function picker(): array
	{
		return $this->picker;
	}

	public function query(): string|null
	{
		return $this->query;
	}

	public function search(): bool
	{
		return $this->search;
	}

	/**
	 * @param TModel|null $model
	 */
	public function store(ModelWithContent|null $model = null): string
	{
		if (Uuids::enabled() === false) {
			return 'id';
		}

		return Str::lower($this->store);
	}

	public function text(): string|null
	{
		return $this->text;
	}

	public function toFormValue(): array
	{
		$ids = [];

		// loop through the IDs from the $value property
		// and resolve them to the model to ensure the correct IDs
		foreach ($this->value as $id) {
			if ($model = $this->toModel($id)) {
				$ids[] = $model->uuid()?->toString() ?? $model->id();
			}
		}

		return $ids;
	}

	/**
	 * @param TModel $model
	 */
	abstract public function toItem(ModelWithContent $model): array;

	/**
	 * @return TModel|null
	 */
	abstract public function toModel(string $id): ModelWithContent|null;

	public function toStoredValue(): array
	{
		$ids = [];

		// loop through the IDs from the $value property
		// and convert them to the stored values
		foreach ($this->value as $id) {
			if ($model = $this->toModel($id)) {
				$ids[] = (string)$model->{$this->store($model)}();
			}
		}

		return $ids;
	}

	public function validations(): array
	{
		return [
			'max',
			'min'
		];
	}
}
