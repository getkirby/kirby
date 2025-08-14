<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDialogController::class)]
#[CoversClass(PageChangeTitleDialogController::class)]
class PageChangeTitleDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PageChangeTitleDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
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
		$controller = PageChangeTitleDialogController::factory('test');
		$this->assertInstanceOf(PageChangeTitleDialogController::class, $controller);
		$this->assertSame('test', $controller->page->id());
	}

	public function testLoad(): void
	{
		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Title', $props['fields']['title']['label']);
		$this->assertFalse($props['fields']['title']['disabled']);
		$this->assertSame('URL appendix', $props['fields']['slug']['label']);
		$this->assertFalse($props['fields']['slug']['disabled']);
		$this->assertSame('/', $props['fields']['slug']['path']);
		$this->assertSame('Create from title', $props['fields']['slug']['wizard']['text']);
		$this->assertSame('title', $props['fields']['slug']['wizard']['field']);

		$this->assertSame('test', $props['value']['title']);
		$this->assertSame('test', $props['value']['slug']);

		$this->assertSame('Change', $props['submitButton']);
	}

	public function testPath(): void
	{
		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);
		$this->assertSame('/', $controller->path());

		$page       = $this->app->page('test/test-child');
		$controller = new PageChangeTitleDialogController($page);
		$this->assertSame('/test/', $controller->path());
	}

	public function testPathMultilang(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				['code' => 'en'],
				['code' => 'de'],
			]
		]);

		$page       = $this->app->page('test/test-child');
		$controller = new PageChangeTitleDialogController($page);
		$this->assertSame('en/test/', $controller->path());
	}

	public function testSubmitWithChangedTitle(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'New title',
					'slug'  => 'test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);
		$response   = $controller->submit();
		$this->assertSame(['page.changeTitle'], $response['event']);

		$page = $this->app->page('test');
		$this->assertSame('New title', $page->title()->value());
	}

	public function testSubmitWithChangedSlug(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'test',
					'slug'  => 'new-test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);
		$response   = $controller->submit();
		$this->assertSame(['page.changeSlug'], $response['event']);

		$page = $this->app->page('new-test');
		$this->assertSame('new-test', $page->slug());
	}

	public function testSubmitWithChangedTitleAndSlug(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'New title',
					'slug'  => 'new-test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);
		$response   = $controller->submit();
		$this->assertSame(['page.changeTitle', 'page.changeSlug'], $response['event']);

		$page = $this->app->page('new-test');
		$this->assertSame('New title', $page->title()->value());
		$this->assertSame('new-test', $page->slug());
	}

	public function testSubmitWithoutChanges(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'test',
					'slug'  => 'test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);
		$response   = $controller->submit();

		$this->assertSame([], $response['event']);
	}

	public function testSubmitWithoutTitle(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'slug' => 'new-test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$controller->submit();
	}

	public function testSubmitWithoutSlug(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'New title'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$controller->submit();
	}

	public function testSubmitWithReferrer(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'test',
					'slug'  => 'new-test',
					'_referrer' => '/pages/test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page       = $this->app->page('test');
		$controller = new PageChangeTitleDialogController($page);
		$response   = $controller->submit();
		$this->assertSame(['page.changeSlug'], $response['event']);
		$this->assertSame('/pages/new-test', $response['redirect']);
	}
}
