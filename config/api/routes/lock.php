<?php


/**
 * Content Lock Routes
 */
return [
    [
        'pattern' => '(:all)/lock',
        'method'  => 'PATCH',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->create();
            }
        }
    ],
    [
        'pattern' => '(:all)/lock',
        'method'  => 'DELETE',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->remove();
            }
        }
    ],
    [
        'pattern' => '(:all)/unlock',
        'method'  => 'PATCH',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->unlock();
            }
        }
    ],
    [
        'pattern' => '(:all)/unlock',
        'method'  => 'DELETE',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->resolve();
            }
        }
    ],
];
