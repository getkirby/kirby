<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class AppLanguagesTest extends TestCase
{
    public function testLanguages()
    {
        $app = new App([
            'options' => [
                'languages' => true
            ],
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
        $this->assertSame('en', $app->languageCode());
    }

    public function testLanguageCode()
    {
        $app = new App([
            'options' => [
                'languages' => true
            ],
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

        $this->assertSame('de', $app->languageCode('de'));
        $this->assertSame('en', $app->languageCode('en'));
        $this->assertSame('en', $app->languageCode());
        $this->assertSame(null, $app->languageCode('fr'));
    }
}
