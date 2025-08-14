<?php

namespace Kirby\Api;

use Kirby\Cms\User;

class UserModelTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Api.UserModel';

	protected User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = new User(['email' => 'test@getkirby.com']);
	}

	public function testFiles(): void
	{
		$user = new User([
			'email' => 'test@getkirby.com',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$model = $this->api->resolve($user)->select('files')->toArray();

		$this->assertSame('a.jpg', $model['files'][0]['filename']);
		$this->assertSame('b.jpg', $model['files'][1]['filename']);
	}

	public function testImage(): void
	{
		$image = $this->attr($this->user, 'panelImage');
		$expected = [
			'back' => 'black',
			'color' => 'gray-500',
			'cover' => false,
			'icon'  => 'user',
			'ratio' => '1/1'
		];

		$this->assertSame($expected, $image);
	}
}
