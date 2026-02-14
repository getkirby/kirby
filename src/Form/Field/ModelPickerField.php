<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Form\Mixin;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuids;

/**
 * Input class for fields that pick models as value
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelPickerField extends InputField
{
	use Mixin\Max;
	use Mixin\Min;

	/**
	 * The placeholder text if none have been selected yet
	 */
	protected array|string|null $empty;

	/**
	 * Image settings for each item
	 */
	protected array|null $image;

	/**
	 * Info text for each item
	 */
	protected string|null $info;

	/**
	 * Changes the layout of the selected entries.
	 * Available layouts: `list`, `cardlets`, `cards`
	 */
	protected string|null $layout;

	/**
	 * Whether each item should be clickable
	 */
	protected bool|null $link;

	/**
	 * If `false`, only a single one can be selected
	 */
	protected bool|null $multiple;

	/**
	 * Additional picker dialog props { image, layout, size }
	 * @since 6.0.0
	 */
	protected array|null $picker;

	/**
	 * Query for the items to be included in the picker
	 */
	protected string|null $query;

	/**
	 * Enable/disable the search field in the picker
	 */
	protected bool|null $search;

	/**
	 * Layout size for cards: `tiny`, `small`, `medium`, `large`, `huge`, `full`
	 */
	protected string|null $size;

	/**
	 * Whether to store `uuid` or `id` in the content file of the model
	 */
	protected string|null $store;

	/**
	 * Main text for each item
	 */
	protected string|null $text;

	protected array $value = [];

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $empty = null,
		array|string|null $help = null,
		array|null $image = null,
		string|null $info = null,
		array|string|null $label = null,
		string|null $layout = null,
		bool|null $link = null,
		int|null $max = null,
		int|null $min = null,
		bool|null $multiple = null,
		string|null $name = null,
		array|null $picker = null,
		string|null $query = null,
		bool|null $required = null,
		bool|null $search = null,
		string|null $size = null,
		string|null $store = null,
		string|null $text = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			help: $help,
			label: $label,
			name: $name,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->empty    = $empty;
		$this->image    = $image;
		$this->info     = $info;
		$this->layout   = $layout;
		$this->link     = $link;
		$this->max      = $max;
		$this->min      = $min;
		$this->multiple = $multiple;
		$this->picker   = $picker;
		$this->query    = $query;
		$this->search   = $search;
		$this->store    = $store;
		$this->size     = $size;
		$this->text     = $text;
	}

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
							return $model ? $field->toItem($model) : null;
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

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		return parent::fill(value: Data::decode($value, 'yaml'));
	}

	public function image(): array|null
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
			'cards'    => 'cards',
			'cardlets' => 'cardlets',
			default    => 'list'
		};
	}

	public function link(): bool
	{
		return $this->link ?? true;
	}

	public function multiple(): bool
	{
		return $this->multiple ?? true;
	}

	public function picker(): array
	{
		return $this->picker ?? [];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'empty'    => $this->empty(),
			'image'    => $this->image(),
			'info'     => $this->info(),
			'layout'   => $this->layout(),
			'link'     => $this->link(),
			'max'      => $this->max(),
			'min'      => $this->min(),
			'multiple' => $this->multiple(),
			'query'    => $this->query(),
			'search'   => $this->search(),
			'size'     => $this->size(),
			'store'    => $this->store(),
			'text'     => $this->text(),
		];
	}

	public function query(): string|null
	{
		return $this->query;
	}

	public function search(): bool
	{
		return $this->search ?? true;
	}

	public function size(): string
	{
		return $this->size ?? 'auto';
	}

	public function store(ModelWithContent|null $model = null): string
	{
		if (Uuids::enabled() === false) {
			return 'id';
		}

		return Str::lower($this->store ?? 'uuid');
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

	abstract public function toItem(ModelWithContent $model): array;

	abstract public function toModel(string $id): ModelWithContent|null;

	public function toStoredValue(): array
	{
		$ids = [];

		// loop through the IDs from the $value property
		// and convert them to the stored values
		foreach ($this->value as $id) {
			if ($model = $this->toModel($id)) {
				$ids[] = (string)$model->{$this->store()}();
			}
		}

		return $ids;
	}

	public function type(): string
	{
		return str_replace('picker', '', parent::type()) . 's';
	}

	public function validations(): array
	{
		return [
			'max',
			'min'
		];
	}
}
