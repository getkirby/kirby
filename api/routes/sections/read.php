<?php

return [
    'pattern' => 'sections/(:any)',
    'method'  => ['GET', 'POST'],
    'action'  => function ($type) {

        $class   = 'Kirby\\Panel\\Sections\\' . $type . 'section';
        $section = new $class($this->input());

        return $section->toArray();

    }
];
