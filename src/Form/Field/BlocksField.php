<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks as BlocksCollection;
use Kirby\Cms\Fieldset;
use Kirby\Cms\Fieldsets;
use Kirby\Cms\ModelWithContent;
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
		string $to = 'values'
	): array {
		$result = [];
		$fields = [];

		foreach ($blocks as $block) {
			try {
				$type = $block['type'];

				// get and cache fields at the same time
				$fields[$type] ??= $this->fields($block['type']);

				// overwrite the block content with form values
				$block['content'] = $this->form(
					$fields[$type],
					$block['content']
				)->$to();

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

	public function fieldsets(): Fieldsets
	{
		return $this->fieldsets;
	}

	public function fieldsetGroups(): array|null
	{
		$groups = $this->fieldsets()->groups();
		return empty($groups) === true ? null : $groups;
	}

	public function fill(mixed $value = null): void
	{
		$value  = BlocksCollection::parse($value);
		$blocks = BlocksCollection::factory($value)->toArray();
		$this->value = $this->blocksToValues($blocks);
	}

	public function form(array $fields, array $input = []): Form
	{
		return new Form([
			'fields' => $fields,
			'model'  => $this->model,
			'strict' => true,
			'values' => $input,
		]);
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
					$fields   = $field->fields($fieldsetType);
					$defaults = $field->form($fields, [])->data(true);
					$content  = $field->form($fields, $defaults)->values();

					return Block::factory([
						'content' => $content,
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
						'data'   => array_merge(
							$this->data(),
							['field' => $field]
						)
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

	public function store(mixed $value): mixed
	{
		$blocks = $this->blocksToValues((array)$value, 'content');

		// returns empty string to avoid storing empty array as string `[]`
		// and to consistency work with `$field->isEmpty()`
		if (empty($blocks) === true) {
			return '';
		}

		return $this->valueToJson($blocks, $this->pretty());
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

	public function validations(): array
	{
		return [
			'blocks' => function ($value) {
				if ($this->min && count($value) < $this->min) {
					throw new InvalidArgumentException([
						'key'  => 'blocks.min.' . ($this->min === 1 ? 'singular' : 'plural'),
						'data' => [
							'min' => $this->min
						]
					]);
				}

				if ($this->max && count($value) > $this->max) {
					throw new InvalidArgumentException([
						'key'  => 'blocks.max.' . ($this->max === 1 ? 'singular' : 'plural'),
						'data' => [
							'max' => $this->max
						]
					]);
				}

				$fields = [];
				$index  = 0;

				foreach ($value as $block) {
					$index++;
					$type = $block['type'];

					try {
						$fieldset    = $this->fieldset($type);
						$blockFields = $fields[$type] ?? $fieldset->fields() ?? [];
					} catch (Throwable) {
						// skip invalid blocks
						continue;
					}

					// store the fields for the next round
					$fields[$type] = $blockFields;

					// overwrite the content with the serialized form
					$form = $this->form($blockFields, $block['content']);
					foreach ($form->fields() as $field) {
						$errors = $field->errors();

						// rough first validation
						if (empty($errors) === false) {
							throw new InvalidArgumentException([
								'key' => 'blocks.validation',
								'data' => [
									'field'    => $field->label(),
									'fieldset' => $fieldset->name(),
									'index'    => $index
								]
							]);
						}
					}
				}

				return true;
			}
		];
	}
}
