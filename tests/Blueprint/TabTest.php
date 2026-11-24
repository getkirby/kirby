<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Form\Fields;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Tab::class)]
class TabTest extends TestCase
{
	protected ModelWithContent $model;

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->model = new Page(['slug' => 'a']);
	}

	protected function fields(): Fields
	{
		return new Fields(
			fields: ['title' => ['type' => 'text']],
			model: $this->model
		);
	}

	protected function tab(array $props = []): Tab
	{
		return new Tab(...[
			'model' => $this->model,
			'name'  => 'content',
			...$props
		]);
	}

	public function testColumns(): void
	{
		$columns = [
			[
				'width'  => '1/1',
				'fields' => []
			]
		];

		$this->assertSame([], $this->tab()->columns());
		$this->assertSame($columns, $this->tab(['columns' => $columns])->columns());
	}

	public function testColumnsWithFieldProps(): void
	{
		$tab = $this->tab([
			'columns' => [
				[
					'width'  => '1/1',
					'fields' => [
						'title'   => ['type' => 'text'],
						'missing' => ['type' => 'text']
					]
				]
			]
		]);

		$columns = $tab->columns($this->fields());
		$fields  = $columns[0]['fields'];

		// only fields that exist in the collection are resolved
		$this->assertSame(['title'], array_keys($fields));

		// the blueprint definition is replaced by the resolved props
		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('Title', $fields['title']['label']);

		// all other column props are kept
		$this->assertSame('1/1', $columns[0]['width']);
	}

	public function testFieldNames(): void
	{
		$tab = $this->tab([
			'columns' => [
				[
					'width'  => '1/2',
					'fields' => [
						'title' => ['type' => 'text'],
						'text'  => ['type' => 'textarea']
					]
				],
				[
					'width'  => '1/2',
					'fields' => [
						'gallery'  => ['type' => 'filelist'],
						'seoTitle' => ['type' => 'text']
					]
				]
			]
		]);

		$this->assertSame(
			['title', 'text', 'gallery', 'seotitle'],
			$tab->fieldNames()
		);
	}

	public function testFieldNamesEmpty(): void
	{
		$this->assertSame([], $this->tab()->fieldNames());
	}

	public function testIcon(): void
	{
		$this->assertNull($this->tab()->icon());
		$this->assertSame('text', $this->tab(['icon' => 'text'])->icon());
	}

	public function testLabel(): void
	{
		$this->assertSame('Content', $this->tab(['label' => 'Content'])->label());
	}

	public function testLabelAutomatic(): void
	{
		$tab = new Tab(
			model: $this->model,
			name: 'contentTab'
		);

		$this->assertSame('Content tab', $tab->label());
	}

	public function testLabelTranslated(): void
	{
		$this->assertSame(
			'Inhalt',
			$this->tab(['label' => ['de' => 'Inhalt']])->label()
		);
	}

	public function testLabelTranslatedWithI18nKey(): void
	{
		I18n::$translations = [
			'en' => [
				'tab.content' => 'Content'
			]
		];

		$this->assertSame('Content', $this->tab(['label' => 'tab.content'])->label());

		I18n::$translations = [];
	}

	public function testLink(): void
	{
		$this->assertSame('/pages/a/?tab=content', $this->tab()->link());
	}

	public function testModel(): void
	{
		$this->assertSame($this->model, $this->tab()->model());
	}

	public function testName(): void
	{
		$this->assertSame('content', $this->tab()->name());
	}

	public function testToButtonProps(): void
	{
		$tab = $this->tab([
			'columns' => [
				[
					'width'  => '1/1',
					'fields' => [
						'title' => ['type' => 'text']
					]
				]
			],
			'icon'  => 'text',
			'label' => 'Content'
		]);

		$this->assertSame([
			'fields' => ['title'],
			'icon'   => 'text',
			'label'  => 'Content',
			'link'   => '/pages/a/?tab=content',
			'name'   => 'content'
		], $tab->toButtonProps());
	}

	public function testToViewProps(): void
	{
		$columns = [
			[
				'width'  => '1/1',
				'fields' => []
			]
		];

		$tab = $this->tab([
			'columns' => $columns,
			'icon'    => 'text',
			'label'   => 'Content'
		]);

		$this->assertSame([
			'columns' => $columns,
			'icon'    => 'text',
			'label'   => 'Content',
			'link'    => '/pages/a/?tab=content',
			'name'    => 'content'
		], $tab->toViewProps());
	}

	public function testToViewPropsWithCustomProps(): void
	{
		$tab = $this->tab([
			'props' => ['foo' => 'bar']
		]);

		$this->assertSame([
			'foo'     => 'bar',
			'columns' => [],
			'icon'    => null,
			'label'   => 'Content',
			'link'    => '/pages/a/?tab=content',
			'name'    => 'content'
		], $tab->toViewProps());
	}

	public function testToViewPropsWithFieldProps(): void
	{
		$tab = $this->tab([
			'columns' => [
				[
					'width'  => '1/1',
					'fields' => [
						'title' => ['type' => 'text']
					]
				]
			]
		]);

		$array = $tab->toViewProps($this->fields());

		$this->assertSame(
			'Title',
			$array['columns'][0]['fields']['title']['label']
		);
	}
}
