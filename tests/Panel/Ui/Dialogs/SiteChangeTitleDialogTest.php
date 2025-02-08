<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteChangeTitleDialog::class)]
class SiteChangeTitleDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.SiteChangeTitleDialog';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'content' => [
					'title' => 'My Site'
				]
			]
		]);
	}

	public function testProps(): void
	{
		$dialog = new SiteChangeTitleDialog();
		$props  = $dialog->props();
		$this->assertArrayHasKey('title', $props['fields']);
		$this->assertSame('My Site', $props['value']['title']);
	}

	public function testRender(): void
	{
		$dialog = new SiteChangeTitleDialog();
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'title' => 'My Other Site',
				]
			]
		]);

		$dialog = new SiteChangeTitleDialog();
		$this->assertSame('My Site', $dialog->site()->title()->value());

		$result = $dialog->submit();
		$this->assertSame('My Other Site', $dialog->site()->title()->value());
		$this->assertSame('site.changeTitle', $result['event']);
	}
}
