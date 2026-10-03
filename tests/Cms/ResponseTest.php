<?php

namespace Kirby\Cms;

use Kirby\Http\Uri;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
	protected function setUp(): void
	{
		$this->kirby([
			'urls' => [
				'index' => 'https://getkirby.test'
			]
		]);
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		Uri::$current = null;
	}

	public function testRedirect(): void
	{
		$response = Response::redirect();
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.test'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithLocation(): void
	{
		$response = Response::redirect('https://getkirby.com');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.com'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithInternationalLocation(): void
	{
		$response = Response::redirect('https://täst.de');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://xn--tst-qla.de'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithResponseCodeAndUri(): void
	{
		$response = Response::redirect('/uri', 301);
		$this->assertSame('', $response->body());
		$this->assertSame(301, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.test/uri'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithInherit(): void
	{
		Uri::$current = new Uri('https://getkirby.test/notes/tag:foo?bar=baz');

		$response = Response::redirect('/de', inherit: true);
		$this->assertEquals(['Location' => 'https://getkirby.test/de/tag:foo?bar=baz'], $response->headers()); // cannot use strict assertion (Uri object)
	}
}
