<?php

namespace Kirby\Cms;

use Kirby\Panel\Page as PanelPage;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class PageTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Page';

	public function testApiUrl(): void
	{
		$this->app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'mother',
						'children' => [
							[
								'slug' => 'child'
							]
						]
					]
				]
			]
		]);

		$page = $this->app->page('mother/child');

		$this->assertSame('https://getkirby.com/api/pages/mother+child', $page->apiUrl());
		$this->assertSame('pages/mother+child', $page->apiUrl(true));
	}

	public function testDepth(): void
	{
		$grandma = new Page([
			'slug' => 'grandma',
		]);

		$mother = new Page([
			'slug'   => 'mother',
			'parent' => $grandma,
		]);

		$child = new Page([
			'slug'   => 'test',
			'parent' => $mother
		]);

		$this->assertSame(1, $grandma->depth());
		$this->assertSame(2, $mother->depth());
		$this->assertSame(3, $child->depth());
	}

	public function testId(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->id());
	}

	public function testIdForNestedPage(): void
	{
		$mother = new Page([
			'slug' => 'mother'
		]);

		$child = new Page([
			'slug' => 'child',
			'parent' => $mother
		]);

		$this->assertSame('mother', $mother->id());
		$this->assertSame('mother/child', $child->id());
	}

	public function testPanel(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PanelPage::class, $page->panel());
	}

	public function testPermalink(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => ['uuid' => 'my-page-uuid']
		]);

		$this->assertSame('//@/page/my-page-uuid', $page->permalink());
	}

	public function testQuery(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->query('page.slug'));
		$this->assertSame('test', $page->query('model.slug'));
	}

	public function testSite(): void
	{
		$site = new Site();
		$page = new Page([
			'slug'   => 'test',
			'site' => $site
		]);

		$this->assertIsSite($site, $page->site());
	}

	public function testSiteWithInvalidValue(): void
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug' => 'test',
			'site' => 'mysite'
		]);
	}

	public function testToArray(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$expected = [
			'content'      => [],
			'translations' => [
				'en' => [
					'code'    => 'en',
					'content' => [],
					'exists'  => false,
					'slug'    => null
				]
			],
			'children'  => [],
			'files'     => [],
			'id'        => 'test',
			'mediaUrl'  => '/media/pages/test',
			'mediaRoot' => static::TMP . '/media/pages/test',
			'num'       => null,
			'parent'    => null,
			'slug'      => 'test',
			'template'  => $page->template(),
			'uid'       => 'test',
			'uri'       => 'test',
			'url'       => '/test',
		];

		$this->assertSame($expected, $page->toArray());
	}

	public function testToString(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->toString('{{ page.slug }}'));
	}

	public function testUidInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'de',
					'slug' => 'test-de'
				]
			]
		]);

		$this->assertSame('test', $page->slug());
		$this->assertSame('test', $page->uid());

		$this->app->setCurrentLanguage('de');

		$this->assertSame('test-de', $page->slug());
		$this->assertSame('test', $page->uid(), 'The uid should be the same in all languages');
	}

	public function testUidInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->uid());
	}
}
