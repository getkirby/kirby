<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDialogController::class)]
#[CoversClass(PageDuplicateDialogController::class)]
class PageDuplicateDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PageDuplicateDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test'],
					[
						'slug' => 'test-with-files',
						'files' => [
							['filename' => 'test.jpg']
						]
					],
					[
						'slug' => 'test-with-children',
						'children' => [
							['slug' => 'test-child']
						]
					],
					[
						'slug' => 'test-with-children-and-files',
						'children' => [
							['slug' => 'test-child']
						],
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
		$controller = PageDuplicateDialogController::factory('test');
		$this->assertInstanceOf(PageDuplicateDialogController::class, $controller);
		$this->assertSame('test', $controller->page->id());
	}

	public function testFields(): void
	{
		$page       = $this->app->page('test');
		$controller = new PageDuplicateDialogController($page);
		$fields     = $controller->fields();

		$this->assertCount(2, $fields);
		$this->assertSame('URL appendix', $fields['slug']['label']);
		$this->assertSame('slug', $fields['slug']['type']);
		$this->assertSame('/', $fields['slug']['path']);
	}

	public function testFieldsWithFiles(): void
	{
		$page       = $this->app->page('test-with-files');
		$controller = new PageDuplicateDialogController($page);
		$fields     = $controller->fields();

		$this->assertCount(3, $fields);
		$this->assertSame('Copy files', $fields['files']['label']);
		$this->assertSame('toggle', $fields['files']['type']);
		$this->assertSame('1/1', $fields['files']['width']);
	}

	public function testFieldsWithChildren(): void
	{
		$page       = $this->app->page('test-with-children');
		$controller = new PageDuplicateDialogController($page);
		$fields     = $controller->fields();

		$this->assertCount(3, $fields);
		$this->assertSame('Copy pages', $fields['children']['label']);
		$this->assertSame('toggle', $fields['children']['type']);
		$this->assertSame('1/1', $fields['children']['width']);
	}

	public function testFieldsWithChildrenAndFiles(): void
	{
		$page       = $this->app->page('test-with-children-and-files');
		$controller = new PageDuplicateDialogController($page);
		$fields     = $controller->fields();

		$this->assertCount(4, $fields);
		$this->assertSame('1/2', $fields['files']['width']);
		$this->assertSame('1/2', $fields['children']['width']);
	}

	public function testLoad(): void
	{
		$page       = $this->app->page('test');
		$controller = new PageDuplicateDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertIsArray($props['fields']);
		$this->assertSame('Duplicate', $props['submitButton']);
		$this->assertFalse($props['value']['children']);
		$this->assertFalse($props['value']['files']);
		$this->assertSame('test-copy', $props['value']['slug']);
	}

	public function testPath(): void
	{
		$page = $this->app->page('test');
		$controller = new PageDuplicateDialogController($page);
		$this->assertSame('/', $controller->path());

		$page = $this->app->page('test-with-children/test-child');
		$controller = new PageDuplicateDialogController($page);
		$this->assertSame('/test-with-children/', $controller->path());
	}

	public function testSlugTitleSuffixCount(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Test'
						]
					],
					[
						'slug' => 'test-copy',
						'content' => [
							'title' => 'Test Copy'
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = $this->app->page('test-copy');
		$controller = new PageDuplicateDialogController($page);
		$this->assertNull($controller->suffixCount());
		$this->assertSame('test-copy-copy', $controller->slug());
		$this->assertSame('Test Copy Copy', $controller->title());

		$page = $this->app->page('test');
		$controller = new PageDuplicateDialogController($page);
		$this->assertSame(2, $controller->suffixCount());
		$this->assertSame('test-copy2', $controller->slug());
		$this->assertSame('Test Copy 2', $controller->title());
	}

	public function testSlugTitleSuffixCountFurther(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Test'
						]
					],
					[
						'slug' => 'test-copy',
						'content' => [
							'title' => 'Test Copy'
						]
					],
					[
						'slug' => 'test-copy2',
						'content' => [
							'title' => 'Test Copy 2'
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = $this->app->page('test');
		$controller = new PageDuplicateDialogController($page);
		$this->assertSame(3, $controller->suffixCount());
		$this->assertSame('test-copy3', $controller->slug());
		$this->assertSame('Test Copy 3', $controller->title());
	}

	public function testSubmit(): void
	{
		$this->assertCount(0, $this->app->site()->drafts());

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'New Test',
					'slug'  => 'new-test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		// store the dummy page on disk
		// otherwise it cannot be duplicated
		$page = $this->app->page('test');
		$page->update();

		$controller = new PageDuplicateDialogController($page);
		$response   = $controller->submit();

		$this->assertSame('page.duplicate', $response['event']);
		$this->assertSame('/pages/new-test', $response['redirect']);

		$drafts = $this->app->site()->drafts();
		$this->assertCount(1, $drafts);
		$this->assertSame('new-test', $drafts->first()->slug());
	}
}
