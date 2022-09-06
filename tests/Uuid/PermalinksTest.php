<?php

namespace Kirby\Uuid;

class PermalinksTest extends TestCase
{
	public function testRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id']
					]
				]
			]
		]);

		// not cached, should fail (redirect to error)
		$response = $app->router()->call('/@/page/my-id');
		$this->assertSame(false, $response);

		// cached, should redirect to page A
		$app->page('a')->uuid()->populate();
		$response = $app->router()->call('/@/page/my-id')->send();
		$this->assertSame(302, $response->code());
		$this->assertSame('https://getkirby.com/a', $response->header('Location'));
	}
}
