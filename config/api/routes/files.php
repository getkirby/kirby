<?php

use Kirby\Exception\InvalidArgumentException;

/**
 * Files Routes
 */
return [

    [
        'pattern' => '(:all)/files/(:any)/sections/(:any)',
        'method'  => 'GET',
        'action'  => function (string $path, string $filename, string $sectionName) {
            if ($section = $this->file($path, $filename)->blueprint()->section($sectionName)) {
                return $section->toResponse();
            }
        }
    ],
    [
        'pattern' => '(:all)/files/(:any)/fields/(:any)/(:all?)',
        'method'  => 'ALL',
        'action'  => function (string $parent, string $filename, string $fieldName, string $path = null) {
            if ($file = $this->file($parent, $filename)) {
                return $this->fieldApi($file, $fieldName, $path);
            }
        }
    ],
    [
        'pattern' => '(:all)/files',
        'method'  => 'GET',
        'action'  => function (string $path) {
            return $this->parent($path)->files();
        }
    ],
    [
        'pattern' => '(:all)/files',
        'method'  => 'POST',
        'action'  => function (string $path) {
            return $this->upload(function ($source, $filename) use ($path) {
                return $this->parent($path)->createFile([
                    'source'   => $source,
                    'template' => $this->requestBody('template'),
                    'filename' => $filename
                ]);
            });
        }
    ],
    [
        'pattern' => '(:all)/files/search',
        'method'  => 'GET|POST',
        'action'  => function (string $path) {
            $files = $this->parent($path)->files();

            if ($this->requestMethod() === 'GET') {
                return $files->search($this->requestQuery('q'));
            } else {
                return $files->query($this->requestBody());
            }
        }
    ],
    [
        'pattern' => '(:all)/files/sort',
        'method'  => 'PATCH',
        'action'  => function (string $path) {
            return $this->parent($path)->files()->changeSort($this->requestBody('files'));
        }
    ],
    [
        'pattern' => '(:all)/files/(:any)',
        'method'  => 'GET',
        'action'  => function (string $path, string $filename) {
            return $this->file($path, $filename);
        }
    ],
    [
        'pattern' => '(:all)/files/(:any)',
        'method'  => 'PATCH',
        'action'  => function (string $path, string $filename) {
            return $this->file($path, $filename)->update($this->requestBody(), $this->language(), true);
        }
    ],
    [
        'pattern' => '(:all)/files/(:any)',
        'method'  => 'POST',
        'action'  => function (string $path, string $filename) {
            return $this->upload(function ($source) use ($path, $filename) {
                return $this->file($path, $filename)->replace($source);
            });
        }
    ],
    [
        'pattern' => '(:all)/files/(:any)',
        'method'  => 'DELETE',
        'action'  => function (string $path, string $filename) {
            return $this->file($path, $filename)->delete();
        }
    ],
    [
        'pattern' => '(:all)/files/(:any)/name',
        'method'  => 'PATCH',
        'action'  => function (string $path, string $filename) {
            return $this->file($path, $filename)->changeName($this->requestBody('name'));
        }
    ],

];
