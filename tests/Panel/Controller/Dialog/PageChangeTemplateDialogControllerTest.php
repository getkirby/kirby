<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\Exception;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDialogController::class)]
#[CoversClass(PageChangeTemplateDialogController::class)]
class PageChangeTemplateDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PageChangeTemplateDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'a'
					]
				]
			],
			'blueprints' => [
				'pages/a' => [
					'title' => 'A',
					'options' => [
						'changeTemplate' => [
							'b'
						]
					]
				],
				'pages/b' => [
					'title' => 'B',
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = PageChangeTemplateDialogController::factory('test');
		$this->assertInstanceOf(PageChangeTemplateDialogController::class, $controller);
		$this->assertSame('test', $controller->page->id());
	}

	public function testLoad(): void
	{
		$page       = $this->app->page('test');
		$controller = new PageChangeTemplateDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Template', $props['fields']['template']['label']);

		$this->assertSame('A', $props['fields']['template']['options'][0]['text']);
		$this->assertSame('a', $props['fields']['template']['options'][0]['value']);
		$this->assertSame('B', $props['fields']['template']['options'][1]['text']);
		$this->assertSame('b', $props['fields']['template']['options'][1]['value']);

		$this->assertSame('Change', $props['submitButton']['text']);
		$this->assertSame('notice', $props['submitButton']['theme']);
		$this->assertSame('a', $props['value']['template']);
	}

	public function testLoadWithoutAlternatives(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'a'
					]
				]
			],
			'blueprints' => null
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTemplateDialogController($page);

		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.page.changeTemplate.invalid');

		$controller->load();
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'a'
					]
				]
			],
			'blueprints' => [
				'pages/a' => [
					'title' => 'A',
					'options' => [
						'changeTemplate' => [
							'b'
						]
					]
				],
				'pages/b' => [
					'title' => 'B',
				]
			],
			'request' => [
				'query' => [
					'template' => 'b'
				]
			]
		]);

		$this->app->impersonate('kirby');

		// store page first to be able to change the template
		$page = $this->app->page('test');
		$page = $page->update();

		$controller = new PageChangeTemplateDialogController($page);
		$response   = $controller->submit();

		$this->assertSame('page.changeTemplate', $response['event']);

		$page = $this->app->page('test');
		$this->assertSame('b', $page->intendedTemplate()->name());
	}
}
