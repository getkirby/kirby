<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Panel\Page as PanelPage;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageTest';

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

	public function testPanel()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PanelPage::class, $page->panel());
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
		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->uid());
	}

}
