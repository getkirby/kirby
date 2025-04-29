<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Cms\Fieldset;
use Kirby\Cms\Layout;
use Kirby\Cms\Layouts;
use Kirby\Data\Data;
use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\Str;
use Throwable;

class LayoutField extends BlocksField
{
	protected array|null $layouts;
	protected array|null $selector;
	protected Fieldset|null $settings;

	public function __construct(array $params)
	{
		$this->setModel($params['model'] ?? App::instance()->site());
		$this->setLayouts($params['layouts'] ?? ['1/1']);
		$this->setSelector($params['selector'] ?? null);
		$this->setSettings($params['settings'] ?? null);

		parent::__construct($params);
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		$attrs   = $this->attrsForm();
		$value   = Data::decode($value, type: 'json', fail: false);
		$layouts = Layouts::factory($value, ['parent' => $this->model])->toArray();

		foreach ($layouts as $layoutIndex => $layout) {
			if ($this->settings !== null) {
				$layouts[$layoutIndex]['attrs'] = $attrs->reset()->fill($layout['attrs'])->toFormValues();
			}

			foreach ($layout['columns'] as $columnIndex => $column) {
				$layouts[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($column['blocks']);
			}
		}

		$this->value = $layouts;

		return $this;
	}

	public function attrsForm(): Form
	{
		return new Form(
			fields: $this->settings()?->fields() ?? [],
			model:  $this->model
		);
	}

	public function layouts(): array|null
	{
		return $this->layouts;
	}

	/**
	 * Creates form values for each layout
	 */
	public function layoutsToValues(array $layouts): array
	{
		foreach ($layouts as &$layout) {
			$layout['id'] 	   ??= Str::uuid();
			$layout['columns'] ??= [];

			array_walk($layout['columns'], function (&$column) {
				$column['id']   ??= Str::uuid();
				$column['blocks'] = $this->blocksToValues($column['blocks'] ?? []);
			});
		}

		return $layouts;
	}

	/**
	 * Paste action for layouts:
	 *  - generates new uuids for layout, column and blocks
	 *  - filters only supported layouts
	 *  - filters only supported fieldsets
	 */
	public function pasteLayouts(array $layouts): array
	{
		$layouts = $this->layoutsToValues($layouts);

		foreach ($layouts as $layoutIndex => &$layout) {
			$layout['id'] = Str::uuid();

			// remove the row if layout not available for the pasted layout field
			$columns = array_column($layout['columns'], 'width');
			if (in_array($columns, $this->layouts(), true) === false) {
				unset($layouts[$layoutIndex]);
				continue;
			}

			array_walk($layout['columns'], function (&$column) {
				$column['id'] = Str::uuid();

				array_walk($column['blocks'], function (&$block, $index) use ($column) {
					$block['id'] = Str::uuid();

					// remove the block if it's not available
					try {
						$this->fieldset($block['type']);
					} catch (Throwable) {
						unset($column['blocks'][$index]);
					}
				});
			});
		}

		return $layouts;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'layouts'  => $this->layouts(),
			'selector' => $this->selector(),
			'settings' => $this->settings()?->toArray()
		];
	}

