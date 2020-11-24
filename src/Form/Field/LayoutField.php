<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Fieldset;
use Kirby\Cms\Form;
use Kirby\Cms\Layout;
use Kirby\Cms\Layouts;
use Kirby\Toolkit\Str;

class LayoutField extends BlocksField
{
    protected $layouts;

    public function __construct(array $params)
    {
        $this->setModel($params['model'] ?? site());
        $this->setLayouts($params['layouts'] ?? ['1/1']);
        $this->setSettings($params['settings'] ?? []);
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

    public function layouts(): ?array
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
                $defaults = $field->attrsForm([])->data(true);
                $attrs    = $field->attrsForm($defaults)->values();
                $columns  = get('columns') ?? ['1/1'];

                return Layout::factory([
                    'attrs'   => $attrs,
                    'columns' => array_map(function ($width) {
                        return [
                            'blocks' => [],
                            'id'     => uuid(),
                            'width'  => $width,
                        ];
                    }, $columns)
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
        $this->layouts = array_map(function ($layout) {
            return Str::split($layout);
        }, $layouts);
    }

    protected function setSettings(array $settings = [])
    {
        if (empty($settings) === true) {
            $this->settings = null;
            return;
        }

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
        return [];
    }
}
