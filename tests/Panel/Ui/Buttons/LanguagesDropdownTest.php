<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Panel\Areas\AreaTestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\LanguagesDropdown
 * @covers ::__construct
 */
class LanguagesDropdownTest extends AreaTestCase
{
	/**
	 * @covers ::option
	 */
	public function testOption()
	{
		$page     = new Page(['slug' => 'test']);
		$button   = new LanguagesDropdown($page);
		$language = new Language(['name' => 'Deutsch', 'code' => 'de']);
		$this->assertSame([
			'text'    => 'Deutsch',
			'code'    => 'de',
			'current' => false,
			'link'    => '/pages/test?language=de'
		], $button->option($language));
	}

	/**
	 * @covers ::options
	 * @
	 */
	public function testOptionsSingleLang()
	{
		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);
		$this->assertSame([], $button->options());
	}

	/**
	 * @covers ::options
	 */
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
				'link'    => '/pages/test?language=en'
			],
			'-',
			[
				'text'    => 'Deutsch',
				'code'    => 'de',
				'current' => false,
				'link'    => '/pages/test?language=de'
			]
		], $button->options());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderSingleLang()
	{
		$page   = new Page(['slug' => 'test']);
		$button = new LanguagesDropdown($page);
		$this->assertNull($button->render());
	}

	/**
	 * @covers ::props
	 * @covers ::render
	 */
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
