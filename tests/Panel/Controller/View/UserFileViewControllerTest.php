<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\File;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(FileViewController::class)]
#[CoversClass(UserFileViewController::class)]
class UserFileViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.UserFileViewController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'files' => [
						['filename' => 'test.jpg']
					]
				]
			],
		]);

		$this->app->impersonate('kirby');
	}

	public function testBreadcrumb(): void
	{
		$file       = $this->app->user('test')->file('test.jpg');
		$controller = new UserFileViewController($file);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame([
			[
				'label' => 'test@getkirby.com',
				'link'  => '/users/test',
			],
			[
				'label' => 'test.jpg',
				'link'  => '/users/test/files/test.jpg',
			],
		], $breadcrumb);
	}

	public function assertFactory(
		File $file,
		string $parent
	): void {
		$controller = FileViewController::factory($parent, 'test.jpg');
		$this->assertInstanceOf(FileViewController::class, $controller);
		$this->assertSame($file, $controller->model());
	}

	public function testFactoryForUserFile(): void
	{
		$controller = UserFileViewController::factory('users/test', 'test.jpg');
		$this->assertInstanceOf(UserFileViewController::class, $controller);
		$this->assertSame($this->app->user('test')->file('test.jpg'), $controller->model());
	}
}
