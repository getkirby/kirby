<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class NumberFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'number';
    }

    public function props(): array
    {
        return ['name' => 'test'];
    }

    public function testMax()
    {
        $this->assertEquals(10, $this->field(['max' => 10])->max());
    }

    public function testMin()
    {
        $this->assertEquals(10, $this->field(['min' => 10])->min());
    }

    public function testStep()
    {
        $this->assertEquals(10, $this->field(['step' => 10])->step());
    }

    /**
     * @expectedException Exception
     */
    public function testExceedMax()
    {
        $this->field(['max' => 10])->submit(20);
    }

    /**
     * @expectedException Exception
     */
    public function testExceedMin()
    {
        $this->field(['min' => 10])->submit(1);
    }

}
