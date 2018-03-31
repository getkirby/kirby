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
            return $this->file($id, $filename)->blueprint()->options()->toArray();
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
        'pattern' => 'pages/(:any)/files/(:any)/(:any)/(:all?)',
        'method'  => 'ALL',
        'action'  => function (string $id, string $filename, string $sectionName, string $path = '') {
            return $this->file($id, $filename)->blueprint()->section($sectionName)->apiCall($this, $path);
        }
    ],
    [
        'pattern' => 'pages/(:any)/options',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->page($id)->blueprint()->options()->toArray();
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
        'pattern' => 'pages/(:any)/(:any)/(:all?)',
        'method'  => 'ALL',
        'action'  => function (string $id, string $sectionName, string $path = '') {
            return $this->page($id)->blueprint()->section($sectionName)->apiCall($this, $path);
        }
    ]

];
