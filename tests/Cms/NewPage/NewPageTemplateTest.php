<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class NewPageTemplateTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageTemplateTest';

	public function testIntendedTemplate()
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$this->assertSame('test', $page->intendedTemplate()->name());
	}

	public function testInvalidTemplate()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'     => 'test',
			'template' => []
		]);
	}

	public function testTemplate()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('default', $page->template()->name());
	}
}
