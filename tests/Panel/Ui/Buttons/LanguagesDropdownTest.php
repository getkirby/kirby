<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Language;
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
		$language = new Language(['name' => 'Deutsch', 'code' => 'de']);
		$button   = new LanguagesDropdown();
		$this->assertSame([
			'text'    => 'Deutsch',
			'code'    => 'de',
			'current' => false
		], $button->option($language));
	}

	/**
	 * @covers ::options
	 */
	public function testOptionsSingleLang()
	{
		$button = new LanguagesDropdown();
		$this->assertSame([], $button->options());
	}

	/**
	 * @covers ::options
	 */
	public function testOptionsMultiLang()
	{
		$this->enableMultilang();
		$this->installLanguages();

		$button = new LanguagesDropdown();
		$this->assertSame([
			[
				'text'    => 'English',
				'code'    => 'en',
				'current' => true
			],
			'-',
			[
				'text'    => 'Deutsch',
				'code'    => 'de',
				'current' => false
			]
		], $button->options());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderSingleLang()
	{
		$button = new LanguagesDropdown();
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

		$button = new LanguagesDropdown();
		$this->assertSame('k-languages-dropdown', $button->component);
		$this->assertSame('k-languages-dropdown', $button->class);
		$this->assertSame('translate', $button->icon);
		$this->assertCount(3, $button->options);
		$this->assertSame('text', $button->responsive);
		$this->assertSame('EN', $button->text);

		$render = $button->render();
		$this->assertIsArray($render);
		$this->assertSame('k-languages-dropdown', $render['component']);
		$this->assertIsArray($render['props']);
		$this->assertIsArray($render['props']['options']);
	}
}
