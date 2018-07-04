<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Form\Field;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    public function setUp()
    {
        // start with a fresh set of fields
        Field::$mixins = [];
        Field::$types  = [];

        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }


}
