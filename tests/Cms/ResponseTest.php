<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass \Kirby\Cms\Response
 */
class ResponseTest extends TestCase
{
	public function setUp(): void
	{
		$this->kirby([
			'urls' => [
				'index' => 'https://getkirby.test'
			]
		]);
	}

	/**
	 * @covers ::redirect
	 */
	public function testRedirect()
	{
		$response = Response::redirect();
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.test'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	/**
	 * @covers ::redirect
	 */
	public function testRedirectWithLocation()
	{
		$response = Response::redirect('https://getkirby.com');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.com'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	/**
	 * @covers ::redirect
	 */
	public function testRedirectWithInternationalLocation()
	{
		$response = Response::redirect('https://täst.de');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://xn--tst-qla.de'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	/**
	 * @covers ::redirect
	 */
	public function testRedirectWithResponseCodeAndUri()
	{
		$response = Response::redirect('/uri', 301);
		$this->assertSame('', $response->body());
		$this->assertSame(301, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.test/uri'], $response->headers()); // cannot use strict assertion (Uri object)
	}
}
