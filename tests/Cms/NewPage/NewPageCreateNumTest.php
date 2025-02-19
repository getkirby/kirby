<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageCreateNumTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageCreateNumTest';

	public function testCreateDateBasedNum()
	{
		// without date
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			]
		]);

		$this->assertSame((int)date('Ymd'), $page->createNum());

		// with date field
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date',
			],
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		$this->assertSame(20121212, $page->createNum());
	}

	public function testCreateDateBasedNumInMultiLanguageMode()
	{
		$this->setupMultiLanguage();
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			],
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'date' => '2019-01-01',
					]
				],
				[
					'code' => 'de',
					'content' => [
						'date' => '2018-01-01',
					]
				]
			]
		]);

		$this->assertSame(20190101, $page->createNum());

		$this->app->setCurrentLanguage('de');

		$this->assertSame(20190101, $page->createNum(), 'The num should always be created in the default language');
	}

	public function testCreateDateBasedNumWithDateHandler()
	{
		$this->app = $this->app->clone([
			'options' => [
				'date.handler' => 'strftime'
			]
		]);

		// without date
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			]
		]);

		$this->assertSame((int)date('Ymd'), $page->createNum());

		// with date field
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date',
			],
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		$this->assertSame(20121212, $page->createNum());
	}

	public function testCreateDefaultNum()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'one-child',
						'children' => [
							[
								'slug' => 'child-a'
							]
						]
					],
					[
						'slug'     => 'three-children',
						'children' => [
							[
								'slug' => 'child-a',
								'num'  => 1
							],
							[
								'slug' => 'child-b',
								'num'  => 2
							],
							[
								'slug' => 'child-c'
							]
						],
						'drafts' => [
							[
								'slug' => 'draft'
							]
						]
					]
				]
			]
		]);

		// no siblings
		$page = $app->page('one-child/child-a');
		$this->assertSame(1, $page->createNum());

		// two listed siblings / no position
		$page = $app->page('three-children/child-c');
		$this->assertSame(3, $page->createNum());

		// one listed sibling / valid position
		$page = $app->page('three-children/child-a');
		$this->assertSame(2, $page->createNum(2));

		// one listed sibling / position too low
		$page = $app->page('three-children/child-a');
		$this->assertSame(1, $page->createNum(-1));

		// one listed sibling / position too high
		$page = $app->page('three-children/child-a');
		$this->assertSame(2, $page->createNum(3));

		// draft / no position
		$page = $app->page('three-children/draft');
		$this->assertSame(3, $page->createNum());

		// draft / given position
		$page = $app->page('three-children/draft');
		$this->assertSame(1, $page->createNum(1));
	}

	public function testCreateQueryBasedNum()
	{
		$page = Page::create([
			'slug' => 'test',
			'blueprint' => [
				'num' => '{{ page.year }}'
			],
			'content' => [
				'year' => 2016
			]
		]);

		$this->assertSame(2016, $page->createNum());
	}

	public function testCreateQueryBasedNumWithoutResult()
	{
		$page = Page::create([
			'slug' => 'test',
			'blueprint' => [
				'num' => '{{ page.year }}'
			]
		]);

		$this->assertSame(0, $page->createNum());
	}

	public function testCreateQueryBasedNumInMultiLanguageMode()
	{
		$this->setupMultiLanguage();
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => '{{ page.year }}'
			],
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'year' => 2016
					]
				],
				[
					'code' => 'de',
					'content' => [
						'year' => 1999
					]
				]
			]
		]);

		$this->assertSame(2016, $page->createNum());
	}

	public function testCreateZeroBasedNum()
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'zero'
			]
		]);

		$this->assertSame(0, $page->createNum());

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 0
			]
		]);

		$this->assertSame(0, $page->createNum());
	}
}
