<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Panel\Site as Panel;
use PHPUnit\Framework\Attributes\DataProvider;

class SiteTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Site';

	public function testUrl()
	{
		$site = new Site([
			'url' => $url = 'https://getkirby.com'
		]);

		$this->assertSame($url, $site->url());
		$this->assertSame($url, $site->__toString());
	}

	public function testToString()
	{
		$site = new Site(['url' => 'https://getkirby.com']);
		$this->assertSame('https://getkirby.com', $site->toString('{{ site.url }}'));
	}

	public function testBreadcrumb()
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'home',
				],
				[
					'slug' => 'grandma',
					'children' => [
						[
							'slug' => 'mother',
							'children' => [
								[
									'slug' => 'child'
								]
							]
						]
					]
				]
			]
		]);

		$site->visit('grandma/mother/child');

		$crumb = $site->breadcrumb();

		$this->assertSame($site->find('home'), $crumb->nth(0));
		$this->assertSame($site->find('grandma'), $crumb->nth(1));
		$this->assertSame($site->find('grandma/mother'), $crumb->nth(2));
		$this->assertSame($site->find('grandma/mother/child'), $crumb->nth(3));
	}

	public function testBreadcrumbSideEffects()
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'home',
				],
				[
					'slug' => 'grandma',
					'children' => [
						[
							'slug' => 'mother',
							'children' => [
								[
									'slug' => 'child-a'
								],
								[
									'slug' => 'child-b'
								],
								[
									'slug' => 'child-c'
								]
							]
						]
					]
				]
			]
		]);

		$page  = $site->visit('grandma/mother/child-b');
		$crumb = $site->breadcrumb();

		$this->assertSame($site->find('home'), $crumb->nth(0));
		$this->assertSame($site->find('grandma'), $crumb->nth(1));
		$this->assertSame($site->find('grandma/mother'), $crumb->nth(2));
		$this->assertSame($site->find('grandma/mother/child-b'), $crumb->nth(3));

		$this->assertSame('child-a', $page->prev()->slug());
		$this->assertSame('child-c', $page->next()->slug());
	}

	public function testModified()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			]
		]);

		// create the site file
		F::write($file = static::TMP . '/site.txt', 'test');

		$modified = filemtime($file);
		$site     = $app->site();

		$this->assertSame($modified, $site->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $site->modified($format));

		// custom date handler
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $site->modified($format, 'strftime'));
	}

	public function testModifiedInMultilangInstallation()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			]
		]);

		// create the english site
		F::write($file = static::TMP . '/site.en.txt', 'test');
		touch($file, $modified = \time() + 2);

		$this->assertSame($modified, $app->site()->modified());

		// create the german site
		F::write($file = static::TMP . '/site.de.txt', 'test');
		touch($file, $modified = \time() + 5);

		// change the language
		$app->setCurrentLanguage('de');
		$app->setCurrentTranslation('de');

		$this->assertSame($modified, $app->site()->modified());
	}

	public function testIs()
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


	public static function previewUrlProvider(): array
	{
		return [
			[null, '/'],
			['https://test.com', 'https://test.com'],
			['{{ site.url }}#test', '/#test'],
			[false, null],
			[null, null, false],
		];
	}

	#[DataProvider('previewUrlProvider')]
	public function testCustomPreviewUrl(
		string|bool|null $input,
		string|null $expected,
		bool $authenticated = true
	): void {
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => '/'
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		if ($authenticated) {
			$app->impersonate('test@getkirby.com');
		}

		$options = [];

		if ($input !== null) {
			$options = [
				'preview' => $input
			];
		}

		// simple
		$site = new Site([
			'blueprint' => [
				'name'    => 'site',
				'options' => $options
			]
		]);

		$this->assertSame($expected, $site->previewUrl());
	}

	public function testToArray()
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
		$this->assertSame([], $data['translations']);
		$this->assertSame('/', $data['url']);
	}

	public function testPanel()
	{
		$site = new Site();
		$this->assertInstanceOf(Panel::class, $site->panel());
	}

	public function testQuery()
	{
		$site = new Site([
			'content' => [
				'title' => 'Mægazine',
			]
		]);

		$this->assertSame('Mægazine', $site->query('site.title')->value());
		$this->assertSame('Mægazine', $site->query('model.title')->value());
	}

	public function testApiUrl()
	{
		$site = new Site();

		$this->assertSame('/api/site', $site->apiUrl());
		$this->assertSame('site', $site->apiUrl(true));
	}
}
