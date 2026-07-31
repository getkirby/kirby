<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;
use Kirby\Toolkit\HtmlString;

class InfoSectionTest extends TestCase
{
	protected function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function testHeadline(): void
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

	public function testHeadlineFromName(): void
	{
		// single label
		$section = new Section('info', [
			'name'  => 'helpSection',
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertSame('Help section', $section->headline());
	}

	public function testText(): void
	{
		// single language text
		$section = new Section('info', [
			'name'     => 'test',
			'model'    => new Page(['slug' => 'test']),
			'text'     => 'Test'
		]);

		$this->assertSame('<p>Test</p>', (string)$section->text());

		// translated text
		$section = new Section('info', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'text'  => [
				'en' => 'Information',
				'de' => 'Informationen'
			]
		]);

		$this->assertSame('<p>Information</p>', (string)$section->text());
	}

	public function testTheme(): void
	{
		$section = new Section('info', [
			'name'  => 'test',
			'model' => new Page(['slug' => 'test']),
			'theme' => 'notice'
		]);

		$this->assertSame('notice', $section->theme());
	}

	public function testToArray(): void
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
			'text'  => new HtmlString('<p>Test Text</p>'),
			'theme' => 'notice'
		];

		$this->assertEquals($expected, $section->toArray()); // @phpstan-ignore-line
	}
}
