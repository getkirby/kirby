<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Page;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDialogController::class)]
#[CoversClass(PageMoveDialogController::class)]
class PageMoveDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PageMoveDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'a',
						'content'  => ['uuid' => 'a'],
						'children' => [
							['slug' => 'test', 'content' => ['uuid' => 'test']]
						]
					],
					[
						'slug' => 'b',
						'content' => ['uuid' => 'b'],
						'template' => 'test'
					]
				]
			],
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						'pages' => ['type' => 'pages']
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = PageMoveDialogController::factory('a/test');
		$this->assertInstanceOf(PageMoveDialogController::class, $controller);
		$this->assertSame('a/test', $controller->page->id());
	}

	public function testLoad(): void
	{
		$page       = $this->app->page('a/test');
		$controller = new PageMoveDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);
		$this->assertSame('k-page-move-dialog', $dialog->component);

		$props = $dialog->props();
		$this->assertSame('page://a', $props['value']['parent']);
		$this->assertSame('/pages/a+test', $props['value']['move']);
	}

	public function testParent(): void
	{
		$page       = $this->app->page('a/test');
		$controller = new PageMoveDialogController($page);
		$this->assertSame('page://a', $controller->parent());

		$page       = $this->app->page('a');
		$controller = new PageMoveDialogController($page);
		$this->assertSame('site://', $controller->parent());

		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			]
		]);

		$page       = $this->app->page('a/test');
		$controller = new PageMoveDialogController($page);
		$this->assertSame('a', $controller->parent());

		$page       = $this->app->page('a');
		$controller = new PageMoveDialogController($page);
		$this->assertSame('/', $controller->parent());
	}

	public function testSubmit(): void
	{
		$parentA = Page::create([
			'slug'     => 'parent-a',
			'template' => 'test'
		]);

		$parentB = Page::create([
			'slug'     => 'parent-b',
			'template' => 'test'
		]);

		$child = Page::create([
			'parent' => $parentA,
			'slug'   => 'child'
		]);

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'parent-b'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageMoveDialogController($child);
		$response   = $controller->submit();
		$this->assertSame('page.move', $response['event']);
	}
}
