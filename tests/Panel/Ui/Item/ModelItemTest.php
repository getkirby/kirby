<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\Page;
use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelItem::class)]
class ModelItemTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Ui.ModelItem';

	protected Page $model;

	public function setUp(): void
	{
		parent::setUp();
		$this->model = new Page(['slug' => 'test']);
	}

	public function testComponent(): void
	{
		$item = new ModelItem(model: $this->model);
		$this->assertSame('k-item', $item->component());
	}

	public function testImageSettings(): void
	{
		$item = new ModelItem(
			model: $this->model,
			image: [
				'back'  => 'black',
			]
		);

		$this->assertSame('black', $item->props()['image']['back']);
	}

	public function testImageDisabled(): void
	{
		$item = new ModelItem(
			model: $this->model,
			image: false
		);

		$this->assertNull($item->props()['image']);
	}

	public function testInfo(): void
	{
		$item = new ModelItem(
			model: $this->model,
			info: 'Test'
		);

		$this->assertSame('Test', $item->props()['info']);
	}

	public function testInfoDynamic(): void
	{
		$item = new ModelItem(
			model: $this->model,
			info: '{{ page.title }}'
		);

		$this->assertSame('test', $item->props()['info']);
	}

	public function testProps(): void
	{
		$item = new ModelItem(model: $this->model);

		$expected = [
			'image'    => [
				'back'  => 'pattern',
				'color' => 'gray-500',
				'cover' => false,
				'icon'  => 'page',
			],
			'info'        => '',
			'layout'      => 'list',
			'text'        => 'test',
			'id'          => 'test',
			'link'        => '/pages/test',
			'permissions' => $this->model->permissions()->toArray(),
			'uuid'        => $this->model->uuid()?->toString()
		];

		$this->assertSame($expected, $item->props());
	}

	public function testText(): void
	{
		$item = new ModelItem(
			model: $this->model,
			text: 'Test'
		);

		$this->assertSame('Test', $item->props()['text']);
	}

	public function testTextDynamic(): void
	{
		$item = new ModelItem(
			model: $this->model,
			info: '{{ page.title }}'
		);

		$this->assertSame('test', $item->props()['text']);
	}

}
