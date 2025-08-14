<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

class TestModelViewController extends ModelViewController
{
	public function title(): string
	{
		return 'Foo';
	}
}

#[CoversClass(ModelViewController::class)]
class ModelViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.ModelViewController';

	protected ModelWithContent $model;

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test',
						'content'  => [
							'title' => 'Test Page'
						]
					]
				]
			],
			'blueprints' => [
				'pages/test' => [
					'columns' => [
						[
							'width'    => '1/3',
							'sections' => []
						],
						[
							'width'    => '2/3',
							'sections' => []
						]
					]
				]
			]
		]);

		$this->model = $this->app->page('test');
		$this->app->impersonate('kirby');
	}

	public function testBreadcrumb(): void
	{
		$controller = new TestModelViewController($this->model);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame([], $breadcrumb);
	}

	public function testComponent(): void
	{
		$controller = new TestModelViewController($this->model);
		$this->assertSame('k-page-view', $controller->component());
	}

	public function testLoad(): void
	{
		$controller = new TestModelViewController($this->model);
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-page-view', $view->component);
	}

	public function testModel(): void
	{
		$controller = new TestModelViewController($this->model);
		$this->assertSame($this->model, $controller->model());
	}

	public function testNext(): void
	{
		$controller = new TestModelViewController($this->model);
		$next       = $controller->next();
		$this->assertNull($next);
	}

	public function testPrev(): void
	{
		$controller = new TestModelViewController($this->model);
		$prev       = $controller->prev();
		$this->assertNull($prev);
	}

	public function testTab(): void
	{
		$controller = new TestModelViewController($this->model);
		$this->assertSame('main', $controller->tab()['name']);

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'tab' => 'foo'
				]
			]
		]);

		$controller = new TestModelViewController($this->model);
		$this->assertSame('main', $controller->tab()['name']);
	}

	public function testTabs(): void
	{
		$controller = new TestModelViewController($this->model);
		$tabs       = $controller->tabs();
		$this->assertCount(1, $tabs);
		$this->assertSame('main', $tabs[0]['name']);
	}

	public function testTitle(): void
	{
		$controller = new TestModelViewController($this->model);
		$this->assertSame('Foo', $controller->title());
	}

	public function testVersions(): void
	{
		$this->model->version('latest')->save($latest = [
			'foo' => 'bar'
		]);

		$controller = new TestModelViewController($this->model);
		$versions   = $controller->versions();
		$this->assertSame('bar', $versions['latest']['foo']);
		$this->assertSame('bar', $versions['changes']['foo']);

		$this->model->version('changes')->save($changes = [
			'foo' => 'baz'
		]);

		$controller = new TestModelViewController($this->model);
		$versions   = $controller->versions();
		$this->assertSame('bar', $versions['latest']['foo']);
		$this->assertSame('baz', $versions['changes']['foo']);
	}
}
