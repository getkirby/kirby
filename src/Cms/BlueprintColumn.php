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

    public function sections(): BlueprintCollection
    {
        if (is_a($this->sections, BlueprintCollection::class) === true) {
            return $this->sections;
        }

        $this->sections = new BlueprintCollection;

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
        $array = parent::toArray();
        $array['sections'] = $this->sections()->toArray();

        return $array;
    }

    public function toLayout(): array
    {
        $array = parent::toLayout();
        $array['sections'] = $this->sections()->toLayout();

        return $array;
    }

}
