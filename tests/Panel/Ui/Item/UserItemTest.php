<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\TestCase;
use Kirby\Cms\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserItem::class)]
class UserItemTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.UserItem';

	protected User $model;

	public function setUp(): void
	{
		parent::setUp();
		$this->model = new User(['email' => 'test@getkirby.com', 'id' => 'test']);
	}

	public function testComponent(): void
	{
		$item = new UserItem(user: $this->model);
		$this->assertSame('k-item', $item->component());
	}

	public function testProps(): void
	{
		$item = new UserItem(user: $this->model);

		$expected = [
			'id'          => 'test',
			'image'       => [
				'back'  => 'black',
				'color' => 'gray-500',
				'cover' => false,
				'icon'  => 'user',
				'ratio' => '1/1'
			],
			'info'        => 'Nobody',
			'link'        => '/users/test',
			'permissions' => [
				'create'         => false,
				'changeEmail'    => false,
				'changeLanguage' => false,
				'changeName'     => false,
				'changePassword' => false,
				'changeRole'     => false,
				'delete'         => false,
				'update'         => false,
			],
			'text'         => 'test@getkirby.com',
			'uuid'         => 'user://test',
		];

		$this->assertSame($expected, $item->props());
	}
}
