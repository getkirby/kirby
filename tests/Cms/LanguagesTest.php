<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LanguagesTest extends TestCase
{

    public function testLoad()
    {

        $app = new App([
            'options' => [
                'languages' => [
                    [
                        'code'    => 'en',
                        'name'    => 'English',
                        'default' => true,
                        'locale'  => 'en_US',
                        'url'     => '/',
                    ],
                    [
                        'code'    => 'de',
                        'name'    => 'Deutsch',
                        'locale'  => 'de_DE',
                        'url'     => '/de',
                    ],
                ]
            ]
        ]);

        $languages = Languages::load();

        $this->assertCount(2, $languages);
        $this->assertEquals(['en', 'de'], $languages->codes());
        $this->assertEquals('en', $languages->findDefault()->code());

    }

}
