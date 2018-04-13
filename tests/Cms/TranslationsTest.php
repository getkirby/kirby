<?php

namespace Kirby\Cms;

class TranslationsTest extends TestCase
{

    public function testFactory()
    {
        $translations = Translations::factory([
            'de_DE' => [
                'translation.name' => 'Deutsch'
            ],
            'en_US' => [
                'translation.name' => 'English'
            ]
        ]);

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->has('de_DE'));
        $this->assertTrue($translations->has('en_US'));
    }

    public function testLoad()
    {
        $translations = Translations::load(__DIR__ . '/fixtures/translations');

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->has('de_DE'));
        $this->assertTrue($translations->has('en_US'));
    }

}
