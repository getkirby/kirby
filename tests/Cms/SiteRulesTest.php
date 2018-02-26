<?php

namespace Kirby\Cms;

class SiteRulesTest extends TestCase
{

    public function testUpdate()
    {
        $site = new Site([]);
        $this->assertTrue(SiteRules::update($site, [
            'copyright' => '2018'
        ]));
    }

}
