<?php

return [
    'pattern' => 'sections/(:any)',
    'method'  => ['GET', 'POST'],
    'action'  => function ($type) {

        $class   = 'Kirby\\Panel\\Sections\\' . ucfirst(strtolower($type)) . 'Section';
        $section = new $class($this->input());

        return $section->toArray();

    }
];
