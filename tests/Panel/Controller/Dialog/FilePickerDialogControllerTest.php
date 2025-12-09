<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Panel\Collector\FilesCollector;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelPickerDialogController::class)]
#[CoversClass(FilePickerDialogController::class)]
class FilePickerDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.FilePickerDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
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

		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame(5, $controller->page);
		$this->assertSame('test', $controller->search);
	}

	public function testCollector(): void
	{
		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(FilesCollector::class, $controller->collector());
	}

	public function testFind(): void
	{
		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(File::class, $controller->find('test.jpg'));
	}

	public function testItem(): void
	{
		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$item = $controller->item($this->app->file('test.jpg'));
		$this->assertArrayHasKey('image', $item);
		$this->assertSame('', $item['info']);
		$this->assertSame('list', $item['layout']);
		$this->assertSame('test.jpg', $item['id']);
		$this->assertSame('/site/files/test.jpg', $item['link']);
		$this->assertArrayHasKey('permissions', $item);
	}

	public function testItems(): void
	{
		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$items = $controller->items();
		$this->assertCount(1, $items);
		$this->assertSame('test.jpg', $items[0]['filename']);
	}

	public function testLoad(): void
	{
		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$dialog = $controller->load();
		$this->assertInstanceOf(Dialog::class, $dialog);
		$this->assertSame('k-file-picker-dialog', $dialog->component);
	}

	public function testProps(): void
	{
		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame('k-file-picker-dialog', $props['component']);
		$this->assertTrue($props['hasSearch']);
		$this->assertCount(1, $props['items']);
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
					'value' => 'test.jpg',
				],
			],
		]);

		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame(['test.jpg'], $props['value']);
	}

	public function testQuery(): void
	{
		$controller = new FilePickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame('site.files', $controller->query());

		$controller = new FilePickerDialogController(
			model: $this->app->file('test.jpg')
		);

		$this->assertSame('file.siblings', $controller->query());

		$controller = new FilePickerDialogController(
			model: $this->app->file('test.jpg'),
			query: 'site.files.type("image")'
		);

		$this->assertSame('site.files.type("image")', $controller->query());
	}
}
