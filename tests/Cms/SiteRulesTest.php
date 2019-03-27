<?php

namespace Kirby\Cms;

class SiteRulesTest extends TestCase
{
    public function testChangeTitleWithoutPermissions()
    {
        $permissions = $this->createMock(SitePermissions::class);
        $permissions->method('__call')->with('changeTitle')->willReturn(false);

        $site = $this->createMock(Site::class);
        $site->method('permissions')->willReturn($permissions);

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to change the title');

        SiteRules::changeTitle($site, 'test');
    }

    public function testUpdate()
    {
        $app = new App();
        $app->impersonate('kirby');

        $site = new Site([]);
        $this->assertTrue(SiteRules::update($site, [
            'copyright' => '2018'
        ]));
    }

    public function testUpdateWithoutPermissions()
    {
        $permissions = $this->createMock(SitePermissions::class);
        $permissions->method('__call')->with('update')->willReturn(false);

        $site = $this->createMock(Site::class);
        $site->method('permissions')->willReturn($permissions);

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to update the site');

        SiteRules::update($site, []);
    }
}
