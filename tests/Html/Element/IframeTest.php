<?php

namespace Kirby\Html\Element;

use Kirby\Html\TestCase;

class IframeTest extends TestCase
{

    public function testConstruct()
    {

        // default
        $img = new Iframe;

        $this->assertEquals('',  $img->attr('src'));
        $this->assertEquals('<iframe border="0" frameborder="0" height="100%" width="100%"></iframe>', (string)$img);

        // custom src
        $img = new Iframe('https://getkirby.com');

        $this->assertEquals('https://getkirby.com',  $img->attr('src'));
        $this->assertEquals('<iframe border="0" frameborder="0" height="100%" src="https://getkirby.com" width="100%"></iframe>', (string)$img);

    }

}
