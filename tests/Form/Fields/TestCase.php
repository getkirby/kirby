<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Form\Field;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $app;

    public function setUp(): void
    {
        // start with a fresh set of fields
        Field::$mixins = [];
        Field::$types  = [];

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function app()
    {
        return $this->app;
    }
}
