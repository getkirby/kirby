<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class PageBlueprint extends Blueprint
{

    public function data()
    {

        if ($this->data !== null) {
            return $this->data;
        }

        $data = parent::data();

        if (empty($data['layout']) === true) {
            $data = (new PageBlueprintConverter($data))->toArray();
        }

        foreach ($data['layout'] as $layoutKey => $layoutColumn) {
            foreach ($layoutColumn['sections'] as $sectionKey => $sectionAttributes) {
                if ($sectionAttributes['type'] === 'fields') {
                    $fields = new Fields($this->model(), $sectionAttributes['fields']);
                    $data['layout'][$layoutKey]['sections'][$sectionKey]['fields'] = array_values($fields->toArray());
                }
            }
        }

        return $this->data = $data;

    }

}
