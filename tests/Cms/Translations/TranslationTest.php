<?php

namespace Kirby\Cms;

class TranslationTest extends TestCase
{
    public function testProps()
    {
        $translation = new Translation('en', [
            'translation.author'    => 'Kirby',
            'translation.name'      => 'English',
            'translation.direction' => 'ltr',
            'translation.locale'    => 'en_GB',
            'test'                  => 'Test'
        ]);

        $this->assertEquals('Kirby', $translation->author());
        $this->assertEquals('English', $translation->name());
        $this->assertEquals('ltr', $translation->direction());
        $this->assertEquals('en_GB', $translation->locale());
        $this->assertEquals('Test', $translation->get('test'));
    }

    public function testLoad()
    {
        $translation = Translation::load('de', __DIR__ . '/fixtures/translations/de.json');

        $this->assertEquals('de', $translation->code());
        $this->assertEquals('Deutsch', $translation->name());
    }
}
