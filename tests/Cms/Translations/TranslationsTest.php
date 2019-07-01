<?php

namespace Kirby\Cms;

class TranslationsTest extends TestCase
{
    public function testFactory()
    {
        $translations = Translations::factory([
            'de' => [
                'translation.name' => 'Deutsch'
            ],
            'en' => [
                'translation.name' => 'English'
            ]
        ]);

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->has('de'));
        $this->assertTrue($translations->has('en'));
    }

    public function testLoad()
    {
        $translations = Translations::load(__DIR__ . '/fixtures/translations');

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->has('de'));
        $this->assertTrue($translations->has('en'));
    }
}
