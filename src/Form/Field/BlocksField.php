<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks as BlocksCollection;
use Kirby\Cms\Fieldset;
use Kirby\Cms\Fieldsets;
use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Max;
use Kirby\Form\Mixin\Min;
use Kirby\Form\Mixin\Pretty;
use Kirby\Panel\Controller\Dialog\FieldDialogController;
use Kirby\Panel\Controller\Drawer\FieldDrawerController;
use Kirby\Toolkit\Str;
use Throwable;

class BlocksField extends InputField
{
	use EmptyState;
	use Max;
	use Min;
	use Pretty;

	/**
	 * Defines the allowed block types in the blocks field. See below.
	 */
	protected array|null $fieldsets;

	/**
	 * Cache for the Fieldsets collection
	 */
	protected Fieldsets $fieldsetsCollection;

	/**
	 * Cache for all Form instances for each fieldset
	 */
	protected array $forms;

	/**
	 * Group name to identify all block fields that can share blocks via drag & drop
	 */
	protected string|null $group;

	protected mixed $value = [];

	public function __construct(
		bool|null $autofocus = null,
		array|null $default = null,
		bool|null $disabled = null,
		array|string|null $empty = null,
		array|null $fieldsets = null,
		array|string|null $help = null,
		string|null $group = null,
		array|string|null $label = null,
		string|null $name = null,
		int|null $max = null,
		int|null $min = null,
		bool|null $pretty = null,
		bool|null $required = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null,
	) {
		parent::__construct(
			autofocus: $autofocus,
			default:   $default,
			disabled:  $disabled,
			help:      $help,
			label:     $label,
			name:      $name,
			required:  $required,
			translate: $translate,
			when:      $when,
			width:     $width
		);

		$this->empty     = $empty;
		$this->fieldsets = $fieldsets;
		$this->group     = $group;
		$this->max       = $max;
		$this->min       = $min;
		$this->pretty    = $pretty;
	}

	public function api(): array
	{
		$field = $this;

		return [
			[
				'pattern' => 'uuid',
				'action'  => fn (): array => ['uuid' => Str::uuid()]
			],
			[
				'pattern' => 'paste',
				'method'  => 'POST',
				'action'  => function () use ($field): array {
					$request = App::instance()->request();
					$value   = BlocksCollection::parse($request->get('html'));
					$blocks  = BlocksCollection::factory($value);

					return $field->pasteBlocks($blocks->toArray());
				}
			],
			[
				'pattern' => 'fieldsets/(:any)',
				'method'  => 'GET',
				'action'  => function (
					string $fieldsetType
				) use ($field): array {
					$fields = $field->fields($fieldsetType);
					$form   = $field->form($fields);

					$form->fill(input: $form->defaults());

					return Block::factory([
						'content' => $form->toFormValues(),
						'type'    => $fieldsetType
					])->toArray();
				}
			],
			[
				'pattern' => 'fieldsets/(:any)/fields/(:any)/(:all?)',
				'method'  => 'ALL',
				'action'  => function (
					string $fieldsetType,
					string $fieldName,
					string|null $path = null
				) use ($field) {
					/**
					 * @var \Kirby\Cms\Api $api
					 */
					$api    = $this;
					$fields = $field->fields($fieldsetType);
					$field  = $field->form($fields)->field($fieldName);

					$fieldApi = $api->clone([
						'routes' => $field->api(),
						'data'   => [
							...$api->data(),
							'field' => $field
						]
					]);

					return $fieldApi->call(
						$path,
						$api->requestMethod(),
						$api->requestData()
					);
				}
			],
		];
	}

	public function blocksToValues(
		array $blocks,
		string $to = 'toFormValues'
	): array {
		$result = [];

		foreach ($blocks as $block) {
			try {
				$form = $this->fieldsetForm($block['type']);

				// overwrite the block content with form values
				$block['content'] = $form->reset()->fill(input: $block['content'])->$to();

				// create id if not exists
				$block['id'] ??= Str::uuid();
			} catch (Throwable) {
				// skip invalid blocks
			} finally {
				$result[] = $block;
			}
		}

		return $result;
	}

	public function default(): mixed
	{
		$default = $this->default;

		if (is_array($default) === false) {
			return null;
		}

		// set id for blocks if not exists
		array_walk($default, function (&$block) {
			$block['id'] ??= Str::uuid();
		});

		return $default;
	}

	public function dialogs(): array
	{
		return [
			[
				'pattern' => 'fieldsets/(:any)/fields/(:any)/(:all?)',
				'method'  => 'ALL',
				'action'  => function (
					string $fieldsetType,
					string $fieldName,
					string|null $path = null
				) {
					$fields = $this->fields($fieldsetType);
					$field  = $this->form($fields)->field($fieldName);
					return new FieldDialogController($field, $path);
				}
			],
		];
	}

