<?php

namespace Kirby\Toolkit\DI;

use PHPUnit\Framework\TestCase;

class SingletonsTest extends TestCase
{

    public function testSet()
    {
        // instances
        $dependencies = new Singletons;
        $dependencies->set('test', 'stdClass');

        $a = $dependencies->get('test');
        $b = $dependencies->get('test');

        $this->assertTrue($a === $b);

        // singleton via singleton method
        $dependencies = new Singletons;
        $dependencies->singleton('test', 'stdClass');

        $a = $dependencies->get('test');
        $b = $dependencies->get('test');

        $this->assertTrue($a === $b);
    }
}
