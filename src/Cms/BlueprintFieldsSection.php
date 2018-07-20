<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\NotFoundException;

/**
 * Fields sections are basically just
 * forms. They can contain any number of
 * fields. The section also handles validation
 * and submission of forms.
 */
class BlueprintFieldsSection extends BlueprintSection
{
    protected $fields;
    protected $form;
    protected $values;

    public function errors()
    {
        return $this->form()->errors();
    }

    public function field(string $name)
    {
        if ($field = $this->fields()->find($name)) {
            return $field;
        }

        throw new NotFoundException([
            'key'  => 'blueprint.field.notFound',
            'data' => ['name' => $name]
        ]);
    }

    public function fields()
    {
        return $this->form()->fields();
    }

    public function form()
    {
        $fields   = $this->fields;
        $disabled = $this->model()->permissions()->update() === false;

        if ($disabled === true) {
            $fields = array_map(function ($field) {
                $field['disabled'] = true;
                return $field;
            }, $fields);
        }

        return new Form([
            'fields' => $fields,
            'values' => $this->values ?? $this->model()->content()->toArray(),
            'model'  => $this->model(),
        ]);
    }

    /**
     * @return array
     */
    public function routes(): array
    {
        return [
            'read' => [
                'pattern' => '/',
                'method'  => 'GET',
                'action'  => function () {
                    return $this->section()->toArray();
                }
            ]
        ];
    }

    protected function setFields($fields): self
    {
        if (empty($fields) === true) {
            $fields = [];
        }

        foreach ($fields as $name => $field) {
            $field = Blueprint::extend($field);
            $field['name'] = $name;
            $this->fields[$name] = $field;
        }

        return $this;
    }

    public function toArray(): array
    {
        $form = $this->form();

        return [
            'code'   => 200,
            'data'   => $form->values(),
            'options' => [
                'errors' => $form->errors(),
                'fields' => $form->fields()->toOptions(),
                'name'   => $this->name(),
                'type'   => $this->type(),
            ],
            'status' => 'ok',
            'type'   => 'section'
        ];
    }

    public function values()
    {
        return $this->form()->values();
    }
}
