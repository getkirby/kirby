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

}
