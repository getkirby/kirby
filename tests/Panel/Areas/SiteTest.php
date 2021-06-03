<?php

namespace Kirby\Panel\Areas;

class SiteTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
    }

    public function testSiteWithoutAuthentication(): void
    {
        $this->assertRedirect('site', 'login');
    }

    public function testSiteRedirectFromHome(): void
    {
        $this->login();
        $this->assertRedirect('/', 'site');
    }

    public function testSite(): void
    {
        $this->login();

        $view  = $this->view('site');
        $props = $view['props'];

        $this->assertSame('site', $view['id']);
        $this->assertSame('Site', $view['title']);
        $this->assertSame('k-site-view', $view['component']);

        // TODO: add more props tests
    }
}
