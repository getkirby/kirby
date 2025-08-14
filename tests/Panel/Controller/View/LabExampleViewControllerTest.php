<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Lab\Category;
use Kirby\Panel\Lab\Example;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LabExampleViewController::class)]
class LabExampleViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.LabExampleViewController';

	protected Category $category;
	protected Example $example;

	public function setUp(): void
	{
		parent::setUp();
		$this->category = Category::factory('components');
		$this->example = $this->category->example('buttons');
	}

	public function testBreadcrumb(): void
	{
		$controller = new LabExampleViewController($this->category, $this->example);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame('Components', $breadcrumb[0]['label']);
		$this->assertSame('buttons', $breadcrumb[1]['label']);
		$this->assertSame('/lab/components/buttons/1_variants', $breadcrumb[1]['link']);
	}

	public function testButtons(): void
	{
		$controller = new LabExampleViewController($this->category, $this->example);
		$buttons = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(2, $buttons->render());
	}

	public function testFactory(): void
	{
		$controller = LabExampleViewController::factory('components', 'buttons');
		$this->assertInstanceOf(LabExampleViewController::class, $controller);
	}

	public function testGithub(): void
	{
		$controller = LabExampleViewController::factory('basics', 'design', 'colors');
		$this->assertSame('https://github.com/getkirby/kirby/tree/main/panel/src/styles/config/colors.css', $controller->github());
	}

	public function testLoad(): void
	{
		$controller = new LabExampleViewController($this->category, $this->example);
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-lab-playground-view', $view->component);

		$props = $view->props();
		$this->assertTrue($props['compiler']);
		$this->assertSame('k-button', $props['docs']);
		$this->assertSame('/lab/components/buttons/1_variants/index.vue', $props['file']);
		$this->assertSame('https://github.com/getkirby/kirby/tree/main/panel/src/components/Navigation/Button.vue', $props['github']);
		$this->assertArrayHasKey('props', $props);
		$this->assertArrayHasKey('style', $props);
		$this->assertArrayHasKey('template', $props);
		$this->assertSame('1_variants', $props['tab']);
		$this->assertIsArray($props['tabs']);
		$this->assertSame('buttons', $props['title']);
	}

	public function testLoadWithDisabledCompiler(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'vue' => [
						'compiler' => false
					]
				]
			]
		]);

		$controller = new LabExampleViewController($this->category, $this->example);
		$props = $controller->load()->props();
		$this->assertFalse($props['compiler']);
	}
}
