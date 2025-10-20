<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Responder::class)]
class ResponderCorsTest extends TestCase
{
	public function setUp(): void
	{
		$this->kirby([
			'urls' => [
				'index' => 'https://getkirby.test'
			]
		]);
	}

	public function tearDown(): void
	{
		unset($_COOKIE['foo']);
	}

	public function testDisabled(): void
	{
		$this->assertSame([], Responder::corsHeaders());
	}

	public function testWildcardOrigin(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('*', $headers['Access-Control-Allow-Origin']);
		$this->assertArrayNotHasKey('Vary', $headers);
		$this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
		$this->assertArrayNotHasKey('Access-Control-Expose-Headers', $headers);
	}

	public function testWildcardOriginWithCredentials(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot use wildcard origin (*) with credentials');

		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'          => true,
					'allowOrigin'      => '*',
					'allowCredentials' => true
				]
			]
		]);

		Responder::corsHeaders();
	}

	public function testSingleOriginMatch(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => 'https://example.com'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('Origin', $headers['Vary']);
	}

	public function testSingleOriginNoMatch(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => 'https://example.com'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://evil.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame([], $headers);
	}

	public function testMultipleOriginsMatch(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com', 'https://app2.com', 'https://staging.com']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://app2.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://app2.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('Origin', $headers['Vary']);
	}

	public function testMultipleOriginsNoMatch(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com', 'https://app2.com']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://evil.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame([], $headers);
	}

	public function testMultipleOriginsCaseInsensitive(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com', 'https://App2.COM']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://app2.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://app2.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('Origin', $headers['Vary']);
	}

	public function testOriginMatchingIgnoresReferer(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com']
				]
			],
			'server' => [
				'HTTP_REFERER' => 'https://app1.com/'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame([], $headers);
	}

	public function testOriginMatchingRejectsEmptyOrigin(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => ''
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame([], $headers);
	}

	public function testOriginMatchingRequiresOriginHeader(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com']
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame([], $headers);
	}

	public function testPreflightDefaultAllowMethods(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertSame('GET, HEAD, PUT, POST, DELETE, PATCH', $headers['Access-Control-Allow-Methods']);
	}

	public function testPreflightCustomAllowMethods(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'      => true,
					'allowOrigin'  => 'https://example.com',
					'allowMethods' => 'GET, POST'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertSame('GET, POST', $headers['Access-Control-Allow-Methods']);
	}

	public function testPreflightReflectsRequestHeaders(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			],
			'server' => [
				'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Accept, Content-Type, Authorization'
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertSame('Accept, Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('Access-Control-Request-Headers', $headers['Vary']);
	}

	public function testPreflightCustomAllowHeaders(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'      => true,
					'allowOrigin'  => 'https://example.com',
					'allowHeaders' => 'Content-Type, Authorization'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertSame('Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('Origin, Access-Control-Request-Headers', $headers['Vary']);
	}

	public function testPreflightMaxAgeNotSetByDefault(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertArrayNotHasKey('Access-Control-Max-Age', $headers);
	}

	public function testPreflightCustomMaxAge(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => 'https://example.com',
					'maxAge'      => 3600
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertSame('3600', $headers['Access-Control-Max-Age']);
	}

	public function testPreflightNotIncludedInNormalRequests(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'      => true,
					'allowMethods' => 'GET, POST',
					'allowHeaders' => 'Content-Type'
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertArrayNotHasKey('Access-Control-Allow-Methods', $headers);
		$this->assertArrayNotHasKey('Access-Control-Allow-Headers', $headers);
		$this->assertArrayNotHasKey('Access-Control-Max-Age', $headers);
	}

	public function testCredentials(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'          => true,
					'allowOrigin'      => 'https://example.com',
					'allowCredentials' => true
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('true', $headers['Access-Control-Allow-Credentials']);
	}

	public function testCredentialsWithMultipleOrigins(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'          => true,
					'allowOrigin'      => ['https://app1.com', 'https://app2.com'],
					'allowCredentials' => true
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://app1.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://app1.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('true', $headers['Access-Control-Allow-Credentials']);
		$this->assertSame('Origin', $headers['Vary']);
	}

	public function testExposeHeaders(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'       => true,
					'allowOrigin'   => 'https://example.com',
					'exposeHeaders' => 'X-Custom-Header'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('X-Custom-Header', $headers['Access-Control-Expose-Headers']);
	}

	public function testArrayFormats(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'       => true,
					'allowOrigin'   => 'https://example.com',
					'allowMethods'  => ['GET', 'POST', 'PUT', 'DELETE'],
					'allowHeaders'  => ['Content-Type', 'Authorization', 'X-Custom-Header'],
					'exposeHeaders' => ['X-Total-Count', 'X-Page-Number']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertSame('GET, POST, PUT, DELETE', $headers['Access-Control-Allow-Methods']);
		$this->assertSame('Content-Type, Authorization, X-Custom-Header', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('X-Total-Count, X-Page-Number', $headers['Access-Control-Expose-Headers']);
	}

	public function testEmptyValues(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'          => true,
					'allowOrigin'      => 'https://example.com',
					'allowCredentials' => false,
					'exposeHeaders'    => ''
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
		$this->assertArrayNotHasKey('Access-Control-Expose-Headers', $headers);
	}

	public function testCompleteConfiguration(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'          => true,
					'allowOrigin'      => 'https://example.com',
					'allowMethods'     => 'GET, POST, PUT, DELETE',
					'allowHeaders'     => 'Content-Type, Authorization',
					'maxAge'           => 3600,
					'allowCredentials' => true,
					'exposeHeaders'    => 'X-Custom-Header'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$headers = Responder::corsHeaders(preflight: true);

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, POST, PUT, DELETE', $headers['Access-Control-Allow-Methods']);
		$this->assertSame('Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('3600', $headers['Access-Control-Max-Age']);
		$this->assertSame('true', $headers['Access-Control-Allow-Credentials']);
		$this->assertSame('X-Custom-Header', $headers['Access-Control-Expose-Headers']);
	}

	public function testHeadersWithInjection(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$responder = new Responder();
		$headers = $responder->headers();

		$this->assertSame('*', $headers['Access-Control-Allow-Origin']);
	}

	public function testHeadersWithCustomHeadersNotOverridden(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => 'https://example.com'
				]
			]
		]);

		$responder = new Responder();
		$responder->header('Access-Control-Allow-Origin', 'https://custom.com');
		$headers = $responder->headers();

		$this->assertSame('https://custom.com', $headers['Access-Control-Allow-Origin']);
	}

	public function testHeadersWithCacheBehavior(): void
	{
		$_COOKIE['foo'] = 'bar';

		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => 'https://example.com'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$responder = new Responder();
		$responder->usesCookie('foo');
		$headers = $responder->headers();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('no-store, private', $headers['Cache-Control']);
	}

	public function testHeadersWithVaryMerging(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com', 'https://app2.com']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://app1.com'
			]
		]);

		$responder = new Responder();
		$responder->usesAuth(true);
		$responder->usesCookies(['session']);
		$headers = $responder->headers();

		$this->assertSame('Authorization, Cookie, Origin', $headers['Vary']);
		$this->assertSame('https://app1.com', $headers['Access-Control-Allow-Origin']);
	}

	public function testHeadersWithExistingVaryNotOverridden(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'enabled'     => true,
					'allowOrigin' => ['https://app1.com']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://app1.com'
			]
		]);

		$responder = new Responder();
		$responder->header('Vary', 'Accept-Encoding');
		$headers = $responder->headers();

		$this->assertSame('Accept-Encoding', $headers['Vary']);
		$this->assertSame('https://app1.com', $headers['Access-Control-Allow-Origin']);
	}
}
