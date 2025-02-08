<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Page;
use Kirby\Panel\Ui\TestCase;
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

#[CoversClass(PageChangeStatusDialog::class)]
class PageChangeStatusDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.PageChangeStatusDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'drafts' => [
					['slug' => 'a'],
				],
				'children' => [
					['slug' => 'b', 'num' => 1],
					['slug' => 'c', 'num' => 2],
					['slug' => 'd', 'num' => 3]
				]
			]
		]);
	}

	public function testFields(): void
	{
		$dialog = PageChangeStatusDialog::for('b');
		$fields = $dialog->fields();
		$this->assertArrayHasKey('status', $fields);
		$this->assertArrayHasKey('position', $fields);

		$this->assertSame('Select a new status', $fields['status']['label']);
		$this->assertSame('Draft', $fields['status']['options'][0]['text']);
		$this->assertSame('Unlisted', $fields['status']['options'][1]['text']);
		$this->assertSame('Public', $fields['status']['options'][2]['text']);
		$this->assertSame('Please select a position', $fields['position']['label']);
		$this->assertSame(['status' => 'listed'], $fields['position']['when']);
	}

	public function testFor(): void
	{
		$dialog = PageChangeStatusDialog::for('b');
		$this->assertInstanceOf(PageChangeStatusDialog::class, $dialog);
		$this->assertSame($this->app->page('b'), $dialog->page());
	}

	public function testRender(): void
	{
		$dialog = PageChangeStatusDialog::for('b');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
		$this->assertSame('Change', $result['props']['submitButton']);
		$this->assertSame([
			'status'   => 'listed',
			'position' => 1
		], $result['props']['value']);
	}

	public function testRenderDraft(): void
	{
		$dialog = PageChangeStatusDialog::for('a');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
		$this->assertSame('Change', $result['props']['submitButton']);
		$this->assertSame([
			'status'   => 'draft',
			'position' => 4
		], $result['props']['value']);
	}

	public function testRenderDraftWithErrors(): void
	{
		Page::$models['errorpage'] = PageWithErrors::class;

		$this->app = $this->app->clone([
			'site' => [
				'drafts' => [
					[
						'slug'     => 'a',
						'model'    => 'errorpage',
						'template' => 'errorpage'
					],
				]
			]
		]);

		$dialog = PageChangeStatusDialog::for('a');
		$result = $dialog->render();
		$this->assertSame('k-error-dialog', $result['component']);
		$this->assertSame('The page has errors and cannot be published', $result['props']['message']);
		$this->assertSame('Error 1', $result['props']['details'][0]['label']);
		$this->assertSame('Error 2', $result['props']['details'][1]['label']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'status'  => 'listed',
					'position' => 2
				]
			]
		]);

		$dialog = PageChangeStatusDialog::for('a');
		$this->assertSame('draft', $dialog->page()->status());
		$this->assertNull($dialog->page()->num());

		$result = $dialog->submit();
		$this->assertSame('listed', $dialog->page()->status());
		$this->assertSame(2, $dialog->page()->num());
		$this->assertSame('page.changeStatus', $result['event']);
	}
}
