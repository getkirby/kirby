<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\Page;
use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageItem::class)]
class PageItemTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.PageItem';

	protected Page $model;

	public function setUp(): void
	{
		parent::setUp();
		$this->model = new Page(['slug' => 'test']);
	}

	public function testComponent(): void
	{
		$item = new PageItem(page: $this->model);
		$this->assertSame('k-item', $item->component());
	}

	public function testProps(): void
	{
		$item = new PageItem(page: $this->model);

		$expected = [
			'image'    => [
				'back'  => 'pattern',
				'color' => 'gray-500',
				'cover' => false,
				'icon'  => 'page',
			],
			'info'     => '',
			'layout'   => 'list',
			'text'     => 'test',
			'id'       => 'test',
			'link'     => '/pages/test',
			'permissions' => [
				'changeSlug'   => false,
				'changeStatus' => false,
				'changeTitle'  => false,
				'delete'       => false,
				'sort'         => false,
			],
			'uuid'     => $this->model->uuid()?->toString(),
			'dragText' => '(link: ' . $this->model->uuid() . ' text: test)',
			'parent'   => null,
			'status'   => 'unlisted',
			'template' => 'default',
		];

		$this->assertSame($expected, $item->props());
	}
}
