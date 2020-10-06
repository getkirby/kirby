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

        $this->assertSame('de', $translation->code());
        $this->assertSame('Deutsch', $translation->name());

        // invalid
        $translation = Translation::load('zz', __DIR__ . '/fixtures/translations/zz.json');

        $this->assertSame('zz', $translation->code());
        $this->assertSame([], $translation->data());
    }

    public function testToArray()
    {
        $translation = Translation::load('de', __DIR__ . '/fixtures/translations/de.json');

        $this->assertSame([
            'code' => 'de',
            'data' => [
                'translation.direction' => 'ltr',
                'translation.name' => 'Deutsch',
                'translation.author' => 'Kirby Team',
                'error.test' => 'Dies ist ein Testfehler',
            ],
            'name' => 'Deutsch',
            'author' => 'Kirby Team',
        ], $translation->toArray());
    }
}
