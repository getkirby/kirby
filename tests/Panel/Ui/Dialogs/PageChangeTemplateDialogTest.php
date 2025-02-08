<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\Exception;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageChangeTemplateDialog::class)]
class PageChangeTemplateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.PageChangeTemplateDialog';

	protected function setUp(): void
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
	}

	public function testFields(): void
	{
		$dialog = PageChangeTemplateDialog::for('test');
		$fields = $dialog->fields();
		$this->assertArrayHasKey('notice', $fields);
		$this->assertArrayHasKey('template', $fields);

		$this->assertSame('Template', $fields['template']['label']);
		$this->assertSame('A', $fields['template']['options'][0]['text']);
		$this->assertSame('a', $fields['template']['options'][0]['value']);
		$this->assertSame('B', $fields['template']['options'][1]['text']);
		$this->assertSame('b', $fields['template']['options'][1]['value']);
	}

	public function testFor(): void
	{
		$dialog = PageChangeTemplateDialog::for('test');
		$this->assertInstanceOf(PageChangeTemplateDialog::class, $dialog);
		$this->assertSame($this->app->page('test'), $dialog->page());
	}

	public function testNoAlternatives(): void
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
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The template for the page "test" cannot be changed');

		PageChangeTemplateDialog::for('test');
	}

	public function testProps(): void
	{
		$dialog = PageChangeTemplateDialog::for('test');
		$props  = $dialog->props();
		$this->assertSame('Change', $props['submitButton']['text']);
		$this->assertSame(['template' => 'a'], $props['value']);
	}

	public function testRender(): void
	{
		$dialog = PageChangeTemplateDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'template' => 'b',
				]
			]
		]);

		$dialog = PageChangeTemplateDialog::for('test');
		$this->assertSame('a', $dialog->page()->intendedTemplate()->name());

		$result = $dialog->submit();
		$this->assertSame('b', $dialog->page()->intendedTemplate()->name());
		$this->assertSame('page.changeTemplate', $result['event']);
	}
}
