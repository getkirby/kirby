<?php

/**
 * Site Routes
 */
return [

    [
        'pattern' => 'site',
        'action'  => function () {
            return $this->site();
        }
    ],
    [
        'pattern' => 'site',
        'method'  => 'PATCH',
        'action'  => function () {
            return $this->site()->update($this->requestBody(), $this->language(), true);
        }
    ],
    [
        'pattern' => 'site/children',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()->children();
        }
    ],
    [
        'pattern' => 'site/children',
        'method'  => 'POST',
        'action'  => function () {
            return $this->site()->createChild($this->requestBody());
        }
    ],
    [
        'pattern' => 'site/children/blueprints',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()->blueprints($this->requestQuery('section'));
        }
    ],
    [
        'pattern' => 'site/children/search',
        'method'  => 'POST',
        'action'  => function () {
            return $this->site()->children()->query($this->requestBody());
        }
    ],
    [
        'pattern' => 'site/find',
        'method'  => 'POST',
        'action'  => function () {
            return $this->site()->find(false, ...$this->requestBody());
        }
    ],
    [
        'pattern' => 'site/title',
        'method'  => 'PATCH',
        'action'  => function () {
            return $this->site()->changeTitle($this->requestBody('title'));
        }
    ],
    [
        'pattern' => 'site/search',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()
                        ->index(true)
                        ->filterBy('isReadable', true)
                        ->search($this->requestQuery('q'), [
                            'score'     => [
                                'id'    => 64,
                                'title' => 64,
                            ]
                        ]);
        }
    ],
    [
        'pattern' => 'site/sections/(:any)',
        'method'  => 'GET',
        'action'  => function (string $sectionName) {
            if ($section = $this->site()->blueprint()->section($sectionName)) {
                return $section->toResponse();
            }
        }
    ],
    [
        'pattern' => 'site/fields/(:any)/(:all?)',
        'method'  => 'ALL',
        'action'  => function (string $fieldName, string $path = null) {
            return $this->fieldApi($this->site(), $fieldName, $path);
        }
    ]

];
