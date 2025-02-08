<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageMoveDialog::class)]
class PageMoveDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.PageMoveDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test'
					],
					[
						'slug'     => 'target',
						'template' => 'parent'
					]
				]
			],
			'blueprints' => [
				'pages/parent' => [
					'sections' => [
						'subpages' => [
							'type'     => 'pages',
							'template' => 'default'
						]
					]
				]
			],
		]);
	}

	public function testFor(): void
	{
		$dialog = PageMoveDialog::for('test');
		$this->assertInstanceOf(PageMoveDialog::class, $dialog);
		$this->assertSame($this->app->page('test'), $dialog->page());
	}

	public function testProps(): void
	{
		$dialog = PageMoveDialog::for('test');
		$props  = $dialog->props();
		$this->assertSame([
			'move'   => '/pages/test',
			'parent' => 'site://'
		], $props['value']);
	}

	public function testPropsDisabledUuids(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			]
		]);

		$dialog = PageMoveDialog::for('test');
		$props  = $dialog->props();
		$this->assertSame([
			'move'   => '/pages/test',
			'parent' => '/'
		], $props['value']);
	}

	public function testRender(): void
	{
		$dialog = PageMoveDialog::for('test');
		$result = $dialog->render();
		$this->assertSame('k-page-move-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'b'
				]
			]
		]);

		// create the page manually to ensure
		// they exist on disk
		$this->app->site()->createChild([
			'slug'     => 'a',
			'template' => 'default'
		]);

		$this->app->site()->createChild([
			'slug'     => 'b',
			'template' => 'parent'
		]);

		$dialog = PageMoveDialog::for('a');
		$this->assertNull($dialog->page()->parent());

		$result = $dialog->submit();
		$this->assertSame('b', $dialog->page()->parent()->id());
		$this->assertSame('page.move', $result['event']);
		$this->assertSame('/pages/b+a', $result['redirect']);
	}
}
