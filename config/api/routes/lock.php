<?php


/**
 * Content Lock Routes
 */
return [
    [
        'pattern' => '(:all)/lock',
        'method'  => 'GET',
        /**
         * @deprecated 3.6.0
         * @todo Remove in 3.7.0
         */
        'action'  => function (string $path) {
            deprecated('The `GET (:all)/lock` API endpoint has been deprecated and will be removed in 3.7.0');

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
        'method'  => 'GET',
        /**
         * @deprecated 3.6.0
         * @todo Remove in 3.7.0
         */
        'action'  => function (string $path) {
            deprecated('The `GET (:all)/unlock` API endpoint has been deprecated and will be removed in 3.7.0');


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
