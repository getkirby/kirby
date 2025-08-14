<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDialogController::class)]
#[CoversClass(PageDeleteDialogController::class)]
class PageDeleteDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PageDeleteDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test'],
					[
						'slug' => 'test-with-children',
						'children' => [
							['slug' => 'test-child']
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = PageDeleteDialogController::factory('test');
		$this->assertInstanceOf(PageDeleteDialogController::class, $controller);
		$this->assertSame('test', $controller->page->id());
	}

	public function testLoad(): void
	{
		$page       = $this->app->page('test');
		$controller = new PageDeleteDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(RemoveDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Do you really want to delete <strong>test</strong>?', $props['text']);
	}

	public function testLoadWithChildren(): void
	{
		$page       = $this->app->page('test-with-children');
		$controller = new PageDeleteDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('info', $props['fields']['info']['type']);
		$this->assertSame('text', $props['fields']['check']['type']);
		$this->assertSame('Do you really want to delete <strong>test-with-children</strong>?', $props['text']);
		$this->assertSame('Delete', $props['submitButton']['text']);
		$this->assertSame('negative', $props['submitButton']['theme']);
		$this->assertSame('medium', $props['size']);
	}

	public function testSubmit(): void
	{
		$this->assertCount(2, $this->app->site()->children());

		$page       = $this->app->page('test');
		$controller = new PageDeleteDialogController($page);
		$response   = $controller->submit();

		$this->assertSame('page.delete', $response['event']);
		$this->assertNull($response['redirect']);
		$this->assertCount(1, $this->app->site()->children());
	}

	public function testSubmitWithChildrenUnchecked(): void
	{
		$page = $this->app->page('test-with-children');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.delete.confirm');

		$controller = new PageDeleteDialogController($page);
		$controller->submit();
	}

	public function testSubmitWithChildrenChecked(): void
	{
		$this->assertCount(2, $this->app->site()->children());

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'check' => 'test-with-children'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test-with-children');
		$controller = new PageDeleteDialogController($page);
		$response   = $controller->submit(['check' => 'test-with-children']);

		$this->assertSame('page.delete', $response['event']);
		$this->assertCount(1, $this->app->site()->children());
	}

	public function testSubmitWithReferrer(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => 'pages/test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageDeleteDialogController($page);
		$response   = $controller->submit();
		$this->assertSame('page.delete', $response['event']);
		$this->assertSame('/site', $response['redirect']);
	}
}
