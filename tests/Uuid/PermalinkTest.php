<?php

namespace Kirby\Uuid;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Permalink::class)]
class PermalinkTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Uuid.Permalink';

	public function testFrom(): void
	{
		$permalink = Permalink::from('page://my-page');
		$this->assertInstanceOf(Permalink::class, $permalink);
		$this->assertInstanceOf(PageUuid::class, $permalink->uuid());
	}

	public function testModel(): void
	{
		$permalink = Permalink::from('page://my-page');
		$this->assertInstanceOf(Page::class, $permalink->model());

		$permalink = Permalink::from('file://my-file');
		$this->assertInstanceOf(File::class, $permalink->model());

		Uuids::cache()->flush();

		$permalink = Permalink::from('page://my-page');
		$this->assertNull($permalink->model(true));
	}

	public function testParse(): void
	{
		$permalink = Permalink::parse('https://getkirby.com/@/page/my-page');
		$this->assertInstanceOf(Permalink::class, $permalink);
		$this->assertInstanceOf(PageUuid::class, $permalink->uuid());
	}

	public function testUrl(): void
	{
		$permalink = Permalink::from('page://my-page');
		$this->assertSame('https://getkirby.com/@/page/my-page', $permalink->url());
	}

	public function testUrlRouting(): void
	{
		// not cached, should fail (redirect to error)
		$response = $this->app->call('/@/page/my-page');
		$this->assertFalse($response);

		// cached, should redirect to page A
		$this->app->page('page-a')->uuid()->populate();
		$response = $this->app->call('/@/page/my-page')->send();
		$this->assertSame(302, $response->code());
		$this->assertSame('https://getkirby.com/page-a', $response->header('Location'));

		// check if ->url() populates cache
		$uuid = $this->app->page('page-a')->uuid();
		$uuid->clear();
		$response = $this->app->call('/@/page/my-page');
		$this->assertFalse($response);

		$permalink = new Permalink($uuid);
		$permalink->url();
		$response = $this->app->call('/@/page/my-page')->send();
		$this->assertSame(302, $response->code());
		$this->assertSame('https://getkirby.com/page-a', $response->header('Location'));
	}
}
