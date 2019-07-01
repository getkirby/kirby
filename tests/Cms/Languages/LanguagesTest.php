<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
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
                'languages' => $root = __DIR__ . '/fixtures/LanguagesTest'
            ]
        ]);

        Data::write($root . '/en.php', [
            'code' => 'en',
            'default' => true
        ]);

        Data::write($root . '/de.php', [
            'code' => 'de'
        ]);

        $languages = Languages::load();

        $this->assertCount(2, $languages);
        $this->assertEquals(['de', 'en'], $languages->codes());
        $this->assertEquals('en', $languages->default()->code());

        Dir::remove($root);
    }

    public function testDefault()
    {
        $this->assertEquals('en', $this->languages->default()->code());
        $this->assertEquals('en', $this->languages->findDefault()->code());
    }
}
