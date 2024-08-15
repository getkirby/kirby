<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Language;
use Kirby\Panel\Areas\AreaTestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\LanguagesButton
 * @covers ::__construct
 */
class LanguagesButtonTest extends AreaTestCase
{
	/**
	 * @covers ::option
	 */
	public function testOption()
	{
		$language = new Language(['name' => 'Deutsch', 'code' => 'de']);
		$button   = new LanguagesButton();
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
		$button   = new LanguagesButton();
		$this->assertSame([], $button->options());
	}

	/**
	 * @covers ::options
	 */
	public function testOptionsMultiLang()
	{
		$this->enableMultilang();
		$this->installLanguages();

		$button   = new LanguagesButton();
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
		$button = new LanguagesButton();
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

		$button = new LanguagesButton();
		$this->assertSame('k-languages-view-button', $button->component);
		$this->assertSame('k-languages-view-button', $button->class);
		$this->assertSame('translate', $button->icon);
		$this->assertCount(3, $button->options);
		$this->assertSame('text', $button->responsive);
		$this->assertSame('EN', $button->text);

		$render = $button->render();
		$this->assertIsArray($render);
		$this->assertSame('k-languages-view-button', $render['component']);
		$this->assertIsArray($render['props']);
		$this->assertIsArray($render['props']['options']);
	}
}
