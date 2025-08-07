<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDialogController::class)]
#[CoversClass(PageChangeSortDialogController::class)]
class PageChangeSortDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PageChangeSortDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'children' => [
							['slug' => 'a'],
							['slug' => 'b', 'num' => 1],
							['slug' => 'c', 'num' => 2]
						],
					],
					[
						'slug'     => 'test-with-num-0',
						'template' => 'test'
					]
				]
			],
			'blueprints' => [
				'pages/test' => [
					'num' => 0
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = PageChangeSortDialogController::factory('test/a');
		$this->assertInstanceOf(PageChangeSortDialogController::class, $controller);
		$this->assertSame('test/a', $controller->page->id());
	}

	public function testLoad(): void
	{
		$page       = $this->app->page('test/a');
		$controller = new PageChangeSortDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Please select a position', $props['fields']['position']['label']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame(3, $props['value']['position']);
	}

	public function testLoadDisabled(): void
	{
		$page       = $this->app->page('test-with-num-0');
		$controller = new PageChangeSortDialogController($page);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.sort.permission');

		$controller->load();
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'status' => 'listed'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test/a');
		$controller = new PageChangeSortDialogController($page);
		$response   = $controller->submit();

		$this->assertSame('page.sort', $response['event']);
		$page = $this->app->page('test/a');
		$this->assertSame('listed', $page->status());
		$this->assertSame(3, $page->num());
	}
}
