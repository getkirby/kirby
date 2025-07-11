<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\LanguageDeleteButton
 */
class LanguageDeleteButtonTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testButton()
	{
		$language = new Language(['code' => 'en']);
		$button   = new LanguageDeleteButton($language);

		$this->assertSame('languages/en/delete', $button->dialog);
	}

	/**
	 * @covers ::__construct
	 */
	public function testDisabled()
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
