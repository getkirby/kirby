<?php

namespace Kirby\Cms;

class SiteRulesTest extends TestCase
{
    public function testUpdate()
    {
        new App([
            'users' => [
                [
                    'email' => 'admin@getkirby.com',
                    'role'  => 'admin'
                ]
            ],
            'user' => 'admin@getkirby.com'
        ]);

        $site = new Site([]);
        $this->assertTrue(SiteRules::update($site, [
            'copyright' => '2018'
        ]));
    }
}
