<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated 6.0.0
 */
#[CoversClass(User::class)]
class UserChangeTotpTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserChangeTotp';

	public function testChangeTotp(): void
	{
		$file = static::TMP . '/site/accounts/admin/.htpasswd';
		F::write($file, 'a very secure hash');

		$user = new User(['id' => 'admin']);
		$this->assertNull($user->secret('totp'));

		$user->changeTotp('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		$this->assertSame(
			"a very secure hash\n" . '{"totp":"ABCDEFGHIJKLMNOPQRSTUVWXYZ234567"}',
			F::read($file)
		);
		$this->assertSame('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', $user->secret('totp'));

		$user->changeTotp(null);
		$this->assertSame('a very secure hash', F::read($file));
		$this->assertNull($user->secret('totp'));
	}
}
