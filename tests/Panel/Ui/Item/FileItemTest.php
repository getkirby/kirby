<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\File;
use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileItem::class)]
class FileItemTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Ui.FileItem';

	protected File $model;

	public function setUp(): void
	{
		parent::setUp();
		$this->model = new File(['filename' => 'test.jpg', 'parent' => $this->app->site()]);
	}

	public function testComponent(): void
	{
		$item = new FileItem(file: $this->model);
		$this->assertSame('k-item', $item->component());
	}

	public function testProps(): void
	{
		$item = new FileItem(file: $this->model);

		$expected = [
			'image'    => $this->model->panel()->image(),
			'info'     => '',
			'layout'   => 'list',
			'text'     => 'test.jpg',
			'id'       => 'test.jpg',
			'link'     => '/site/files/test.jpg',
			'permissions' => [
				'delete'       => false,
				'sort'         => false,
			],
			'uuid'      => $this->model->uuid()?->toString(),
			'dragText'  => '(image: ' . $this->model->uuid() . ')',
			'extension' => 'jpg',
			'filename'  => 'test.jpg',
			'mime'      => 'image/jpeg',
			'parent'    => 'site',
			'template'  => null,
			'url'       => $this->model->url(),
		];

		$this->assertSame($expected, $item->props());
	}
}
