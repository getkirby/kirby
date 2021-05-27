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
            return $this->pages(null, $this->requestQuery('status'));
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
        'pattern' => 'site/children/search',
        'method'  => 'GET|POST',
        'action'  => function () {
            return $this->searchPages();
        }
    ],
    [
        'pattern' => 'site/blueprint',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()->blueprint();
        }
    ],
    [
        'pattern' => [
            'site/blueprints',
            /**
             * @deprecated
             * @todo remove in 3.7.0
             */
            'site/children/blueprints',
        ],
        'method'  => 'GET',
        'action'  => function () {
            // @codeCoverageIgnoreStart
            if ($this->route->pattern() === 'site/children/blueprints') {
                deprecated('`GET site/children/blueprints` API endpoint has been deprecated and will be removed in 3.7.0. Use `GET site/blueprints` instead.');
            }
            // @codeCoverageIgnoreEnd
            return $this->site()->blueprints($this->requestQuery('section'));
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
        'method'  => 'GET|POST',
        'action'  => function () {
            $pages = $this
                ->site()
                ->index(true)
                ->filter('isReadable', true);

            if ($this->requestMethod() === 'GET') {
                return $pages->search($this->requestQuery('q'));
            } else {
                return $pages->query($this->requestBody());
            }
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
