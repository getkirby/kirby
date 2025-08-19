<?php

namespace Kirby\Uuid;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Permalink::class)]
class PermalinkTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Uuid.Permalink';

	public function testFor(): void
	{
		$permalink = Permalink::for('page://my-page');
		$this->assertInstanceOf(Permalink::class, $permalink);
		$this->assertInstanceOf(PageUuid::class, $permalink->uuid());

		$this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				],
			]
		]);

		$permalink = Permalink::for('page://my-page');
		$this->assertNull($permalink);
	}

	public function testParse(): void
	{
		$permalink = Permalink::from('https://getkirby.com/@/page/my-page');
		$this->assertInstanceOf(Permalink::class, $permalink);
		$this->assertInstanceOf(PageUuid::class, $permalink->uuid());
	}

	public function testModel(): void
	{
		$permalink = Permalink::for('page://my-page');
		$this->assertInstanceOf(Page::class, $permalink->model());

		$permalink = Permalink::for('file://my-file');
		$this->assertInstanceOf(File::class, $permalink->model());

		Uuids::cache()->flush();

		$permalink = Permalink::for('page://my-page');
		$this->assertNull($permalink->model(true));
	}

	public function testUrl(): void
	{
		$permalink = Permalink::for('page://my-page');
		$url       = 'https://getkirby.com/@/page/my-page';
		$this->assertSame($url, $permalink->url());
		$this->assertSame($url, (string)$permalink);


		$permalink = Permalink::for('file://my-file');
		$url       = 'https://getkirby.com/@/file/my-file';
		$this->assertSame($url, $permalink->url());
		$this->assertSame($url, (string)$permalink);
	}

	public function testUrlWithLanguage(): void
	{
		$this->app->clone([
			'languages' => [
				[
					'code'    => 'de',
					'default' => true,
				]
			]
		]);

		$permalink = Permalink::for('page://my-page');
		$url       = 'https://getkirby.com/de/@/page/my-page';
		$this->assertSame($url, $permalink->url());
		$this->assertSame($url, (string)$permalink);

		$this->app->clone([
			'languages' => [
				[
					'code'    => 'de',
					'url'     => '/',
					'default' => true,
				]
			]
		]);

		$permalink = Permalink::for('page://my-page');
		$url       = 'https://getkirby.com/@/page/my-page';
		$this->assertSame($url, $permalink->url());
		$this->assertSame($url, (string)$permalink);
	}
}
