<?php

namespace Kirby\Cms;

class BlueprintTab extends BlueprintObject
{
    protected $columns;
    protected $sections;

    public function schema(): array
    {
        return [
            'columns' => [
                'type'     => 'array',
                'required' => true,
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

    public function columns(): Collection
    {
        if (is_a($this->columns, Collection::class) === true) {
            return $this->columns;
        }

        $this->columns = new Collection();

        foreach ($this->prop('columns') as $index => $props) {
            $column = new BlueprintColumn($props + ['id' => $index]);
            $this->columns->set($index, $column);
        }

        return $this->columns;
    }

    public function sections(): Collection
    {
        if (is_a($this->sections, Collection::class) === true) {
            return $this->sections;
        }

        $this->sections = new Collection;

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
        $columns = array_map(function ($column) {
            return $column->toArray();
        }, $this->columns());

        return array_merge(parent::toArray(), [
            'columns' => $columns
        ]);
    }

    public function toLayout(): array
    {
        return array_merge(parent::toArray(), [
            'columns' => array_values($columns->toArray(function($column) {
                return $column->toLayout()
            }))
        ]);
    }
}
