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
        'pattern' => 'site/children',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()->children();
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
        'pattern' => 'site/blueprint',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()->blueprint();
        }
    ],
    [
        'pattern' => 'site/files',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()->files();
        }
    ],
    [
        'pattern' => 'site/files/search',
        'method'  => 'POST',
        'action'  => function () {
            return $this->site()->files()->query($this->requestBody());
        }
    ],
    [
        'pattern' => 'site/files/(:any)',
        'method'  => 'GET',
        'action'  => function (string $filename) {
            return $this->file(null, $filename);
        }
    ],
    [
        'pattern' => 'site/files/(:any)',
        'method'  => 'PATCH',
        'action'  => function (string $filename) {
            return $this->file(null, $filename)->update($this->requestBody());
        }
    ],
    [
        'pattern' => 'site/files/(:any)',
        'method'  => 'DELETE',
        'action'  => function (string $filename) {
            return $this->file(null, $filename)->delete();
        }
    ],
    [
        'pattern' => 'site/files/(:any)/options',
        'method'  => 'GET',
        'action'  => function (string $filename) {
            return $this->file(null, $filename)->blueprint()->options()->toArray();
        }
    ],
    [
        'pattern' => 'site/options',
        'method'  => 'GET',
        'action'  => function () {
            return $this->site()->blueprint()->options()->toArray();
        }
    ],
    [
        'pattern' => 'site/(:any)/(:all?)',
        'method'  => 'ALL',
        'action'  => function (string $sectionName, string $path = '') {
            return $this->site()->blueprint()->section($sectionName)->apiCall($this, $path);
        }
    ]

];
