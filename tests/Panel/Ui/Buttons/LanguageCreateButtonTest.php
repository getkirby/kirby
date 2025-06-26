<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageCreateButton::class)]
class LanguageCreateButtonTest extends TestCase
{
	public function testButton(): void
	{
		$button = new LanguageCreateButton();

		$this->assertSame('languages/create', $button->dialog);
		$this->assertSame('Add a new language', $button->text);
	}

	public function testDisabled(): void
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'create' => true
						]
					]
				],
				'users/user' => [
					'name' => 'user',
					'permissions' => [
						'languages' => [
							'create' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor'],
				['email' => 'user@getkirby.com', 'role' => 'user']
			]
		]);

		// not authenticated
		$button = new LanguageCreateButton();
		$this->assertTrue($button->disabled);

		// with permission
		$app->impersonate('editor@getkirby.com', function () {
			$button = new LanguageCreateButton();
			$this->assertFalse($button->disabled);
		});

		// without permission
		$app->impersonate('user@getkirby.com', function () {
			$button = new LanguageCreateButton();
			$this->assertTrue($button->disabled);
		});
	}
}
