<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class FieldsetTest extends TestCase
{
    public function testConstruct()
    {
        $fieldset = new Fieldset([
            'type' => 'test'
        ]);

        $this->assertSame('test', $fieldset->type());
        $this->assertSame('Test', $fieldset->name());
        $this->assertFalse($fieldset->disabled());
        $this->assertNull($fieldset->icon());
        $this->assertTrue($fieldset->translate());
    }
}
