<?php

use Kirby\Exception\InvalidArgumentException;

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
            return $this->page($id)->update($this->requestBody(), $this->language(), true);
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
        'pattern' => 'pages/(:any)/blueprints',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->page($id)->blueprints($this->requestQuery('section'));
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
        'pattern' => 'pages/(:any)/files',
        'method'  => 'POST',
        'action'  => function (string $id) {
            return $this->upload(function ($source, $filename) use ($id) {
                return $this->page($id)->createFile([
                    'source'   => $source,
                    'template' => $this->requestBody('template'),
                    'filename' => $filename
                ]);
            });
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
        'pattern' => 'pages/(:any)/files/sort',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->page($id)->files()->changeSort($this->requestBody('files'));
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
        'method'  => 'POST',
        'action'  => function (string $id, string $filename) {
            return $this->upload(function ($source) use ($id, $filename) {
                return $this->file($id, $filename)->replace($source);
            });
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
            return $this->file($id, $filename)->permissions()->toArray();
        }
    ],
    [
        'pattern' => 'pages/(:any)/files/(:any)/rename',
        'method'  => 'PATCH',
        'action'  => function (string $id, string $filename) {
            return $this->file($id, $filename)->changeName($this->requestBody('name'));
        }
    ],
    [
        'pattern' => 'pages/(:any)/files/(:any)/sections/(:any)',
        'method'  => 'GET',
        'action'  => function (string $id, string $filename, string $sectionName) {
            if ($section = $this->file($id, $filename)->blueprint()->section($sectionName)) {
                return $section->toResponse();
            }
        }
    ],
    [
        'pattern' => 'pages/(:any)/options',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->page($id)->permissions()->toArray();
        }
    ],
    [
        'pattern' => 'pages/(:any)/slug',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->page($id)->changeSlug($this->requestBody('slug'));
        }
    ],
    [
        'pattern' => 'pages/(:any)/status',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->page($id)->changeStatus($this->requestBody('status'), $this->requestBody('position'));
        }
    ],
    [
        'pattern' => 'pages/(:any)/template',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->page($id)->changeTemplate($this->requestBody('template'));
        }
    ],
    [
        'pattern' => 'pages/(:any)/title',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->page($id)->changeTitle($this->requestBody('title'));
        }
    ],
    [
        'pattern' => 'pages/(:any)/sections/(:any)',
        'method'  => 'GET',
        'action'  => function (string $id, string $sectionName) {
            if ($section = $this->page($id)->blueprint()->section($sectionName)) {
                return $section->toResponse();
            }
        }
    ]

];
