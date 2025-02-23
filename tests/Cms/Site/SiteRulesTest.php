<?php

namespace Kirby\Cms;


use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteRulesTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteRules';

	public function testChangeTitleWithoutPermissions(): void
	{
		$permissions = $this->createMock(SitePermissions::class);
		$permissions->method('can')->with('changeTitle')->willReturn(false);

		$site = $this->createMock(Site::class);
		$site->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the title');

		SiteRules::changeTitle($site, 'test');
	}

	public function testUpdate(): void
	{
		$app = new App();
		$app->impersonate('kirby');

		$this->expectNotToPerformAssertions();

		$site = new Site([]);
		SiteRules::update($site, ['copyright' => '2018']);
	}

	public function testUpdateWithoutPermissions(): void
	{
		$permissions = $this->createMock(SitePermissions::class);
		$permissions->method('can')->with('update')->willReturn(false);

		$site = $this->createMock(Site::class);
		$site->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the site');

		SiteRules::update($site, []);
	}
}
