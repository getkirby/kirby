<?php

namespace Kirby\Collection\Traits;

use Kirby\Collection\Collection;

use PHPUnit\Framework\TestCase;

class NavigatorTest extends TestCase
{

    public function testFirstLast()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier'
        ]);

        $this->assertEquals('eins', $collection->first());
        $this->assertEquals('vier', $collection->last());
    }

    public function testNth()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier'
        ]);

        $this->assertEquals('eins', $collection->nth(0));
        $this->assertEquals('zwei', $collection->nth(1));
        $this->assertEquals('drei', $collection->nth(2));
        $this->assertEquals('vier', $collection->nth(3));
    }

}
