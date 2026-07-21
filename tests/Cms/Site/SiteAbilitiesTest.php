<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteAbilities::class)]
class SiteAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteAbilities';

	public function testHasWithoutCheckMethod(): void
	{
		$abilities = new SiteAbilities($this->app->site());

		$this->assertFalse($abilities->has('changeTitle'));
		$this->assertFalse($abilities->has('update'));
	}
}
