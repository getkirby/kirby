<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class PageTranslationsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageTranslations';

	public function app($language = null)
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
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
			],
			'site' => [
				'children' => [
					[
						'children' => [
							[
								'children' => [
									[
										'slug' => 'child',
										'translations' => [
											[
												'code' => 'en',
												'content' => [
													'title' => 'Child',
												]
											],
											[
												'code' => 'de',
												'slug' => 'kind',
												'content' => [
													'title' => 'Kind',
												]
											],
										]
									]
								],
								'slug' => 'mother',
								'translations' => [
									[
										'code' => 'en',
										'content' => [
											'title' => 'Mother',
										]
									],
									[
										'code' => 'de',
										'slug' => 'mutter',
										'content' => [
											'title' => 'Mutter',
										]
									],
								],
							]
						],
						'slug'  => 'grandma',
						'translations' => [
							[
								'code' => 'en',
								'content' => [
									'title' => 'Grandma',
									'untranslated' => 'Untranslated'
								]
							],
							[
								'code' => 'de',
								'slug' => 'oma',
								'content' => [
									'title' => 'Oma',
								]
							],
						],
					],
					[
						'slug' => 'home'
					]
				],
			],
		]);

		if ($language !== null) {
			$app->setCurrentLanguage($language);
			$app->setCurrentTranslation($language);
		}

		return $app;
	}

	public function testUrl()
	{
		$app = $this->app();

		$page = $app->page('home');
		$this->assertSame('/en', $page->url());
		$this->assertSame('/de', $page->url('de'));

		$page = $app->page('grandma');
		$this->assertSame('/en/grandma', $page->url());
		$this->assertSame('/de/oma', $page->url('de'));

		$page = $app->page('grandma/mother');
		$this->assertSame('/en/grandma/mother', $page->url());
		$this->assertSame('/de/oma/mutter', $page->url('de'));

		$page = $app->page('grandma/mother/child');
		$this->assertSame('/en/grandma/mother/child', $page->url());
		$this->assertSame('/de/oma/mutter/kind', $page->url('de'));
	}

	public function testContentInEnglish()
	{
		$page = $this->app()->page('grandma');
		$this->assertSame('Grandma', $page->title()->value());
		$this->assertSame('Untranslated', $page->untranslated()->value());
	}

	public function testContentInDeutsch()
	{
		$page = $this->app('de')->page('grandma');
		$this->assertSame('Oma', $page->title()->value());

		$this->assertSame('Untranslated', $page->untranslated()->value());
	}

	public function testContent()
	{
		$page = $this->app('en')->page('grandma');

		// without language code
		$content = $page->content();
		$this->assertSame('Grandma', $content->title()->value());
		$this->assertSame('Untranslated', $content->untranslated()->value());

		// with default language code
		$content = $page->content('en');
		$this->assertSame('Grandma', $content->title()->value());
		$this->assertSame('Untranslated', $content->untranslated()->value());

		// with different language code
		$content = $page->content('de');
		$this->assertSame('Oma', $content->title()->value());
		$this->assertSame('Untranslated', $content->untranslated()->value());

		// switch back to default
		$content = $page->content('en');
		$this->assertSame('Grandma', $content->title()->value());
		$this->assertSame('Untranslated', $content->untranslated()->value());
	}

	public function testSlug()
	{
		$app = $this->app();

		$this->assertSame('grandma', $app->page('grandma')->slug());
		$this->assertSame('grandma', $app->page('grandma')->slug('en'));
		$this->assertSame('oma', $app->page('grandma')->slug('de'));

		$this->assertSame('mother', $app->page('grandma/mother')->slug());
		$this->assertSame('mother', $app->page('grandma/mother')->slug('en'));
		$this->assertSame('mutter', $app->page('grandma/mother')->slug('de'));

		$this->assertSame('child', $app->page('grandma/mother/child')->slug());
		$this->assertSame('child', $app->page('grandma/mother/child')->slug('en'));
		$this->assertSame('kind', $app->page('grandma/mother/child')->slug('de'));
	}

	public function testFindInEnglish()
	{
		$app = $this->app();
		$this->assertSame('grandma', $app->page('grandma')->id());
		$this->assertSame('grandma/mother', $app->page('grandma/mother')->id());
		$this->assertSame('grandma/mother/child', $app->page('grandma/mother/child')->id());
	}

	public function testFindInDeutsch()
	{
		$app = $this->app('de');
		$this->assertSame('grandma', $app->page('oma')->id());
		$this->assertSame('grandma/mother', $app->page('oma/mutter')->id());
		$this->assertSame('grandma/mother/child', $app->page('oma/mutter/kind')->id());
	}

	public function testTranslations()
	{
		$page = $this->app()->page('grandma');
		$this->assertCount(2, $page->translations());
		$this->assertSame(['en', 'de'], $page->translations()->keys());
	}

	public function testUntranslatableFields()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
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
			],
			'options' => [
				'languages' => true
			]
		]);

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'fields' => [
					'a' => [
						'type' => 'text'
					],
					'b' => [
						'type' => 'text',
						'translate' => false
					],
					'CAPITALIZED' => [
						'type' => 'text',
						'translate' => false
					],
					'dDdDdD' => [
						'type' => 'text',
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$en = $page->update([
			'a' => 'A',
			'b' => 'B',
			'capitalized' => 'C',
			'dDdDdD' => 'D'
		]);

		$expected = [
			'a' => 'A',
			'b' => 'B',
			'capitalized' => 'C',
			'dddddd' => 'D'
		];

		$this->assertSame($expected, $en->content('en')->data());

		$de = $page->update([
			'a' => 'A',
			'b' => 'B',
			'capitalized' => 'C',
			'dDdDdD' => 'D'
		], 'de');

		$expected = [
			'a' => 'A',
			'b' => null,
			'capitalized' => null,
			'dddddd' => 'D'
		];

		$this->assertSame($expected, $de->content('de')->data());
	}
}
