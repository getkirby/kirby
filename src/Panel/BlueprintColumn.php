<?php

namespace Kirby\Panel;

class BlueprintColumn extends BlueprintObject
{

    protected $sections = null;

    public function schema(): array
    {
        return [
            'sections' => [
                'type'     => 'array',
                'required' => true,
            ],
            'width' => [
                'type'    => 'string',
                'default' => function () {
                    return '1/1';
                },
            ],
        ];
    }

    public function sections(): array
    {
        if (is_array($this->sections) === true) {
            return $this->sections;
        }

        $this->sections = [];

        foreach ($this->prop('sections') as $section) {
            $section = new BlueprintSection($section);
            $this->sections[$section->name()] = $section;
        }

        return $this->sections;
    }

    public function section(string $name)
    {
        return $this->sections()[$name] ?? null;
    }

    public function toArray(): array
    {
        $sections = array_map(function ($section) {
            return $section->toArray();
        }, $this->sections());

        return array_merge(parent::toArray(), [
            'sections' => $sections
        ]);
    }

    public function toLayout(): array
    {
        $sections = array_values(array_map(function ($section) {
            return $section->name();
        }, $this->sections()));

        return array_merge(parent::toArray(), [
            'sections' => $sections
        ]);
    }

}
