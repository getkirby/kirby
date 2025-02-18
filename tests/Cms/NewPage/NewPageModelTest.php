<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

class PageTestModel extends Page
{
}

#[CoversClass(Page::class)]
class NewPageModelTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageModelTest';

	public function testPageModel()
	{
		Page::$models = [
			'dummy' => PageTestModel::class
		];

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(PageTestModel::class, $page);

		Page::$models = [];
	}
}
