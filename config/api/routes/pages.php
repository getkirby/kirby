<?php

/**
 * Page Routes
 */
return [

    [
        'pattern' => 'pages/(:any)',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->page($id);
        }
    ],
    [
        'pattern' => 'pages/(:any)',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->page($id)->update($this->requestBody());
        }
    ],
    [
        'pattern' => 'pages/(:any)',
        'method'  => 'DELETE',
        'action'  => function (string $id) {
            return $this->page($id)->delete();
        }
    ],
    [
        'pattern' => 'pages/(:any)/blueprint',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->page($id)->blueprint();
        }
    ],
    [
        'pattern' => 'pages/(:any)/children',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->page($id)->children();
        }
    ],
    [
        'pattern' => 'pages/(:any)/children',
        'method'  => 'POST',
        'action'  => function (string $id) {
            return $this->page($id)->createChild($this->requestBody());
        }
    ],
    [
        'pattern' => 'pages/(:any)/children/search',
        'method'  => 'POST',
        'action'  => function (string $id) {
            return $this->page($id)->children()->query($this->requestBody());
        }
    ],
    [
        'pattern' => 'pages/(:any)/files',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->page($id)->files();
        }
    ],
    [
        'pattern' => 'pages/(:any)/files/search',
        'method'  => 'POST',
        'action'  => function (string $id) {
            return $this->page($id)->files()->query($this->requestBody());
        }
    ],
    [
        'pattern' => 'pages/(:any)/files',
        'method'  => 'POST',
        'action'  => function (string $id) {

            return $this->upload(function ($source, $filename) use ($id) {
                return $this->page($id)->createFile($source, [
                    'filename' => $filename
                ]);
            });

        }
    ],
    [
        'pattern' => 'pages/(:any)/files/(:any)',
        'method'  => 'GET',
        'action'  => function (string $id, string $filename) {
            return $this->file($id, $filename);
        }
    ],
    [
        'pattern' => 'pages/(:any)/files/(:any)',
        'method'  => 'PATCH',
        'action'  => function (string $id, string $filename) {
            return $this->file($id, $filename)->update($this->requestBody());
        }
    ],
    [
        'pattern' => 'pages/(:any)/files/(:any)',
        'method'  => 'DELETE',
        'action'  => function (string $id, string $filename) {
            return $this->file($id, $filename)->delete();
        }
    ],
    [
        'pattern' => 'pages/(:any)/files/(:any)/options',
        'method'  => 'GET',
        'action'  => function (string $id, string $filename) {
            // TODO: implement file options
        }
    ],
    [
        'pattern' => 'pages/(:any)/options',
        'method'  => 'GET',
        'action'  => function (string $id) {
            // TODO: implement options
        }
    ],

];
