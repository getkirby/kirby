<?php

namespace Kirby\Cms;

use Kirby\Panel\Site as Panel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Site';

	public function testApiUrl(): void
	{
		$site = new Site();

		$this->assertSame('/api/site', $site->apiUrl());
		$this->assertSame('site', $site->apiUrl(true));
	}

	public function testIs(): void
	{
		$appA = new App([
			'roots' => [
				'index' => '/dev/null/a',
			]
		]);

		$appB = new App([
			'roots' => [
				'index' => '/dev/null/b',
			]
		]);

		$a = $appA->site();
		$b = $appB->site();
		$c = new Page(['slug' => 'test']);

		$this->assertTrue($a->is($a));
		$this->assertFalse($a->is($b));
		$this->assertFalse($a->is($c));
		$this->assertFalse($b->is($c));
	}

	public function testToArray(): void
	{
		$site = new Site();
		$data = $site->toArray();

		$this->assertCount(9, $data);
		$this->assertArrayHasKey('children', $data);
		$this->assertArrayHasKey('content', $data);
		$this->assertArrayHasKey('errorPage', $data);
		$this->assertArrayHasKey('files', $data);
		$this->assertArrayHasKey('homePage', $data);
		$this->assertArrayHasKey('page', $data);
		$this->assertArrayHasKey('title', $data);
		$this->assertArrayHasKey('translations', $data);
		$this->assertArrayHasKey('url', $data);

		$this->assertSame([], $data['children']);
		$this->assertSame([], $data['content']);
		$this->assertFalse($data['errorPage']);
		$this->assertSame([], $data['files']);
		$this->assertFalse($data['homePage']);
		$this->assertFalse($data['page']);
		$this->assertNull($data['title']);
		$this->assertSame([
			'en' => [
				'code'    => 'en',
				'content' => [],
				'exists'  => false,
				'slug'    => null
			]
		], $data['translations']);
		$this->assertSame('/', $data['url']);
	}

	public function testToString(): void
	{
		$site   = new Site(['url' => 'https://getkirby.com']);
		$string = $site->toString('{{ site.url }}');
		$this->assertSame('https://getkirby.com', $string);
	}

	public function testPanel()
	{
		$site = new Site();
		$this->assertInstanceOf(Panel::class, $site->panel());
	}

	public function testQuery(): void
	{
		$site = new Site([
			'content' => [
				'title' => 'Mægazine',
			]
		]);

		$this->assertSame('Mægazine', $site->query('site.title')->value());
		$this->assertSame('Mægazine', $site->query('model.title')->value());
	}
}
