<?php

namespace Kirby\Cms;

class BlueprintTab extends BlueprintObject
{
    protected $columns;
    protected $fields;
    protected $sections;

    public function schema(): array
    {
        return [
            'columns' => [
                'type'     => 'array',
                'required' => true,
            ],
            'icon' => [
                'type' => 'string',
            ],
            'id' => [
                'type'    => 'string',
                'default' => function () {
                    return $this->name();
                }
            ],
            'label' => [
                'type'    => 'string',
                'default' => function () {
                    return 'Main';
                }
            ],
            'name' => [
                'type'     => 'string',
                'required' => true
            ]
        ];
    }

    public function columns(): BlueprintCollection
    {
        if (is_a($this->columns, BlueprintCollection::class) === true) {
            return $this->columns;
        }

        $this->columns = new BlueprintCollection;

        foreach ($this->prop('columns') as $index => $props) {
            $column = new BlueprintColumn($props + ['id' => $index]);
            $this->columns->set($index, $column);
        }

        return $this->columns;
    }

    public function fields(): BlueprintCollection
    {
        if (is_a($this->fields, BlueprintCollection::class) === true) {
            return $this->fields;
        }

        $this->fields = new BlueprintCollection;

        foreach ($this->sections() as $section) {
            foreach ($section->fields() as $field) {
                $this->fields->set($field->id(), $field);
            }
        }

        return $this->fields;
    }

    public function field(string $name)
    {
        return $this->fields()->find($name);
    }

    public function sections(): BlueprintCollection
    {
        if (is_a($this->sections, BlueprintCollection::class) === true) {
            return $this->sections;
        }

        $this->sections = new BlueprintCollection;

        foreach ($this->columns() as $column) {
            foreach ($column->sections() as $section) {
                $this->sections->set($section->id(), $section);
            }
        }

        return $this->sections;
    }

    public function section(string $name)
    {
        return $this->sections()->find($name);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['columns'] = $this->columns()->toArray();

        return $array;
    }

    public function toLayout(): array
    {
        $array = parent::toArray();;
        $array['columns'] = $this->columns()->toLayout();

        return $array;
    }
}
