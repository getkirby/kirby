<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SitePagesTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SitePages';

	public function testErrorPage(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'error']
			]
		]);

		$this->assertIsPage('error', $site->errorPage());
	}

	public function testHomePage(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertIsPage('home', $site->homePage());
	}

	public function testPage(): void
	{
		$site = new Site([
			'page' => $page = new Page(['slug' => 'test'])
		]);

		$this->assertIsPage($page, $site->page());
	}

	public function testDefaultPageWithChildren(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertIsPage('home', $site->page());
	}

	public function testPageWithPathAndChildren(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'test']
			]
		]);

		$this->assertIsPage('test', $site->page('test'));
	}

	public function testPages(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar']
			]
		]);

		$collection = $site->pages();
		$this->assertCount(3, $collection);
	}
}
