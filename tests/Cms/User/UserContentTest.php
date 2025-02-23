<?php

namespace Kirby\Cms;


use Kirby\Content\Content;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(User::class)]
class UserContentTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserContent';

	public function testContent(): void
	{
		$user = new User([
			'email'   => 'user@domain.com',
			'content' => $content = ['company' => 'Test']
		]);

		$this->assertSame($content, $user->content()->toArray());
	}

	public function testContentInvalid(): void
	{
		$this->expectException(TypeError::class);

		new User([
			'email'   => 'user@domain.com',
			'content' => 'something'
		]);
	}

	public function testContentDefault(): void
	{
		$user = new User(['email' => 'user@domain.com']);
		$this->assertInstanceOf(Content::class, $user->content());
		$this->assertSame([], $user->content()->toArray());
	}
}
