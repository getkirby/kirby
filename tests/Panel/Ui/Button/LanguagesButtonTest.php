<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\Page;
use Kirby\Panel\Areas\AreaTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguagesButton::class)]
class LanguagesButtonTest extends AreaTestCase
{
	public function testHasDiff(): void
	{
		$this->install();
		$this->installLanguages();

		$page = new Page(['slug' => 'test']);
		$button = new LanguagesButton($page);

		// no changes
		$this->assertFalse($button->hasDiff());

		// changes in current translation (not considered)
		$page->version('latest')->save([], 'en');
		$page->version('changes')->save([], 'en');
		$this->assertFalse($button->hasDiff());

		// changes in other translations
		$page->version('latest')->save([], 'de');
		$page->version('changes')->save([], 'de');
		$this->assertTrue($button->hasDiff());
	}

	public function testProps(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesButton($page);
		$props  = $button->props();
		$this->assertFalse($props['hasDiff']);
	}

	public function testRenderDefault(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesButton($page);
		$this->assertNull($button->render());
	}

	public function testRenderSingleLang(): void
	{
		$this->enableMultilang();
		$this->app([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				]
			]
		]);

		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesButton($page);
		$this->assertNull($button->render());
	}

	public function testRenderMultiLang(): void
	{
		$this->enableMultilang();
		$this->installLanguages();

		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesButton($page);
		$this->assertSame('k-languages-dropdown', $button->component);
		$this->assertSame('k-languages-dropdown', $button->class);
		$this->assertSame('translate', $button->icon);
		$this->assertSame('/pages/test/languages', $button->options);
		$this->assertSame('text', $button->responsive);
		$this->assertSame('EN', $button->text);

		$render = $button->render();
		$this->assertIsArray($render);
		$this->assertSame('k-languages-dropdown', $render['component']);
		$this->assertIsArray($render['props']);
		$this->assertSame('/pages/test/languages', $render['props']['options']);
	}
}
