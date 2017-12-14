<?php

namespace Kirby\Cms;

class PagesTest extends TestCase
{

    public function pages()
    {
        return new Pages([
            new Page(['id' => 'a', 'num' => 1]),
            new Page(['id' => 'b', 'num' => 2]),
            new Page(['id' => 'c'])
        ]);
    }

    public function testFind()
    {
        $this->assertIsPage($this->pages()->find('a'), 'a');
        $this->assertIsPage($this->pages()->find('b'), 'b');
        $this->assertIsPage($this->pages()->find('c'), 'c');
    }

    public function testInvisible()
    {
        $this->assertCount(1, $this->pages()->invisible());
    }

    public function testVisible()
    {
        $this->assertCount(2, $this->pages()->visible());
    }

}
