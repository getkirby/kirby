<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\TestCase;
use Kirby\Cms\User;
use Kirby\Toolkit\HtmlString;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserItem::class)]
class UserItemTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Ui.UserItem';

	protected User $model;

	protected function setUp(): void
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
			'image'       => [
				'back'  => 'black',
				'color' => 'gray-500',
				'cover' => false,
				'icon'  => 'user',
				'ratio' => '1/1'
			],
			'info'        => new HtmlString('Nobody'),
			'layout'      => 'list',
			'text'        => new HtmlString('test@getkirby.com'),
			'id'          => 'test',
			'link'        => '/users/test',
			'permissions' => [
				'access'         => false,
				'create'         => false,
				'changeEmail'    => false,
				'changeLanguage' => false,
				'changeName'     => false,
				'changePassword' => false,
				'changeRole'     => false,
				'delete'         => false,
				'list'           => false,
				'update'         => false,
			],
			'uuid'         => 'user://test',
		];

		$this->assertEquals($expected, $item->props()); // -ignore-line
	}
}
