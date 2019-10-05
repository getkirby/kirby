<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class AppLanguagesTest extends TestCase
{
    public function testLanguages()
    {
        $app = new App([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch'
                ]
            ]
        ]);

        $this->assertTrue($app->multilang());
        $this->assertCount(2, $app->languages());
        $this->assertEquals('en', $app->languageCode());
    }

    public function testLanguageCode()
    {
        $app = new App([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch'
                ]
            ]
        ]);

        $this->assertEquals('de', $app->languageCode('de'));
        $this->assertEquals('en', $app->languageCode('en'));
        $this->assertEquals('en', $app->languageCode());
        $this->assertEquals(null, $app->languageCode('fr'));
    }
}
