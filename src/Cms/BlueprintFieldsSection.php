<?php

namespace Kirby\Cms;

use Exception;

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

        throw new Exception('The field cannot be found');
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
            'locale' => 'en',
            'model'  => $this->model(),
            'values' => $this->values ?? $this->model()->content()->toArray(),
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
            ],
            'field' => [
                'pattern' => '(:any)',
                'method'  => 'GET',
                'action'  => function (string $fieldName) {
                    return $this->section()->field($fieldName)->toArray();
                }
            ],
            'patchField' => [
                'pattern' => '(:any)',
                'method'  => 'PATCH',
                'action'  => function (string $fieldName) {
                    return $this->section()->updateField($fieldName, $this->requestBody('value'))->toArray();
                }
            ],
            'patchAll' => [
                'pattern' => '/',
                'method'  => 'PATCH',
                'action'  => function () {
                    return $this->section()->updateAll($this->requestBody())->toArray();
                }
            ]
        ];
    }

    protected function setFields(array $fields): self
    {

        foreach ($fields as $name => $field) {
            $field = Blueprint::extend($field);
            $field['name'] = $name;
            $this->fields[$name] = $field;
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'errors' => $this->form()->errors(),
            'fields' => $this->form()->fields()->toOptions(),
            'name'   => $this->name(),
            'type'   => $this->type(),
            'values' => $this->form()->values(),
        ];
    }

    public function updateAll(array $values)
    {
        $this->values = $values;

        try {
            $this->form()->isValid();
        } catch (Exception $e) {
            return $this;
        }

        $this->model = $this->model()->update($values, false);
        return $this;
    }

    public function updateField(string $fieldName, $value)
    {
        $field = $this->field($fieldName)->clone([
            'value' => $value
        ]);

        $model   = $this->model();
        $payload = [$field->name() => $field->value()];

        try {
            $this->form()->isValid();
        } catch (Exception $e) {
            $this->values = $model->content()->update($payload)->toArray();
            return $this;
        }

        $this->model  = $model->update($payload, false);
        $this->values = null;
        return $this;
    }

    public function values()
    {
        return $this->form()->values();
    }

}
