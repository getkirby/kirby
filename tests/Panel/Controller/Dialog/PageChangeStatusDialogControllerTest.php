<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Page;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialogs\ErrorDialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

class PageWithErrors extends Page
{
	public function errors(): array
	{
		return [
			['label' => 'Error 1', 'message' => 'Error description 1'],
			['label' => 'Error 2', 'message' => 'Error description 2'],
		];
	}
}

#[CoversClass(PageDialogController::class)]
#[CoversClass(PageChangeStatusDialogController::class)]
class PageChangeStatusDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PageChangeStatusDialogController';

	public function setUp(): void
	{
		parent::setUp();

		Page::$models['errorpage'] = PageWithErrors::class;

		$this->app = $this->app->clone([
			'site' => [
				'drafts' => [
					['slug' => 'c'],
					[
						'slug'     => 'd',
						'model'    => 'errorpage',
						'template' => 'errorpage'
					],
				],
				'children' => [
					['slug' => 'a'],
					['slug' => 'b', 'num' => 1]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		unset(Page::$models['errorpage']);
		parent::tearDown();
	}

	public function testFactory(): void
	{
		$controller = PageChangeStatusDialogController::factory('a');
		$this->assertInstanceOf(PageChangeStatusDialogController::class, $controller);
		$this->assertSame('a', $controller->page->id());
	}

	public function testFields(): void
	{
		$page       = $this->app->page('a');
		$controller = new PageChangeStatusDialogController($page);
		$fields     = $controller->fields();
		$this->assertCount(2, $fields);
		$this->assertSame('Select a new status', $fields['status']['label']);
		$this->assertSame('Draft', $fields['status']['options'][0]['text']);
		$this->assertSame('Unlisted', $fields['status']['options'][1]['text']);
		$this->assertSame('Public', $fields['status']['options'][2]['text']);
		$this->assertSame('Please select a position', $fields['position']['label']);
		$this->assertSame(['status' => 'listed'], $fields['position']['when']);
	}

	public function testFieldsForDraft(): void
	{
		$page       = $this->app->page('c');
		$controller = new PageChangeStatusDialogController($page);
		$fields     = $controller->fields();
		$this->assertCount(2, $fields);
		$this->assertCount(3, $fields['position']['options']);
		$this->assertSame('b', $fields['position']['options'][1]['value']);
	}

	public function testLoad(): void
	{
		$page       = $this->app->page('a');
		$controller = new PageChangeStatusDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();

		$this->assertIsArray($props['fields']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame('unlisted', $props['value']['status']);
		$this->assertSame(2, $props['value']['position']);
	}

	public function testLoadForDraft(): void
	{
		$page       = $this->app->page('c');
		$controller = new PageChangeStatusDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame(2, $props['value']['position']);
	}

	public function testLoadForDraftWithErrors(): void
	{
		$page       = $this->app->page('d');
		$controller = new PageChangeStatusDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(ErrorDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('The page has errors and cannot be published', $props['message']);
		$this->assertSame('Error 1', $props['details'][0]['label']);
		$this->assertSame('Error description 1', $props['details'][0]['message']);
		$this->assertSame('Error 2', $props['details'][1]['label']);
		$this->assertSame('Error description 2', $props['details'][1]['message']);
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

		$page = $this->app->page('a');
		$this->assertSame('unlisted', $page->status());

		$controller = new PageChangeStatusDialogController($page);
		$response   = $controller->submit();

		$this->assertSame('page.changeStatus', $response['event']);

		$page = $this->app->page('a');
		$this->assertSame('listed', $page->status());
		$this->assertSame(2, $page->num());
	}
}
