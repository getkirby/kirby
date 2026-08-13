<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteAbilities::class)]
class SiteAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.SiteAbilities';

	public function testError(): void
	{
		$abilities = new SiteAbilities(
			model: $this->app->site(),
			user: $this->user()
		);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.site.test');

		$abilities->error('test');
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}
}
