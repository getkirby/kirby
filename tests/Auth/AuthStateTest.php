<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Cms\User as CmsUser;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthStateTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.AuthState';

	protected function auth(User $user, Status|null $status): Auth
	{
		return new class ($this->app, $user, $status) extends Auth {
			public function __construct(
				App $kirby,
				protected User $user,
				protected Status|null $status
			) {
				parent::__construct($kirby);
				// override dependencies with test doubles
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
		$user = $this->createMock(User::class);
		$user->expects($this->once())->method('flush');

		$auth = $this->auth($user, $this->createStub(Status::class));
		$auth->flush();
		$this->assertNull($auth->testStatus());
	}

	public function testSetUserClearsStatusCache(): void
	{
		$kirbyUser = $this->createStub(CmsUser::class);

		$authUser = $this->createMock(User::class);
		$authUser->expects($this->once())->method('set')->with($kirbyUser);

		$status = $this->createStub(Status::class);
		$auth   = $this->auth($authUser, $status);
		$auth->setUser($kirbyUser);
		$this->assertNull($auth->testStatus());
	}
}
