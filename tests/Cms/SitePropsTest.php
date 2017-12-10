<?php

namespace Kirby\Cms;

class SitePropsTest extends TestCase
{

    public function testRoot()
    {
        $site = new Site([
            'root' => $root = '/var/www/content'
        ]);

        $this->assertEquals($root, $site->root());
    }

    public function testStore()
    {
        $site = new Site([
            'store' => $store = new Store()
        ]);

        $this->assertEquals($store, $site->store());
    }

    public function testUrl()
    {
        $site = new Site([
            'url' => $url = 'https://getkirby.com'
        ]);

        $this->assertEquals($url, $site->url());
    }

}
