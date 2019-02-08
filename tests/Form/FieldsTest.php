<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use PHPUnit\Framework\TestCase;

class FieldsTest extends TestCase
{
    public function setUp(): void
    {
        Field::$types  = [];
        Field::$mixins = [];
    }

    public function tearDown(): void
    {
        Field::$types  = [];
        Field::$mixins = [];
    }

    public function testConstruct()
    {
        Field::$types = [
            'test' => []
        ];

        $fields = new Fields([
            'a' => [
                'type' => 'test'
            ],
            'b' => [
                'type' => 'test'
            ],
        ]);

        $this->assertEquals('a', $fields->first()->name());
        $this->assertEquals('b', $fields->last()->name());
    }
}
