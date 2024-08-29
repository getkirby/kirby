<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\LanguageAddButton
 */
class LanguageAddButtonTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testButton()
	{
		$button = new LanguageAddButton();

		$this->assertSame('languages/create', $button->dialog);
		$this->assertSame('Add a new language', $button->text);
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
		$button = new LanguageAddButton();
		$this->assertTrue($button->disabled);

		// with permission
		$app->impersonate('editor@getkirby.com', function () {
			$button = new LanguageAddButton();
			$this->assertFalse($button->disabled);
		});

		// without permission
		$app->impersonate('user@getkirby.com', function () {
			$button = new LanguageAddButton();
			$this->assertTrue($button->disabled);
		});
	}
}
