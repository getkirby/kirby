<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\Page;
use Kirby\Cms\TestCase;
use Kirby\Toolkit\HtmlString;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelItem::class)]
class ModelItemTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Ui.ModelItem';

	protected Page $model;

	protected function setUp(): void
	{
		parent::setUp();
		$this->model = new Page(['slug' => 'test']);
	}

	protected function escapable(): Page
	{
		return new Page([
			'slug'    => 'test',
			'content' => ['title' => 'Fish <b>&</b> Chips']
		]);
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

		$this->assertSame('Test', (string)$item->props()['info']);
	}

	public function testInfoDynamic(): void
	{
		$item = new ModelItem(
			model: $this->model,
			info: '{{ page.title }}'
		);

		$this->assertSame('test', (string)$item->props()['info']);
	}

	public function testInfoEmpty(): void
	{
		// an empty template, not null: null would render the model id
		$item = new ModelItem(model: $this->model);
		$this->assertSame('', (string)$item->props()['info']);
	}

	public function testInfoEmptyWithTableLayout(): void
	{
		$item = new ModelItem(model: $this->model, layout: 'table');
		$this->assertSame('', $item->props()['info']);
	}

	public function testInfoWithTableLayout(): void
	{
		$item = new ModelItem(
			model:  $this->escapable(),
			info:   '{{ page.title }}',
			layout: 'table'
		);

		// table cells render as text, so escaping here would show up
		// as entities in the Panel
		$this->assertSame('Fish <b>&</b> Chips', $item->props()['info']);
	}

	public function testInfoWithListLayout(): void
	{
		$item = new ModelItem(
			model: $this->escapable(),
			info:  '{{ page.title }}'
		);

		$info = $item->props()['info'];

		$this->assertInstanceOf(HtmlString::class, $info);
		$this->assertSame('Fish &lt;b&gt;&amp;&lt;/b&gt; Chips', (string)$info);
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
			'info'        => new HtmlString(''),
			'layout'      => 'list',
			'text'        => new HtmlString('test'),
			'id'          => 'test',
			'link'        => '/pages/test',
			'permissions' => $this->model->permissions()->toArray(),
			'uuid'        => $this->model->uuid()?->toString()
		];

		$this->assertEquals($expected, $item->props()); // -ignore-line
	}

	public function testText(): void
	{
		$item = new ModelItem(
			model: $this->model,
			text: 'Test'
		);

		$this->assertSame('Test', (string)$item->props()['text']);
	}

	public function testTextDynamic(): void
	{
		$item = new ModelItem(
			model: $this->model,
			info: '{{ page.title }}'
		);

		$this->assertSame('test', (string)$item->props()['text']);
	}

	public function testTextWithTableLayout(): void
	{
		$item = new ModelItem(
			model:  $this->escapable(),
			text:   '{{ page.title }}',
			layout: 'table'
		);

		// table cells render as text, so escaping here would show up
		// as entities in the Panel
		$this->assertSame('Fish <b>&</b> Chips', $item->props()['text']);
	}

	public function testTextWithListLayout(): void
	{
		$item = new ModelItem(
			model: $this->escapable(),
			text:  '{{ page.title }}'
		);

		$text = $item->props()['text'];

		$this->assertInstanceOf(HtmlString::class, $text);
		$this->assertSame('Fish &lt;b&gt;&amp;&lt;/b&gt; Chips', (string)$text);
	}

}
