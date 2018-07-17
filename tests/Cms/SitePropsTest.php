<?php

namespace Kirby\Cms;

class SitePropsTest extends TestCase
{

    public function testUrl()
    {
        $site = new Site([
            'url' => $url = 'https://getkirby.com'
        ]);

        $this->assertEquals($url, $site->url());
    }

    public function testToString()
    {
        $site = new Site(['url' => 'https://getkirby.com']);
        $this->assertEquals('https://getkirby.com', $site->toString('{{ site.url }}'));
    }

}
