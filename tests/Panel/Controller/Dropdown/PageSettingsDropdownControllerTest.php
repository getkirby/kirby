<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageSettingsDropdownController::class)]
#[CoversClass(ModelSettingsDropdownController::class)]
class PageSettingsDropdownControllerTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = PageSettingsDropdownController::factory('test');
		$this->assertInstanceOf(PageSettingsDropdownController::class, $controller);
		$this->assertSame($this->app->page('test'), $controller->model());
	}

	public function testIsDisabledOption(): void
	{
		$controller = new PageSettingsDropdownController($this->app->page('test'));
		$this->assertFalse($controller->isDisabledOption('changeTitle'));
	}

	public function testLoad(): void
	{
		$controller = new PageSettingsDropdownController($this->app->page('test'));
		$options    = $controller->load();
		$this->assertCount(10, $options);
	}

	public function testLoadListView(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'view' => 'list'
				]
			]
		]);

		$controller = new PageSettingsDropdownController($this->app->page('test'));
		$options    = $controller->load();
		$this->assertCount(13, $options);
	}
}
