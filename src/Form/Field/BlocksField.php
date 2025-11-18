<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks as BlocksCollection;
use Kirby\Cms\Fieldset;
use Kirby\Cms\Fieldsets;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Max;
use Kirby\Form\Mixin\Min;
use Kirby\Toolkit\Str;
use Throwable;

class BlocksField extends FieldClass
{
	use EmptyState;
	use Max;
	use Min;

	protected Fieldsets $fieldsets;
	protected array $forms;
	protected string|null $group;
	protected bool $pretty;
	protected mixed $value = [];

	public function __construct(array $params = [])
	{
		$this->setFieldsets(
			$params['fieldsets'] ?? null,
			$params['model'] ?? App::instance()->site()
		);

		parent::__construct($params);

		$this->setEmpty($params['empty'] ?? null);
		$this->setGroup($params['group'] ?? 'blocks');
		$this->setMax($params['max'] ?? null);
		$this->setMin($params['min'] ?? null);
		$this->setPretty($params['pretty'] ?? false);
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

	public function fields(string $type): array
	{
		return $this->fieldset($type)->fields();
	}

	public function fieldset(string $type): Fieldset
	{
		if ($fieldset = $this->fieldsets->find($type)) {
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
		return $this->fieldsets;
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
		$this->value = $this->blocksToValues($blocks);

		return $this;
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
		return $this->group;
	}

	public function pretty(): bool
	{
		return $this->pretty;
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

	public function routes(): array
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
					$fields = $field->fields($fieldsetType);
					$field  = $field->form($fields)->field($fieldName);

					$fieldApi = $this->clone([
						'routes' => $field->api(),
						'data'   => [
							...$this->data(),
							'field' => $field
						]
					]);

					return $fieldApi->call(
						$path,
						$this->requestMethod(),
						$this->requestData()
					);
				}
			],
		];
	}

	protected function setDefault(mixed $default = null): void
	{
		// set id for blocks if not exists
		if (is_array($default) === true) {
			array_walk($default, function (&$block) {
				$block['id'] ??= Str::uuid();
			});
		}

		parent::setDefault($default);
	}

	protected function setFieldsets(
		string|array|null $fieldsets,
		ModelWithContent $model
	): void {
		if (is_string($fieldsets) === true) {
			$fieldsets = [];
		}

		$this->fieldsets = Fieldsets::factory(
			$fieldsets,
			['parent' => $model]
		);
	}

	protected function setGroup(string|null $group = null): void
	{
		$this->group = $group;
	}

	protected function setPretty(bool $pretty = false): void
	{
		$this->pretty = $pretty;
	}

	public function toStoredValue(bool $default = false): mixed
	{
		$value  = $this->toFormValue($default);
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
						key: match ($this->min) {
							1       => 'blocks.min.singular',
							default => 'blocks.min.plural'
						},
						data: ['min' => $this->min]
					);
				}

				if ($this->max && count($value) > $this->max) {
					throw new InvalidArgumentException(
						key: match ($this->max) {
							1       => 'blocks.max.singular',
							default => 'blocks.max.plural'
						},
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
