<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Panel\Areas\AreaTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguagesDropdown::class)]
class LanguagesDropdownTest extends AreaTestCase
{
	public function testHasChanges()
	{
		$this->install();
		$this->installLanguages();

		$page = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);

		// no changes
		$this->assertFalse($button->hasChanges());

		// changes in current translation (not considered)
		$page->version('latest')->save([], 'en');
		$page->version('changes')->save([], 'en');
		$this->assertFalse($button->hasChanges());

		// changes in other translations
		$page->version('latest')->save([], 'de');
		$page->version('changes')->save([], 'de');
		$this->assertTrue($button->hasChanges());
	}

	public function testOption()
	{
		$page     = new Page(['slug' => 'test']);
		$button   = new LanguagesDropdown($page);
		$language = new Language(['name' => 'Deutsch', 'code' => 'de']);
		$this->assertSame([
			'text'    => 'Deutsch',
			'code'    => 'de',
			'current' => false,
			'default' => false,
			'changes' => false,
			'lock'    => false
		], $button->option($language));
	}

	public function testOptionsSingleLang()
	{
		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);
		$this->assertSame([], $button->options());
	}

	public function testOptionsMultiLang()
	{
		$this->enableMultilang();
		$this->installLanguages();

		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);
		$this->assertSame([
			[
				'text'    => 'English',
				'code'    => 'en',
				'current' => true,
				'default' => true,
				'changes' => false,
				'lock'    => false
			],
			'-',
			[
				'text'    => 'Deutsch',
				'code'    => 'de',
				'current' => false,
				'default' => false,
				'changes' => false,
				'lock'    => false
			]
		], $button->options());
	}

	public function testProps()
	{
		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);
		$props  = $button->props();
		$this->assertFalse($props['hasChanges']);
	}

	public function testRenderDefault()
	{
		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);
		$this->assertNull($button->render());
	}

	public function testRenderSingleLang()
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
		$button = new LanguagesDropdown($page);
		$this->assertNull($button->render());
	}

	public function testRenderMultiLang()
	{
		$this->enableMultilang();
		$this->installLanguages();

		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);
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
