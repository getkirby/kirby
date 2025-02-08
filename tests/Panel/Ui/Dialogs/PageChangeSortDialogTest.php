<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\PermissionException;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageChangeSortDialog::class)]
class PageChangeSortDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.PageChangeSortDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'num'  => 1
					],
					[
						'slug' => 'b',
						'num'  => 2
					]
				]
			]
		]);
	}

	public function testFor(): void
	{
		$dialog = PageChangeSortDialog::for('a');
		$this->assertInstanceOf(PageChangeSortDialog::class, $dialog);
		$this->assertSame($this->app->page('a'), $dialog->page());
	}

	public function testForNonDefaultNumMode(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/foo' => [
					'num' => 'date'
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'a',
						'template' => 'foo'
					]
				]
			]
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The page "a" cannot be sorted');

		PageChangeSortDialog::for('a');
	}

	public function testProps(): void
	{
		$dialog = PageChangeSortDialog::for('a');
		$props   = $dialog->props();
		$this->assertArrayHasKey('position', $props['fields']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame(1, $props['value']['position']);
	}

	public function testRender(): void
	{
		$dialog = PageChangeSortDialog::for('a');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'position' => 2
				]
			]
		]);

		$dialog = PageChangeSortDialog::for('a');
		$this->assertSame(1, $dialog->page()->num());

		$result = $dialog->submit();
		$this->assertSame(2, $dialog->page()->num());
		$this->assertSame('page.sort', $result['event']);
	}
}
