<?php

use Kirby\Exception\Exception;

/**
 * Content Lock Routes
 */
return [
    [
        'pattern' => '(:all)/lock',
        'method'  => 'GET',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return [
                    'supported' => true,
                    'locked'    => $lock->get()
                ];
            }

            return [
                'supported' => false,
                'locked'    => null
            ];
        }
    ],
    [
        'pattern' => '(:all)/lock',
        'method'  => 'PATCH',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->create();
            }

            throw new Exception([
                'key'      => 'lock.notImplemented',
                'httpCode' => 501
            ]);
        }
    ],
    [
        'pattern' => '(:all)/lock',
        'method'  => 'DELETE',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->remove();
            }

            throw new Exception([
                'key'      => 'lock.notImplemented',
                'httpCode' => 501
            ]);
        }
    ],
    [
        'pattern' => '(:all)/unlock',
        'method'  => 'GET',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return [
                    'supported' => true,
                    'unlocked'  => $lock->isUnlocked()
                ];
            }

            return [
                'supported' => false,
                'unlocked'  => null
            ];
        }
    ],
    [
        'pattern' => '(:all)/unlock',
        'method'  => 'PATCH',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->unlock();
            }

            throw new Exception([
                'key'      => 'lock.notImplemented',
                'httpCode' => 501
            ]);
        }
    ],
    [
        'pattern' => '(:all)/unlock',
        'method'  => 'DELETE',
        'action'  => function (string $path) {
            if ($lock = $this->parent($path)->lock()) {
                return $lock->resolve();
            }

            throw new Exception([
                'key'      => 'lock.notImplemented',
                'httpCode' => 501
            ]);
        }
    ],
];
