<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageDuplicateDialog::class)]
class PageDuplicateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.PageDuplicateDialog';

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

	public function testFields(): void
	{
		$dialog = PageDuplicateDialog::for('test');
		$fields = $dialog->fields();
		$this->assertArrayHasKey('title', $fields);
		$this->assertArrayHasKey('slug', $fields);
		$this->assertArrayNotHasKey('files', $fields);
		$this->assertArrayNotHasKey('children', $fields);

		$this->assertSame('URL appendix', $fields['slug']['label']);
		$this->assertSame('slug', $fields['slug']['type']);
		$this->assertSame('/', $fields['slug']['path']);
	}

	public function testFieldsWithChildren(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'children' => [
							['slug' => 'test']
						]
					]
				]
			]
		]);

		$dialog = PageDuplicateDialog::for('test');
		$fields = $dialog->fields();
		$this->assertArrayHasKey('title', $fields);
		$this->assertArrayHasKey('slug', $fields);
		$this->assertArrayNotHasKey('files', $fields);
		$this->assertArrayHasKey('children', $fields);

		$this->assertSame('toggle', $fields['children']['type']);
		$this->assertSame('Copy pages', $fields['children']['label']);
		$this->assertSame('1/1', $fields['children']['width']);
	}

	public function testFieldsWithFiles(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		$dialog = PageDuplicateDialog::for('test');
		$fields = $dialog->fields();
		$this->assertArrayHasKey('title', $fields);
		$this->assertArrayHasKey('slug', $fields);
		$this->assertArrayHasKey('files', $fields);
		$this->assertArrayNotHasKey('children', $fields);

		$this->assertSame('toggle', $fields['files']['type']);
		$this->assertSame('Copy files', $fields['files']['label']);
		$this->assertSame('1/1', $fields['files']['width']);
	}

	public function testFor(): void
	{
		$dialog = PageDuplicateDialog::for('test');
		$this->assertInstanceOf(PageDuplicateDialog::class, $dialog);
		$this->assertSame($this->app->page('test'), $dialog->page());
	}

	public function testProps(): void
	{
		$dialog = PageDuplicateDialog::for('test');
		$props  = $dialog->props();
		$this->assertSame('Duplicate', $props['submitButton']);
		$this->assertSame([
			'children' => false,
			'files'    => false,
			'slug'     => 'test-copy',
			'title'    => 'test Copy'
		], $props['value']);
	}

	public function testPropsWithSlugCounter(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test'],
					['slug' => 'test-copy'],
					['slug' => 'test-copy2']
				]
			]
		]);

		$dialog = PageDuplicateDialog::for('test');
		$props  = $dialog->props();
		$this->assertSame('test-copy3', $props['value']['slug']);
		$this->assertSame('test Copy 3', $props['value']['title']);
	}

	public function testRender(): void
	{
		$dialog = PageDuplicateDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'Duplicated',
					'slug'  => 'copy'
				]
			]
		]);

		// create the page manually to ensure
		// they exist on disk
		$this->app->site()->createChild([
			'slug'    => 'a',
			'content' => ['foo' => 'bar']
		]);

		$dialog = PageDuplicateDialog::for('a');
		$this->assertSame('bar', $this->app->page('a')->foo()->value());
		$this->assertNull($this->app->page('copy'));

		$result = $dialog->submit();
		$this->assertSame('bar', $this->app->page('a')->foo()->value());
		$this->assertSame('bar', $this->app->page('copy')->foo()->value());
		$this->assertSame('page.duplicate', $result['event']);
		$this->assertSame('/pages/copy', $result['redirect']);
	}
}
