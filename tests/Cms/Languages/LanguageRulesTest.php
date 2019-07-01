<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LanguageRulesTest extends TestCase
{
    public function testCreateWithInvalidCode()
    {
        $language = new Language([
            'code' => 'l',
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid code for the language');

        LanguageRules::create($language);
    }

    public function testCreateWithInvalidName()
    {
        $language = new Language([
            'code' => 'de',
            'name' => ''
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid name for the language');

        LanguageRules::create($language);
    }

    public function testCreateWhenExists()
    {
        $language = $this->createMock(Language::class);
        $language->method('code')->willReturn('de');
        $language->method('name')->willReturn('Deutsch');
        $language->method('exists')->willReturn(true);

        $this->expectException('Kirby\Exception\DuplicateException');
        $this->expectExceptionMessage('The language already exists');

        LanguageRules::create($language);
    }
}
