<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

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

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::create($language);
	}

	public function testCreateWithInvalidName()
	{
		$language = new Language([
			'code' => 'de',
			'name' => ''
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid name for the language');

		LanguageRules::create($language);
	}

	public function testCreateWhenExists()
	{
		$language = $this->createMock(Language::class);
		$language->method('code')->willReturn('de');
		$language->method('name')->willReturn('Deutsch');
		$language->method('exists')->willReturn(true);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('The language already exists');

		LanguageRules::create($language);
	}

	public function testUpdateWithoutCode()
	{
		$language = $this->createMock(Language::class);
		$language->method('code')->willReturn('');
		$language->method('name')->willReturn('Deutsch');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::update($language);
	}

	public function testUpdateWithoutName()
	{
		$language = $this->createMock(Language::class);
		$language->method('code')->willReturn('de');
		$language->method('name')->willReturn('');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid name for the language');

		LanguageRules::update($language);
	}
}
