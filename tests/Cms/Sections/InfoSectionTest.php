<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class InfoSectionTest extends TestCase
{
	public function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function testHeadline()
	{
		// single headline
		$section = new Section('info', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => 'Test'
		]);

		$this->assertSame('Test', $section->headline());

		// translated headline
		$section = new Section('info', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'label' => [
				'en' => 'Information',
				'de' => 'Informationen'
			]
		]);

		$this->assertSame('Information', $section->headline());
	}

	public function testText()
	{
		// single language text
		$section = new Section('info', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'text'     => 'Test'
		]);

		$this->assertSame('<p>Test</p>', $section->text());

		// translated text
		$section = new Section('info', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'text'  => [
				'en' => 'Information',
				'de' => 'Informationen'
			]
		]);

		$this->assertSame('<p>Information</p>', $section->text());
	}

	public function testTheme()
	{
		$section = new Section('info', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'theme' => 'notice'
		]);

		$this->assertSame('notice', $section->theme());
	}

	public function testToArray()
	{
		$section = new Section('info', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'icon'  => 'heart',
			'label' => 'Test Headline',
			'text'  => 'Test Text',
			'theme' => 'notice'
		]);

		$expected = [
			'icon'  => 'heart',
			'label' => 'Test Headline',
			'text'  => '<p>Test Text</p>',
			'theme' => 'notice'
		];

		$this->assertSame($expected, $section->toArray());
	}
}
