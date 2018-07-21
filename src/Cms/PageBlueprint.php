<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for pages.
 */
class PageBlueprint extends Blueprint
{
    protected $num;

    public function num()
    {
        return $this->num;
    }

    public function options()
    {
        if (is_a($this->options, 'Kirby\Cms\PageBlueprintOptions') === true) {
            return $this->options;
        }

        return $this->options = new PageBlueprintOptions($this->model, $this->options);
    }

    protected function setNum($num = null)
    {
        $aliases = [
            0          => 'zero',
            'date'     => '{{ page.date("Ymd") }}',
            'datetime' => '{{ page.date("YmdHi") }}',
            'sort'     => 'default',
        ];

        if (isset($aliases[$num])) {
            $num = $aliases[$num];
        }

        $this->num = $num ?? 'default';
        return $this;
    }
}
