<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Cms\Fieldset;
use Kirby\Cms\Layout;
use Kirby\Cms\Layouts;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\Str;
use Throwable;

class LayoutField extends BlocksField
{
	protected $layouts;
	protected $settings;

	public function __construct(array $params)
	{
		$this->setModel($params['model'] ?? App::instance()->site());
		$this->setLayouts($params['layouts'] ?? ['1/1']);
		$this->setSettings($params['settings'] ?? null);

		parent::__construct($params);
	}

	public function fill($value = null)
	{
		$value   = $this->valueFromJson($value);
		$layouts = Layouts::factory($value, ['parent' => $this->model])->toArray();

		foreach ($layouts as $layoutIndex => $layout) {
			if ($this->settings !== null) {
				$layouts[$layoutIndex]['attrs'] = $this->attrsForm($layout['attrs'])->values();
			}

			foreach ($layout['columns'] as $columnIndex => $column) {
				$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($column['blocks']);
			}
		}

		$this->value = $layouts;
	}

	public function attrsForm(array $input = [])
	{
		$settings = $this->settings();

		return new Form([
			'fields' => $settings ? $settings->fields() : [],
			'model'  => $this->model,
			'strict' => true,
			'values' => $input,
		]);
	}

	public function layouts(): array|null
	{
		return $this->layouts;
	}

	public function props(): array
	{
		$settings = $this->settings();

		return array_merge(parent::props(), [
			'settings' => $settings !== null ? $settings->toArray() : null,
			'layouts'  => $this->layouts()
		]);
	}

	public function routes(): array
	{
		$field  = $this;
		$routes = parent::routes();
		$routes[] = [
			'pattern' => 'layout',
			'method'  => 'POST',
			'action'  => function () use ($field) {
				$request = App::instance()->request();

				$defaults = $field->attrsForm([])->data(true);
				$attrs    = $field->attrsForm($defaults)->values();
				$columns  = $request->get('columns') ?? ['1/1'];

				return Layout::factory([
					'attrs'   => $attrs,
					'columns' => array_map(fn ($width) => [
						'blocks' => [],
						'id'     => Str::uuid(),
						'width'  => $width,
					], $columns)
				])->toArray();
			},
		];

		$routes[] = [
			'pattern' => 'fields/(:any)/(:all?)',
			'method'  => 'ALL',
			'action'  => function (string $fieldName, string $path = null) use ($field) {
				$form  = $field->attrsForm();
				$field = $form->field($fieldName);

				$fieldApi = $this->clone([
					'routes' => $field->api(),
					'data'   => array_merge($this->data(), ['field' => $field])
				]);

				return $fieldApi->call($path, $this->requestMethod(), $this->requestData());
			}
		];

		return $routes;
	}

	protected function setLayouts(array $layouts = [])
	{
		$this->layouts = array_map(
			fn ($layout) => Str::split($layout),
			$layouts
		);
	}

	protected function setSettings($settings = null)
	{
		if (empty($settings) === true) {
			$this->settings = null;
			return;
		}

		$settings = Blueprint::extend($settings);

		$settings['icon']   = 'dashboard';
		$settings['type']   = 'layout';
		$settings['parent'] = $this->model();

		$this->settings = Fieldset::factory($settings);
	}

	public function settings()
	{
		return $this->settings;
	}

	public function store($value)
	{
		$value = Layouts::factory($value, ['parent' => $this->model])->toArray();

		// returns empty string to avoid storing empty array as string `[]`
		// and to consistency work with `$field->isEmpty()`
		if (empty($value) === true) {
			return '';
		}

		foreach ($value as $layoutIndex => $layout) {
			if ($this->settings !== null) {
				$value[$layoutIndex]['attrs'] = $this->attrsForm($layout['attrs'])->content();
			}

			foreach ($layout['columns'] as $columnIndex => $column) {
				$value[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($column['blocks'] ?? [], 'content');
			}
		}

		return $this->valueToJson($value, $this->pretty());
	}

	public function validations(): array
	{
		return [
			'layout' => function ($value) {
				$fields      = [];
				$layoutIndex = 0;

				foreach ($value as $layout) {
					$layoutIndex++;

					// validate settings form
					foreach ($this->attrsForm($layout['attrs'] ?? [])->fields() as $field) {
						$errors = $field->errors();

						if (empty($errors) === false) {
							throw new InvalidArgumentException([
								'key' => 'layout.validation.settings',
								'data' => [
									'index' => $layoutIndex
								]
							]);
						}
					}

					// validate blocks in the layout
					$blockIndex = 0;

					foreach ($layout['columns'] ?? [] as $column) {
						foreach ($column['blocks'] ?? [] as $block) {
							$blockIndex++;
							$blockType = $block['type'];

							try {
								$fieldset    = $this->fieldset($blockType);
								$blockFields = $fields[$blockType] ?? $this->fields($blockType) ?? [];
							} catch (Throwable) {
								// skip invalid blocks
								continue;
							}

							// store the fields for the next round
							$fields[$blockType] = $blockFields;

							// overwrite the content with the serialized form
							foreach ($this->form($blockFields, $block['content'])->fields() as $field) {
								$errors = $field->errors();

								// rough first validation
								if (empty($errors) === false) {
									throw new InvalidArgumentException([
										'key' => 'layout.validation.block',
										'data' => [
											'blockIndex'  => $blockIndex,
											'field'       => $field->label(),
											'fieldset'    => $fieldset->name(),
											'layoutIndex' => $layoutIndex
										]
									]);
								}
							}
						}
					}
				}

				return true;
			}
		];
	}
}
