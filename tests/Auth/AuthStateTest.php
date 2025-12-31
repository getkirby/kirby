<?php

namespace Kirby\Auth;

use Kirby\Auth\User as AuthUser;
use Kirby\Cms\App;
use Kirby\Cms\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthStateTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.AuthState';

	protected function authWith(AuthUser $user, Status|null $status): Auth
	{
		return new class ($this->app, $user, $status) extends Auth {
			public function __construct(App $kirby, AuthUser $user, Status|null $status)
			{
				parent::__construct($kirby);
				$this->user   = $user;
				$this->status = $status;
			}

			public function testStatus(): Status|null
			{
				return $this->status;
			}
		};
	}

	public function testFlush(): void
	{
		$authUser = $this->createMock(AuthUser::class);
		$authUser->expects($this->once())->method('flush');

		$auth = $this->authWith($authUser, $this->createStub(Status::class));
		$auth->flush();
		$this->assertNull($auth->testStatus());
	}

	public function testSetUserClearsStatusCache(): void
	{
		$kirbyUser = $this->createStub(User::class);

		$authUser = $this->createMock(AuthUser::class);
		$authUser->expects($this->once())->method('set')->with($kirbyUser);

		$auth = $this->authWith($authUser, $this->createStub(Status::class));
		$auth->setUser($kirbyUser);
		$this->assertNull($auth->testStatus());
	}
}
