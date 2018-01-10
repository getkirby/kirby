<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class LineFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'line';
    }

    public function testToArray()
    {
        $expected = [
            'name' => 'line',
            'type' => 'line',
        ];

        $this->assertEquals($expected, $this->field()->toArray());
    }

    public function testSubmit()
    {
        $this->assertNull($this->field()->submit('test'));
    }

}
