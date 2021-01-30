<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
    public function testConstruct()
    {
        $layout = new Layout();
        $this->assertInstanceOf('Kirby\Cms\LayoutColumns', $layout->columns());
    }

    public function testIsEmpty()
    {
        $layout = new Layout([
            'columns' => []
        ]);

        $this->assertTrue($layout->isEmpty());
        $this->assertFalse($layout->isNotEmpty());
    }

    public function testIsNotEmpty()
    {
        $layout = new Layout([
            'columns' => [
                [
                    'blocks' => [
                        ['type' => 'heading'],
                        ['type' => 'text'],
                    ]
                ],
                [
                    'blocks' => [
                        ['type' => 'heading'],
                        ['type' => 'text'],
                    ]
                ]
            ]
        ]);

        $this->assertFalse($layout->isEmpty());
        $this->assertTrue($layout->isNotEmpty());
    }

    public function testIsEmptyWithHidden()
    {
        $layout = new Layout([
            'columns' => [
                [
                    'blocks' => [
                        [
                            'type' => 'heading',
                            'isHidden' => true
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue($layout->isEmpty());
        $this->assertFalse($layout->isNotEmpty());
    }
}
