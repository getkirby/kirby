<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class SiteBlueprint extends Blueprint
{

    public function __construct()
    {
        parent::__construct('site');
    }

    public function data()
    {

        if ($this->isDefault()) {
            return $this->data = [
                'name'   => 'site',
                'title'  => 'Site',
                'layout' => [
                    [
                        'width'    => '1/1',
                        'sections' => [
                            [
                                'headline' => 'Pages',
                                'type'     => 'pages',
                                'parent'   => '/'
                            ]
                        ]
                    ]
                ]
            ];
        }

        return parent::data();

    }

}
