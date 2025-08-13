<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\User;
use Kirby\Panel\Collector\UsersCollector;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelsPickerDialogController::class)]
#[CoversClass(UsersPickerDialogController::class)]
class UsersPickerDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UsersPickerDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
				],
				[
					'id'    => 'admin',
					'email' => 'admin@getkirby.com',
					'role'  => 'admin',
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function test__Construct(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'page'   => 5,
					'search' => 'test',
				],
			],
		]);

		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame(5, $controller->page);
		$this->assertSame('test', $controller->search);
	}

	public function testCollector(): void
	{
		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(UsersCollector::class, $controller->collector());
	}

	public function testFind(): void
	{
		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(User::class, $controller->find('test'));
	}

	public function testItem(): void
	{
		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);


		$item = $controller->item($this->app->user('test'));
		$this->assertArrayHasKey('image', $item);
		$this->assertSame('', $item['info']);
		$this->assertSame('list', $item['layout']);
		$this->assertSame('test', $item['id']);
		$this->assertSame('/users/test', $item['link']);
		$this->assertArrayHasKey('permissions', $item);
		$this->assertSame('user://test', $item['uuid']);
	}

	public function testItems(): void
	{
		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$items = $controller->items();
		$this->assertCount(2, $items);
		$this->assertSame('admin@getkirby.com', $items[0]['email']);
		$this->assertSame('test@getkirby.com', $items[1]['email']);
	}

	public function testLoad(): void
	{
		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$dialog = $controller->load();
		$this->assertInstanceOf(Dialog::class, $dialog);
		$this->assertSame('k-users-picker-dialog', $dialog->component);
	}

	public function testProps(): void
	{
		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame('k-users-picker-dialog', $props['component']);
		$this->assertTrue($props['hasSearch']);
		$this->assertCount(2, $props['items']);
		$this->assertSame('list', $props['layout']);
		$this->assertNull($props['max']);
		$this->assertTrue($props['multiple']);
		$this->assertNull($props['size']);
		$this->assertSame([], $props['value']);
	}

	public function testPropsWithValue(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'value' => 'test@getkirby.com, admin@getkirby.com',
				],
			],
		]);

		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame(['test@getkirby.com', 'admin@getkirby.com'], $props['value']);
	}

	public function testQuery(): void
	{
		$controller = new UsersPickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame('kirby.users', $controller->query());

		$controller = new UsersPickerDialogController(
			model: $this->app->user('test')
		);

		$this->assertSame('user.siblings', $controller->query());

		$controller = new UsersPickerDialogController(
			model: $this->app->user('test'),
			query: 'kirby.users.role("admin")'
		);

		$this->assertSame('kirby.users.role("admin")', $controller->query());
	}
}
