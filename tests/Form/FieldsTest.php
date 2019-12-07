<?php

namespace Kirby\Form;

use Kirby\Cms\Page;
use PHPUnit\Framework\TestCase;

class FieldsTest extends TestCase
{
    public function setUp(): void
    {
        Field::$types = [];
    }

    public function tearDown(): void
    {
        Field::$types = [];
    }

    public function testConstruct()
    {
        Field::$types = [
            'test' => []
        ];

        $page   = new Page(['slug' => 'test']);
        $fields = new Fields([
            'a' => [
                'type' => 'test',
                'model' => $page
            ],
            'b' => [
                'type' => 'test',
                'model' => $page
            ],
        ]);

        $this->assertEquals('a', $fields->first()->name());
        $this->assertEquals('b', $fields->last()->name());
    }
}
