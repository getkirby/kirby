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

    public function testTabsNormalize()
    {
        $fieldset = new Fieldset([
            'type' => 'test',
            'fields' => [
                'foo' => ['type' => 'text'],
                'bar' => ['type' => 'text']
            ]
        ]);

        $this->assertIsArray($fieldset->tabs());
        $this->assertArrayHasKey('content', $fieldset->tabs());
        $this->assertArrayHasKey('fields', $fieldset->tabs()['content']);
        $this->assertIsArray($fieldset->tabs()['content']['fields']);
        $this->assertCount(2, $fieldset->tabs()['content']['fields']);
    }
}