	public function routes(): array
	{
		$field  = $this;
		$routes = parent::routes();

		$routes[] = [
			'pattern' => 'layout',
			'method'  => 'POST',
			'action'  => function () use ($field): array {
				$request = App::instance()->request();

				$columns = $request->get('columns') ?? ['1/1'];
				$form    = $field->attrsForm();

				$form->fill(input: $form->defaults());
				$form->submit(input: $request->get('attrs') ?? []);

				return Layout::factory([
					'attrs'   => $form->toFormValues(),
					'columns' => array_map(fn ($width) => [
						'blocks' => [],
						'id'     => Str::uuid(),
						'width'  => $width,
					], $columns)
				])->toArray();
			},
		];

		$routes[] = [
			'pattern' => 'layout/paste',
			'method'  => 'POST',
			'action'  => function () use ($field): array {
				$request = App::instance()->request();
				$value   = Layouts::parse($request->get('json'));
				$layouts = Layouts::factory($value);

				return $field->pasteLayouts($layouts->toArray());
			}
		];

		$routes[] = [
			'pattern' => 'fields/(:any)/(:all?)',
			'method'  => 'ALL',
			'action'  => function (
				string $fieldName,
				string|null $path = null
			) use ($field): array {
				$form  = $field->attrsForm();
				$field = $form->field($fieldName);

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
		];

		return $routes;
	}

	public function selector(): array|null
	{
		return $this->selector;
	}

	protected function setDefault(mixed $default = null): void
	{
		// set id for layouts, columns and blocks within layout if not exists
		if (is_array($default) === true) {
			array_walk($default, function (&$layout) {
				$layout['id'] ??= Str::uuid();

				// set columns id within layout
				if (isset($layout['columns']) === true) {
					array_walk($layout['columns'], function (&$column) {
						$column['id'] ??= Str::uuid();

						// set blocks id within column
						if (isset($column['blocks']) === true) {
							array_walk($column['blocks'], function (&$block) {
								$block['id'] ??= Str::uuid();
							});
						}
					});
				}
			});
		}

		parent::setDefault($default);
	}

	protected function setLayouts(array $layouts = []): void
	{
		$this->layouts = array_map(
			fn ($layout) => Str::split($layout),
			$layouts
		);
	}

	/**
	 * Layout selector's styles such as size (`small`, `medium`, `large` or `huge`) and columns
	 */
	protected function setSelector(array|null $selector = null): void
	{
		$this->selector = $selector;
	}

	protected function setSettings(array|string|null $settings = null): void
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

	public function settings(): Fieldset|null
	{
		return $this->settings;
	}

	public function toStoredValue(bool $default = false): mixed
	{
		$attrs = $this->attrsForm();
		$value = $this->toFormValue($default);
		$value = Layouts::factory($value, ['parent' => $this->model])->toArray();

		// returns empty string to avoid storing empty array as string `[]`
		// and to consistency work with `$field->isEmpty()`
		if ($value === []) {
			return '';
		}

		foreach ($value as $layoutIndex => $layout) {
			if ($this->settings !== null) {
				$value[$layoutIndex]['attrs'] = $attrs->reset()->fill($layout['attrs'])->toStoredValues();
			}

			foreach ($layout['columns'] as $columnIndex => $column) {
				$value[$layoutIndex]['columns'][$columnIndex]['blocks'] = $this->blocksToValues($column['blocks'] ?? [], 'content');
			}
		}

		return Json::encode($value, pretty: $this->pretty());
	}

	public function validations(): array
	{
		return [
			'layout' => function ($value) {
				$attrsForm   = $this->attrsForm();
				$blockForms  = [];
				$layoutIndex = 0;

				foreach ($value as $layout) {
					$layoutIndex++;

					// validate settings form
					$form = $attrsForm->reset()->fill($layout['attrs'] ?? []);

					foreach ($form->fields() as $field) {
						$errors = $field->errors();

						if (count($errors) > 0) {
							throw new InvalidArgumentException(
								key:'layout.validation.settings',
								data: ['index' => $layoutIndex]
							);
						}
					}

					// validate blocks in the layout
					$blockIndex = 0;

					foreach ($layout['columns'] ?? [] as $column) {
						foreach ($column['blocks'] ?? [] as $block) {
							$blockIndex++;
							$blockType = $block['type'];

							if (isset($blockForms[$blockType]) === false) {
								try {
									$fieldset = $this->fieldset($blockType);
									$fields   = $this->fields($blockType) ?? [];
									$blockForms[$blockType] = $this->form($fields);
								} catch (Throwable) {
									// skip invalid blocks
									continue;
								}
							}

							// overwrite the content with the serialized form
							$form = $blockForms[$blockType]->reset()->fill($block['content']);

							foreach ($form->fields() as $field) {
								$errors = $field->errors();

								// rough first validation
								if (count($errors) > 0) {
									throw new InvalidArgumentException(
										key: 'layout.validation.block',
										data: [
											'blockIndex'  => $blockIndex,
											'field'       => $field->label(),
											'fieldset'    => $fieldset->name(),
											'layoutIndex' => $layoutIndex
										]
									);
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
