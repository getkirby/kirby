<?php

namespace Kirby\Panel\Areas;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class AccountDrawersTest extends TestCase
{
	public function testMirrorsUserDrawers(): void
	{
		// the account area should re-expose every user drawer
		// under its own pattern
		$root    = dirname(__DIR__, 3);
		$users   = require $root . '/config/areas/users/drawers.php';
		$account = require $root . '/config/areas/account/drawers.php';

		foreach ($users as $key => $drawer) {
			$mirror = 'account.' . substr($key, strlen('user.'));

			$this->assertArrayHasKey(
				$mirror,
				$account,
				'Missing account drawer for ' . $key
			);
			$this->assertSame($drawer['action'], $account[$mirror]['action']);
		}
	}
}
