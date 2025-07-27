<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileSettingsDropdownController::class)]
#[CoversClass(ModelSettingsDropdownController::class)]
class FileSettingsDropdownControllerTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = FileSettingsDropdownController::factory('pages/test', 'test.jpg');
		$this->assertInstanceOf(FileSettingsDropdownController::class, $controller);
		$this->assertSame($this->app->file('test/test.jpg'), $controller->model());
	}

	public function testIsDisabledOption(): void
	{
		$controller = new FileSettingsDropdownController($this->app->file('test/test.jpg'));
		$this->assertFalse($controller->isDisabledOption('replace'));
	}

	public function testLoad(): void
	{
		$controller = new FileSettingsDropdownController($this->app->file('test/test.jpg'));
		$options    = $controller->load();
		$this->assertCount(6, $options);
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

		$controller = new FileSettingsDropdownController($this->app->file('test/test.jpg'));
		$options    = $controller->load();
		$this->assertCount(9, $options);
	}
}
