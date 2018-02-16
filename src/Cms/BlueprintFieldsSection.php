<?php

namespace Kirby\Cms;

use Exception;

class BlueprintFieldsSection extends BlueprintSection
{

    protected $fields;
    protected $form;

    public function errors()
    {
        return $this->form()->errors();
    }

    public function fields()
    {
        return $this->form()->fields();
    }

    public function form()
    {
        if (is_a($this->form, Form::class)) {
            return $this->form;
        }

        return new Form([
            'fields' => $this->fields,
            'locale' => 'en',
            'model'  => $this->model(),
            'values' => $this->model()->content()->toArray()
        ]);
    }

    protected function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'errors' => $this->form()->errors(),
            'fields' => array_values($this->form()->fields()->toOptions()),
            'name'   => $this->name(),
            'type'   => $this->type(),
            'values' => $this->form()->values(),
        ];
    }

    public function values()
    {
        return $this->form()->values();
    }

}
