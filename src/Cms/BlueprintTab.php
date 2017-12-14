<?php

namespace Kirby\Panel;

class BlueprintTab extends BlueprintObject
{
    protected $columns;
    protected $sections;

    public function schema(): array
    {
        return [
            'label' => [
                'type'    => 'string',
                'default' => function () {
                    return 'Main';
                }
            ],
            'columns' => [
                'type'     => 'array',
                'required' => true,
            ],
            'name' => [
                'type'     => 'string',
                'required' => true
            ]
        ];
    }

    public function columns(): array
    {
        if (is_array($this->columns) === true) {
            return $this->columns;
        }

        foreach ($this->prop('columns') as $column) {
            $this->columns[] = new BlueprintColumn($column);
        }

        return $this->columns;
    }

    public function sections(): array
    {
        if (is_array($this->sections) === true) {
            return $this->sections;
        }

        foreach ($this->columns() as $column) {
            foreach ($column->sections() as $section) {
                $this->sections[$section->name()] = $section;
            }
        }

        return $this->sections;
    }

    public function section(string $name)
    {
        return $this->sections()[$name] ?? null;
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
        $columns = array_map(function ($column) {
            return $column->toLayout();
        }, $this->columns());

        return array_merge(parent::toArray(), [
            'columns' => $columns
        ]);
    }
}
