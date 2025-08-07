<?php

namespace Kirby\Panel\Areas;

class SiteTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
	}

	public function testPageWithoutAuthentication(): void
	{
		$this->assertRedirect('pages/home', 'login');
	}

	public function testPageWithMissingModel(): void
	{
		$this->login();
		$this->assertErrorView('pages/does-not-exist', 'The page "does-not-exist" cannot be found');
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
}
