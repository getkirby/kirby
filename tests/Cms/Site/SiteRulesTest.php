<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteRules::class)]
class SiteRulesTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.SiteRules';

	public function testChangeTitle(): void
	{
		$this->app->impersonate('kirby');

		$this->expectNotToPerformAssertions();

		SiteRules::changeTitle($this->app->site(), 'test');
	}

	public function testChangeTitleWithEmptyTitle(): void
	{
		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.site.changeTitle.empty');

		SiteRules::changeTitle($this->app->site(), '');
	}

	public function testChangeTitleWithoutPermissions(): void
	{
		$this->app->impersonate('nobody');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the title');

		SiteRules::changeTitle($this->app->site(), 'test');
	}

	public function testUpdate(): void
	{
		$this->app->impersonate('kirby');

		$this->expectNotToPerformAssertions();

		SiteRules::update($this->app->site(), ['copyright' => '2018']);
	}

	public function testUpdateWithoutPermissions(): void
	{
		$this->app->impersonate('nobody');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the site');

		SiteRules::update($this->app->site(), []);
	}
}
