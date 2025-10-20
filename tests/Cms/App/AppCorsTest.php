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

	public function testIsCorsEnabledWhenConfigured(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$this->assertTrue($app->isCorsEnabled());
	}

	public function testPreflightRouteWithCompleteConfiguration(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => [
					'enabled'          => true,
					'allowOrigin'      => 'https://example.com',
					'allowMethods'     => 'GET, POST, PUT',
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
					'enabled'     => true,
					'allowOrigin' => 'https://example.com'
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://evil.com'
			]
		]);

		$response = $app->call('api/test', 'OPTIONS');

		// origin doesn't match, route returns null
		$this->assertNull($response);
	}

	public function testPreflightRouteWithWildcardOrigin(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://anywhere.com'
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
			]
		]);

		$response = $app->call('any/route', 'OPTIONS');

		// cors disabled, route returns null
		$this->assertNull($response);
	}
}