	public function drawers(): array
	{
		return [
			[
				'pattern' => 'fieldsets/(:any)/fields/(:any)/(:all?)',
				'method'  => 'ALL',
				'action'  => function (
					string $fieldsetType,
					string $fieldName,
					string|null $path = null
				) {
					$fields = $this->fields($fieldsetType);
					$field  = $this->form($fields)->field($fieldName);
					return new FieldDrawerController($field, $path);
				}
			],
		];
	}

	public function fields(string $type): array
	{
		return $this->fieldset($type)->fields();
	}

	public function fieldset(string $type): Fieldset
	{
		if ($fieldset = $this->fieldsets()->find($type)) {
			return $fieldset;
		}

		throw new NotFoundException(
			'The fieldset ' . $type . ' could not be found'
		);
	}

	protected function fieldsetForm(string $type): Form
	{
		return $this->forms[$type] ??= $this->form($this->fields($type));
	}

	public function fieldsets(): Fieldsets
	{
		return $this->fieldsetsCollection ??= Fieldsets::factory(
			$this->fieldsets,
			['parent' => $this->model()]
		);
	}

	public function fieldsetGroups(): array|null
	{
		$groups = $this->fieldsets()->groups();
		return $groups === [] ? null : $groups;
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		$value  = BlocksCollection::parse($value);
		$blocks = BlocksCollection::factory($value)->toArray();
		return parent::fill(value: $this->blocksToValues($blocks));
	}

	public function form(array $fields): Form
	{
		return new Form(
			fields: $fields,
			model: $this->model,
			language: 'current'
		);
	}

	public function isEmpty(): bool
	{
		return count($this->value()) === 0;
	}

	public function group(): string
	{
		return $this->group ?? 'blocks';
	}

	/**
	 * Paste action for blocks:
	 *  - generates new uuids for the blocks
	 *  - filters only supported fieldsets
	 *  - applies max limit if defined
	 */
	public function pasteBlocks(array $blocks): array
	{
		$blocks = $this->blocksToValues($blocks);

		foreach ($blocks as $index => &$block) {
			$block['id'] = Str::uuid();

			// remove the block if it's not available
			try {
				$this->fieldset($block['type']);
			} catch (Throwable) {
				unset($blocks[$index]);
			}
		}

		return array_values($blocks);
	}

	public function props(): array
	{
		return [
			'empty'          => $this->empty(),
			'fieldsets'      => $this->fieldsets()->toArray(),
			'fieldsetGroups' => $this->fieldsetGroups(),
			'group'          => $this->group(),
			'max'            => $this->max(),
			'min'            => $this->min(),
		] + parent::props();
	}

	public function toStoredValue(): mixed
	{
		$value  = parent::toStoredValue();
		$blocks = $this->blocksToValues((array)$value, 'toStoredValues');

		// returns empty string to avoid storing empty array as string `[]`
		// and to consistency work with `$field->isEmpty()`
		if ($blocks === []) {
			return '';
		}

		return Json::encode($blocks, pretty: $this->pretty());
	}

	public function validations(): array
	{
		return [
			'blocks' => function ($value) {
				if ($this->min && count($value) < $this->min) {
					throw new InvalidArgumentException(
						key: 'blocks.min.' . ($this->min === 1 ? 'singular' : 'plural'),
						data: ['min' => $this->min]
					);
				}

				if ($this->max && count($value) > $this->max) {
					throw new InvalidArgumentException(
						key: 'blocks.max.' . ($this->max === 1 ? 'singular' : 'plural'),
						data: ['max' => $this->max]
					);
				}

				$forms  = [];
				$index  = 0;

				foreach ($value as $block) {
					$index++;
					$type = $block['type'];

					// create the form for the block
					// and cache it for later use
					if (isset($forms[$type]) === false) {
						try {
							$fieldset     = $this->fieldset($type);
							$fields       = $fieldset->fields() ?? [];
							$forms[$type] = $this->form($fields);
						} catch (Throwable) {
							// skip invalid blocks
							continue;
						}
					}

					// overwrite the content with the serialized form
					$form = $forms[$type]->reset()->fill($block['content']);

					foreach ($form->fields() as $field) {
						$errors = $field->errors();

						// rough first validation
						if (count($errors) > 0) {
							throw new InvalidArgumentException(
								key:'blocks.validation',
								data: [
									'field'    => $field->label(),
									'fieldset' => $fieldset->name(),
									'index'    => $index
								]
							);
						}
					}
				}

				return true;
			}
		];
	}
}
