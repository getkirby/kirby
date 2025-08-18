<?php

namespace Kirby\Uuid;

class PermalinksTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Uuid.Permalinks';

	public function testRouting(): void
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
