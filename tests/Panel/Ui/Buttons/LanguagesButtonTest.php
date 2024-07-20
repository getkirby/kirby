<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Panel\Areas\AreaTestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\LanguagesButton
 * @covers ::__construct
 */
class LanguagesButtonTest extends AreaTestCase
{
	/**
	 * @covers ::render
	 */
	public function testSingleLang()
	{
		$button = new LanguagesButton();
		$this->assertNull($button->render());
	}

	/**
	 * @covers ::option
	 * @covers ::options
	 * @covers ::props
	 * @covers ::render
	 */
	public function tesMultiLang()
	{
		$this->enableMultilang();
		$this->installLanguages();

		$button = new LanguagesButton();
		$this->assertSame('k-view-languages-button', $button->component);
		$this->assertSame('k-view-languages-button', $button->class);
		$this->assertSame('translate', $button->icon);
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
		], $button->options);
		$this->assertSame('text', $button->responsive);
		$this->assertSame('EN', $button->text);

		$render = $button->render();
		$this->assertIsArray($render);
		$this->assertSame('k-view-languages-button', $render['component']);
		$this->assertIsArray($render['props']);
		$this->assertIsArray($render['props']['options']);
	}
}
