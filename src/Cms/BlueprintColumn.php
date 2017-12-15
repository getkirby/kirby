<?php

namespace Kirby\Cms;

class BlueprintColumn extends BlueprintObject
{

    protected $sections = null;

    public function schema(): array
    {
        return [
            'sections' => [
                'type'     => 'array',
                'required' => true
            ],
            'width' => [
                'type'    => 'string',
                'default' => function () {
                    return '1/1';
                },
                'validate' => function ($value) {
                    return in_array($value, ['1/1', '1/2', '1/3', '2/3']);
                }
            ],
        ];
    }

    public function sections(): Collection
    {
        if (is_array($this->sections) === true) {
            return $this->sections;
        }

        $this->sections = new Collection;

        foreach ($this->prop('sections') as $props) {
            $section = new BlueprintSection($props);
            $this->sections->set($section->id(), $section);
        }

        return $this->sections;
    }

    public function section(string $name)
    {
        return $this->sections()->find($name);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'sections' => array_values($this->sections()->toArray())
        ]);
    }

    public function toLayout(): array
    {
        return array_merge(parent::toArray(), [
            'sections' => $this->sections()->keys(),
        ]);
    }

}
