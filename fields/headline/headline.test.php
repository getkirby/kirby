<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class HeadlineFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'headline';
    }

    public function props(): array
    {
        return [
            'name' => 'test'
        ];
    }

    public function testToArray()
    {
        $expected = [
            'name'     => 'test',
            'label'    => 'Test',
            'numbered' => true,
            'type'     => 'headline',
        ];

        $this->assertEquals($expected, $this->field(['label' => 'Test'])->toArray());
    }

    public function testSubmit()
    {
        $this->assertNull($this->field()->submit('test'));
    }

}
