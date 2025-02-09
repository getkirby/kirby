<?php

namespace Kirby\Cms;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FieldsSectionTest extends TestCase
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

	public static function modelProvider(): array
	{
		return [
			[
				$page = new Page(['slug' => 'test']),
				true
			],
			[
				new Site(),
				true
			],
			[
				new File(['filename' => 'test.jpg', 'parent' => $page]),
				false
			],
			[
				new User(['email' => 'mail@getkirby.com']),
				false
			],
		];
	}

	#[DataProvider('modelProvider')]
	public function testSkipTitle(\Kirby\Cms\Page|\Kirby\Cms\Site|\Kirby\Cms\File|\Kirby\Cms\User $model, bool $skip)
	{
		$fields = [
			'text' => [
				'type' => 'textarea'
			]
		];

		if ($skip === false) {
			// add a custom title field to those models
			// which don't skip the title. Files and Users
			// should still be able to have title fields if needed
			$fields['title'] = [
				'type' => 'text'
			];
		}

		$section = new Section('fields', [
			'name'   => 'test',
			'model'  => $model,
			'fields' => $fields,
		]);

		if ($skip === true) {
			$this->assertCount(1, $section->fields());
			$this->assertArrayHasKey('text', $section->fields());
			$this->assertArrayNotHasKey('title', $section->fields());
		} else {
			$this->assertCount(2, $section->fields());
			$this->assertArrayHasKey('text', $section->fields());
			$this->assertArrayHasKey('title', $section->fields());
		}
	}

	public function testFormContentMultilang()
	{
		$app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			]
		]);

		$model = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'text' => 'Hello'
					]
				],
				[
					'code' => 'de',
					'content' => [
						'text' => 'Hallo'
					]
				],
			],
		]);

		// default language
		$section = new Section('fields', [
			'name' => 'test',
			'model' => $model,
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertSame('Hello', $section->form()->content()['text']);

		// secondary language
		$app->setCurrentLanguage('de');
		$app->setCurrentTranslation('de');

		$section = new Section('fields', [
			'name' => 'test',
			'model' => $model,
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertSame('Hallo', $section->form()->content()['text']);
	}
}
