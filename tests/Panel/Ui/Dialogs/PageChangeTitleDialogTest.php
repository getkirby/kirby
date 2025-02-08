<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageChangeTitleDialog::class)]
class PageChangeTitleDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.PageChangeTitleDialog';

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
		$dialog = PageChangeTitleDialog::for('test');
		$fields = $dialog->fields();
		$this->assertArrayHasKey('title', $fields);
		$this->assertArrayHasKey('slug', $fields);

		$this->assertSame('Title', $fields['title']['label']);
		$this->assertFalse($fields['title']['disabled']);
		$this->assertSame('URL appendix', $fields['slug']['label']);
		$this->assertFalse($fields['slug']['disabled']);
		$this->assertSame('/', $fields['slug']['path']);
		$this->assertSame('Create from title', $fields['slug']['wizard']['text']);
		$this->assertSame('title', $fields['slug']['wizard']['field']);
	}

	public function testFieldsWithParent(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'a',
						'children' => [
							['slug' => 'b']
						]
					]
				]
			]
		]);

		$dialog = PageChangeTitleDialog::for('a/b');
		$fields = $dialog->fields();
		$this->assertSame('/a/', $fields['slug']['path']);
	}

	public function testFieldsWithLanguages(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			],
			'languages' => [
				['code' => 'en', 'name' => 'English', 'default' => true],
				['code' => 'de', 'name' => 'Deutsch']
			]
		]);

		$dialog = PageChangeTitleDialog::for('a');
		$fields = $dialog->fields();
		$this->assertSame('en/', $fields['slug']['path']);
	}

	public function testFor(): void
	{
		$dialog = PageChangeTitleDialog::for('test');
		$this->assertInstanceOf(PageChangeTitleDialog::class, $dialog);
		$this->assertSame($this->app->page('test'), $dialog->page());
	}

	public function testProps(): void
	{
		$dialog = PageChangeTitleDialog::for('test');
		$props  = $dialog->props();
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame([
			'title' => 'test',
			'slug'  => 'test'
		], $props['value']);
	}

	public function testRender(): void
	{
		$dialog = PageChangeTitleDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title'     => 'My page',
					'slug'      => 'my-page',
					'_referrer' => 'pages/test'
				]
			]
		]);

		$dialog = PageChangeTitleDialog::for('test');
		$this->assertSame('test', $dialog->page()->title()->value());
		$this->assertSame('test', $dialog->page()->slug());

		$result = $dialog->submit();
		$this->assertSame('My page', $dialog->page()->title()->value());
		$this->assertSame('my-page', $dialog->page()->slug());
		$this->assertSame('/pages/my-page', $result['redirect']);
		$this->assertSame([
			'page.changeTitle',
			'page.changeSlug'
		], $result['event']);
	}

	public function testSubmitNoChanges(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'test',
					'slug'  => 'test'
				]
			]
		]);

		$dialog = PageChangeTitleDialog::for('test');
		$result = $dialog->submit();
		$this->assertSame('test', $dialog->page()->title()->value());
		$this->assertSame('test', $dialog->page()->slug());
		$this->assertTrue($result);
	}

	public function testSubmitNoTitle(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'slug' => 'test'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The title must not be empty');

		$dialog = PageChangeTitleDialog::for('test');
		$dialog->submit();
	}

	public function testSubmitNoSlug(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'Test'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid URL appendix');

		$dialog = PageChangeTitleDialog::for('test');
		$dialog->submit();
	}
}
