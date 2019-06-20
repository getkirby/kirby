<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LanguagesTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
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
        ]);

        $this->languages = $this->app->languages();
    }

    public function testLoad()
    {
        $this->assertCount(2, $this->languages);
        $this->assertEquals(['en', 'de'], $this->languages->codes());
        $this->assertEquals('en', $this->languages->default()->code());
    }

    public function testLoadFromFiles()
    {
        $this->app->clone([
            'roots' => [
                'languages' => __DIR__ . '/fixtures/LanguagesTest'
            ]
        ]);

        $languages = $this->app->languages();

        $this->assertCount(2, $this->languages);
        $this->assertEquals(['en', 'de'], $this->languages->codes());
        $this->assertEquals('en', $this->languages->default()->code());
    }

    public function testDefault()
    {
        $this->assertEquals('en', $this->languages->default()->code());
        $this->assertEquals('en', $this->languages->findDefault()->code());
    }
}
