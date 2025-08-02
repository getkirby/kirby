<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageDeleteButton::class)]
class LanguageDeleteButtonTest extends TestCase
{
	public function testButton(): void
	{
		$language = new Language(['code' => 'en']);
		$button   = new LanguageDeleteButton($language);

		$this->assertSame('languages/en/delete', $button->dialog);
	}

	public function testDisabled(): void
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'delete' => true
						]
					]
				],
				'users/user' => [
					'name' => 'user',
					'permissions' => [
						'languages' => [
							'delete' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor'],
				['email' => 'user@getkirby.com', 'role' => 'user']
			]
		]);

		$language = new Language(['code' => 'en']);

		// not authenticated
		$button = new LanguageDeleteButton($language);
		$this->assertTrue($button->disabled);

		// with permission
		$app->impersonate('editor@getkirby.com', function () use ($language) {
			$button = new LanguageDeleteButton($language);
			$this->assertFalse($button->disabled);
		});

		// without permission
		$app->impersonate('user@getkirby.com', function () use ($language) {
			$button = new LanguageDeleteButton($language);
			$this->assertTrue($button->disabled);
		});
	}
}
