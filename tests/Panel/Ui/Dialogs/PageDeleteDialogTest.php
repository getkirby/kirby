<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDeleteDialog::class)]
class PageDeleteDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.PageDeleteDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);
	}

	public function testFor(): void
	{
		$dialog = PageDeleteDialog::for('test');
		$this->assertInstanceOf(PageDeleteDialog::class, $dialog);
		$this->assertSame($this->app->page('test'), $dialog->page());
	}

	public function testRender(): void
	{
		$dialog = PageDeleteDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-remove-dialog', $result['component']);
		$this->assertSame('Do you really want to delete <strong>test</strong>?', $result['props']['text']);
	}

	public function testRenderWithChildren(): void
	{
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

		$dialog = PageDeleteDialog::for('test');
		$result = $dialog->render();
		$props  = $result['props'];
		$this->assertSame('k-form-dialog', $result['component']);
		$this->assertSame('info', $props['fields']['info']['type']);
		$this->assertSame('text', $props['fields']['confirm']['type']);
		$this->assertSame('Do you really want to delete <strong>test</strong>?', $props['text']);
		$this->assertSame('Delete', $props['submitButton']['text']);
		$this->assertSame('medium', $props['size']);

	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => 'pages/test'
				]
			]
		]);

		$dialog = PageDeleteDialog::for('test');
		$this->assertCount(1, $this->app->site()->pages());

		$result = $dialog->submit();
		$this->assertCount(0, $this->app->site()->pages());
		$this->assertSame('/site', $result['redirect']);
		$this->assertSame('page.delete', $result['event']);
	}

	public function testSubmitWithChildren(): void
	{
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
			],
			'request' => [
				'query' => [
					'confirm' => 'test'
				]
			]
		]);

		$dialog = PageDeleteDialog::for('test');
		$this->assertCount(1, $this->app->site()->pages());

		$result = $dialog->submit();
		$this->assertCount(0, $this->app->site()->pages());
		$this->assertSame('page.delete', $result['event']);
	}

	public function testSubmitWithChildrenInvalidConfirm(): void
	{
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
			],
			'request' => [
				'query' => [
					'confirm' => 'wrong'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter the page title to confirm');

		$dialog = PageDeleteDialog::for('test');
		$dialog->submit();
	}
}
