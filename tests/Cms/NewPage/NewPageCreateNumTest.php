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

	public function testCreateDateBasedNumWithoutDate()
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			]
		]);

		$this->assertSame((int)date('Ymd'), $page->createNum());
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

	public function testCreateDateBasedNumWithDateHandlerWithoutDate()
	{
		$this->app = $this->app->clone([
			'options' => [
				'date.handler' => 'strftime'
			]
		]);

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			]
		]);

		$this->assertSame((int)date('Ymd'), $page->createNum());
	}

	public function testCreateDefaultNumForDraftWithSiblings()
	{
		$pageA = Page::create([
			'slug'   => 'child-a',
			'num'    => 1,
			'draft'  => false
		]);

		$pageB = Page::create([
			'slug'   => 'child-b',
			'num'    => 2,
			'draft'  => false
		]);

		$pageC = Page::create([
			'slug'   => 'child-c',
			'draft'  => true,
		]);

		// no given position
		$this->assertSame(3, $pageC->createNum());

		// valid given position
		$this->assertSame(2, $pageC->createNum(2));

		// position too low
		$this->assertSame(1, $pageC->createNum(-1));

		// position too high
		$this->assertSame(3, $pageC->createNum(4));
	}

	public function testCreateDefaultNumForDraftWithoutSiblings()
	{
		$page = Page::create([
			'slug'  => 'test',
			'draft' => true
		]);

		$this->assertSame(1, $page->createNum());
	}

	public function testCreateDefaultNumForPageWithSiblings()
	{
		$pageA = Page::create([
			'slug'   => 'child-a',
			'num'    => 1,
			'draft'  => false
		]);

		$pageB = Page::create([
			'slug'   => 'child-b',
			'num'    => 2,
			'draft'  => false
		]);

		$pageC = Page::create([
			'slug'   => 'child-c',
			'draft'  => false,
		]);

		// no given position
		$this->assertSame(3, $pageC->createNum());

		// valid given position
		$this->assertSame(2, $pageC->createNum(2));

		// position too low
		$this->assertSame(1, $pageC->createNum(-1));

		// position too high
		$this->assertSame(3, $pageC->createNum(4));
	}

	public function testCreateDefaultNumForPageWithoutSiblings()
	{
		$page = Page::create([
			'slug'  => 'test',
			'draft' => false
		]);

		$this->assertSame(1, $page->createNum());
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
