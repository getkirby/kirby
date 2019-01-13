<?php

namespace Kirby\Toolkit;

class TplTest extends TestCase
{
    public function testLoadWithGoodTemplate()
    {
        $tpl = Tpl::load(__DIR__ . '/fixtures/tpl/good.php', ['name' => 'Peter']);
        $this->assertEquals('Hello Peter', $tpl);
    }

    /**
     * @expectedException Error
     */
    public function testLoadWithBadTemplate()
    {
        $tpl = Tpl::load(__DIR__ . '/fixtures/tpl/bad.php');
    }
}
