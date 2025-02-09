<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageSettingsButton::class)]
class LanguageSettingsButtonTest extends TestCase
{
	public function testButton()
	{
		$language = new Language(['code' => 'en']);
		$button   = new LanguageSettingsButton($language);

		$this->assertSame('languages/en/update', $button->dialog);
	}

	public function testDisabled()
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'update' => true
						]
					]
				],
				'users/user' => [
					'name' => 'user',
					'permissions' => [
						'languages' => [
							'update' => false
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
		$button = new LanguageSettingsButton($language);
		$this->assertTrue($button->disabled);

		// with permission
		$app->impersonate('editor@getkirby.com', function () use ($language) {
			$button = new LanguageSettingsButton($language);
			$this->assertFalse($button->disabled);
		});

		// without permission
		$app->impersonate('user@getkirby.com', function () use ($language) {
			$button = new LanguageSettingsButton($language);
			$this->assertTrue($button->disabled);
		});
	}
}
