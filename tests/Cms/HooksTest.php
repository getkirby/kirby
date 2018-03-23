<?php

namespace Kirby\Cms;

class HooksTest extends TestCase
{

    public function testRegisterAndTrigger()
    {
        $kirby   = new App();
        $hooks   = new Hooks($kirby);
        $phpUnit = $this;

        $hooks->register('hookName', function ($a, $b) use ($phpUnit, $kirby) {
            $phpUnit->assertEquals($kirby, $this, 'bound object matches');
            $phpUnit->assertEquals('a', $a);
            $phpUnit->assertEquals('b', $b);
        });

        $hooks->trigger('hookName', 'a', 'b');
    }

}
