<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Panel\Page as PanelPage;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class NewPageTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageTest';

	public function testApiUrl()
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

	public function testDepth()
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

	public function testId()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->id());
	}

	public function testIdForNestedPage()
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

	public function testPanel()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PanelPage::class, $page->panel());
	}

	public function testPermalink()
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => ['uuid' => 'my-page-uuid']
		]);

		$this->assertSame('//@/page/my-page-uuid', $page->permalink());
	}

	public function testQuery()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->query('page.slug'));
		$this->assertSame('test', $page->query('model.slug'));
	}

	public function testSite()
	{
		$site = new Site();
		$page = new Page([
			'slug'   => 'test',
			'site' => $site
		]);

		$this->assertIsSite($site, $page->site());
	}

	public function testSiteWithInvalidValue()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug' => 'test',
			'site' => 'mysite'
		]);
	}

	public function testToArray()
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

	public function testToString()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->toString('{{ page.slug }}'));
	}

	public function testUidInMultiLanguageMode()
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

	public function testUidInSingleLanguageMode()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->uid());
	}
}
