<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LanguageRulesTest extends TestCase
{
    public function setUp(): void
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

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

    public function testUpdateWithoutCode()
    {
        $language = $this->createMock(Language::class);
        $language->method('code')->willReturn('');
        $language->method('name')->willReturn('Deutsch');

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid code for the language');

        LanguageRules::update($language);
    }

    public function testUpdateWithoutName()
    {
        $language = $this->createMock(Language::class);
        $language->method('code')->willReturn('de');
        $language->method('name')->willReturn('');

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid name for the language');

        LanguageRules::update($language);
    }
}
