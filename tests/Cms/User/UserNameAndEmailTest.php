<?php

namespace Kirby\Cms;


use Kirby\Content\Field;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(User::class)]
class UserNameAndEmailTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserNameAndEmail';

	public function testEmail(): void
	{
		$user = new User([
			'email' => $email = 'user@domain.com',
		]);

		$this->assertSame($email, $user->email());
	}

	public function testInvalidEmail(): void
	{
		$this->expectException(TypeError::class);
		new User(['email' => []]);
	}

	public function testName(): void
	{
		$user = new User([
			'name' => $name = 'Homer Simpson',
		]);

		$this->assertInstanceOf(Field::class, $user->name());
		$this->assertSame($name, $user->name()->value());
	}

	public function testNameSanitized(): void
	{
		$user = new User([
			'name' => '<strong>Homer</strong> Simpson',
		]);

		$this->assertInstanceOf(Field::class, $user->name());
		$this->assertSame('Homer Simpson', $user->name()->value());
	}

	public function testNameOrEmail(): void
	{
		$user = new User([
			'email' => $email = 'homer@simpsons.com',
			'name'  => $name = 'Homer Simpson',
		]);

		$this->assertInstanceOf(Field::class, $user->nameOrEmail());
		$this->assertSame($name, $user->nameOrEmail()->value());
		$this->assertSame('name', $user->nameOrEmail()->key());

		$user = new User([
			'email' => $email = 'homer@simpsons.com',
			'name'  => ''
		]);

		$this->assertInstanceOf(Field::class, $user->nameOrEmail());
		$this->assertSame($email, $user->nameOrEmail()->value());
		$this->assertSame('email', $user->nameOrEmail()->key());
	}
}
