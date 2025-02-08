<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageCreateDialog::class)]
class LanguageCreateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.LanguageCreateDialog';

	public function testFields(): void
	{
		$dialog = new LanguageCreateDialog();
		$fields = $dialog->fields();

		$this->assertArrayHasKey('name', $fields);
		$this->assertArrayHasKey('code', $fields);
		$this->assertArrayHasKey('direction', $fields);
		$this->assertArrayHasKey('locale', $fields);

		$this->assertSame('text', $fields['name']['type']);
		$this->assertSame('text', $fields['code']['type']);
		$this->assertSame('select', $fields['direction']['type']);
		$this->assertSame('text', $fields['locale']['type']);
	}

	public function testProps(): void
	{
		$dialog = new LanguageCreateDialog();
		$props  = $dialog->props();
		$this->assertSame('Add a new language', $props['submitButton']);
		$this->assertCount(4, $props['fields']);
		$this->assertSame(
			[
				'code'      => '',
				'direction' => 'ltr',
				'locale'    => '',
				'name'      => '',
			],
			$props['value']
		);
	}

	public function testRender(): void
	{
		$dialog = new LanguageCreateDialog();
		$result = $dialog->render();
		$this->assertSame('k-language-dialog', $result['component']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				]
			],
			'request' => [
				'query'  => [
					'code' => 'de',
					'name' => 'Deutsch'
				]
			]
		]);

		$this->assertCount(1, $this->app->languages());

		$dialog = new LanguageCreateDialog();
		$result = $dialog->submit();

		$this->assertSame(['event' => 'language.create'], $result);
		$this->assertCount(2, $this->app->languages());
	}
}
