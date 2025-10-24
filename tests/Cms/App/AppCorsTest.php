<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(App::class)]
class AppCorsTest extends TestCase
{
	public function testIsCorsEnabledByDefault(): void
	{
		$this->assertFalse($this->app->isCorsEnabled());
	}

	public function testPreflightRouteWithCompleteConfiguration(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => [
					'allowOrigin'      => 'https://example.com',
					'allowMethods'     => 'GET, POST, PUT',
					'allowHeaders'     => 'Content-Type, Authorization',
					'maxAge'           => 3600,
					'allowCredentials' => true,
					'exposeHeaders'    => 'X-Custom-Header'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com',
				'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST'
			]
		]);

		$response = $app->call('api/test', 'OPTIONS');

		$this->assertSame(204, $response->code());
		$this->assertSame('', $response->body());

		$headers = $response->headers();
		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, POST, PUT', $headers['Access-Control-Allow-Methods']);
		$this->assertSame('Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('3600', $headers['Access-Control-Max-Age']);
		$this->assertSame('true', $headers['Access-Control-Allow-Credentials']);
		$this->assertSame('X-Custom-Header', $headers['Access-Control-Expose-Headers']);
		$this->assertSame('Origin, Access-Control-Request-Headers', $headers['Vary']);
	}

	public function testPreflightRouteWithDisallowedOrigin(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => [
					'allowOrigin' => 'https://example.com'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://evil.com',
				'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST'
			]
		]);

		$response = $app->call('api/test', 'OPTIONS');

		// origin doesn't match, preflight request is blocked
		$this->assertNull($response);
	}

	public function testPreflightRouteWithWildcardOrigin(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => true
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://anywhere.com',
				'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST'
			]
		]);

		$response = $app->call('api/test', 'OPTIONS');

		$this->assertSame(204, $response->code());
		$this->assertSame('', $response->body());

		$headers = $response->headers();
		$this->assertSame('*', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, HEAD, PUT, POST, DELETE, PATCH', $headers['Access-Control-Allow-Methods']);
	}

	public function testPreflightRouteWhenDisabled(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => false
			],
			'server' => [
				'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST'
			]
		]);

		$response = $app->call('any/route', 'OPTIONS');

		// cors disabled, route calls next() and falls through
		$this->assertNull($response);
	}

	public function testNonPreflightOptionsRequest(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => true
			],
			'server' => [
				// no Access-Control-Request-Method header
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$response = $app->call('any/route', 'OPTIONS');

		// falls through to other routes
		$this->assertNull($response);
	}

	public function testDynamicConfigWithClosure(): void
	{
		$this->kirby([
			'options' => [
				'cors' => function ($kirby) {
					$origin = $kirby->request()->header('Origin');

					if (in_array($origin, ['https://app1.com', 'https://app2.com'])) {
						return [
							'allowOrigin' => $origin,
							'allowCredentials' => true,
							'allowMethods' => ['GET', 'POST'],
						];
					}

					return ['allowOrigin' => '*'];
				}
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://app1.com'
			]
		]);

		$headers = Cors::headers(preflight: true);

		$this->assertSame('https://app1.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('true', $headers['Access-Control-Allow-Credentials']);
		$this->assertSame('GET, POST', $headers['Access-Control-Allow-Methods']);
	}

	public function testDynamicConfigWithClosureFallback(): void
	{
		$this->kirby([
			'options' => [
				'cors' => function ($kirby) {
					$origin = $kirby->request()->header('Origin');

					if (in_array($origin, ['https://app1.com', 'https://app2.com'])) {
						return [
							'allowOrigin' => $origin,
							'allowCredentials' => true,
						];
					}

					return ['allowOrigin' => '*'];
				}
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://unknown.com'
			]
		]);

		$headers = Cors::headers();

		$this->assertSame('*', $headers['Access-Control-Allow-Origin']);
		$this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
	}
}
