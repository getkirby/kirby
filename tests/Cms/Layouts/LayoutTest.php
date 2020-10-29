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
}
