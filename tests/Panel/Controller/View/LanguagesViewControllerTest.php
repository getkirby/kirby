<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguagesViewController::class)]
class LanguagesViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.LanguagesViewController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				'de' => [
					'code'    => 'de',
					'default' => false,
					'name'    => 'Deutsch'
				]
			]
		]);
	}

	public function testButtons(): void
	{
		$controller = new LanguagesViewController();
		$buttons = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(1, $buttons->render());
	}

	public function testLanguages(): void
	{
		$controller = new LanguagesViewController();
		$languages = $controller->languages();
		$this->assertCount(2, $languages);
	}

	public function testLoad(): void
	{
		$controller = new LanguagesViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-languages-view', $view->component);

		$props = $view->props();
		$this->assertCount(2, $props['languages']);
		$this->assertTrue($props['variables']);
	}
}
