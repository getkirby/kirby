<?php

namespace Kirby\Form\Assertions;

trait Value
{

    public function assertValueIsValid(array $props)
    {
        $this->assertFalse($this->field($props)->error());
    }

    public function assertValueIsInvalid(array $props, string $errorType)
    {
        $this->assertEquals($errorType, $this->field($props)->error()['cause']);
    }

    public function assertValueIsBool()
    {
        $this->assertTrue($this->field(['value' => true])->value());
        $this->assertTrue($this->field(['value' => 1])->value());
        $this->assertTrue($this->field(['value' => 'true'])->value());

        $this->assertFalse($this->field(['value' => false])->value());
        $this->assertFalse($this->field(['value' => 0])->value());
        $this->assertFalse($this->field(['value' => 'false'])->value());

        $this->assertEquals('true', $this->field(['value' => true])->stringValue());
        $this->assertEquals('false', $this->field(['value' => false])->stringValue());
    }

}
