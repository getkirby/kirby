<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(App::class)]
class AppCorsTest extends TestCase
{
	public function testCorsDefault(): void
	{
		$this->assertFalse($this->app->cors());
	}

	public function testCorsWhenEnabled(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$this->assertTrue($app->cors());
	}

	public function testCorsHeadersWhenDisabled(): void
	{
		$this->assertSame([], Responder::corsHeaders());
	}

	public function testCorsHeadersBasic(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$headers = Responder::corsHeaders();

		// Check defaults are applied
		$this->assertSame('*', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, POST, PUT, PATCH, DELETE, OPTIONS', $headers['Access-Control-Allow-Methods']);
		$this->assertSame('Accept, Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('86400', $headers['Access-Control-Max-Age']);
		$this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
	}

	public function testCorsHeadersComplete(): void
	{
		$this->app->clone([
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
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, POST, PUT, DELETE', $headers['Access-Control-Allow-Methods']);
		$this->assertSame('Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('3600', $headers['Access-Control-Max-Age']);
		$this->assertSame('true', $headers['Access-Control-Allow-Credentials']);
		$this->assertSame('X-Custom-Header', $headers['Access-Control-Expose-Headers']);
	}

	public function testCorsHeadersPartial(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled'      => true,
					'allowOrigin'  => 'https://example.com',
					'allowMethods' => 'GET, POST'
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, POST', $headers['Access-Control-Allow-Methods']);
		// These should still use defaults
		$this->assertSame('Accept, Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('86400', $headers['Access-Control-Max-Age']);
		$this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
	}

	public function testCorsHeadersWithCredentialsFalse(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled'          => true,
					'allowOrigin'      => 'https://example.com',
					'allowCredentials' => false
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
	}

	public function testCorsHeadersEmptyExposeHeaders(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled'       => true,
					'allowOrigin'   => 'https://example.com',
					'exposeHeaders' => ''
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertArrayNotHasKey('Access-Control-Expose-Headers', $headers);
	}

	public function testCorsHeadersAllowMethodsArray(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled'      => true,
					'allowOrigin'  => 'https://example.com',
					'allowMethods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, POST, PUT, PATCH, DELETE', $headers['Access-Control-Allow-Methods']);
	}

	public function testCorsHeadersExposeHeadersArray(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled'       => true,
					'allowOrigin'   => 'https://example.com',
					'exposeHeaders' => ['X-Custom-Header', 'X-Total-Count', 'X-Page-Number']
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('X-Custom-Header, X-Total-Count, X-Page-Number', $headers['Access-Control-Expose-Headers']);
	}

	public function testCorsHeadersWithMixedArraysAndStrings(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled'       => true,
					'allowOrigin'   => 'https://example.com',
					'allowMethods'  => ['GET', 'POST'],
					'allowHeaders'  => 'Content-Type, Authorization',
					'exposeHeaders' => ['X-Custom-Header']
				]
			]
		]);

		$headers = Responder::corsHeaders();

		$this->assertSame('https://example.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET, POST', $headers['Access-Control-Allow-Methods']);
		$this->assertSame('Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('X-Custom-Header', $headers['Access-Control-Expose-Headers']);
	}

	public function testCorsHeadersDefaultsCanBeOverridden(): void
	{
		$this->app->clone([
			'options' => [
				'cors' => [
					'enabled'      => true,
					'allowOrigin'  => 'https://custom.com',
					'allowMethods' => 'GET',
					'allowHeaders' => 'X-Custom-Header',
					'maxAge'       => 3600
				]
			]
		]);

		$headers = Responder::corsHeaders();

		// All defaults should be overridden
		$this->assertSame('https://custom.com', $headers['Access-Control-Allow-Origin']);
		$this->assertSame('GET', $headers['Access-Control-Allow-Methods']);
		$this->assertSame('X-Custom-Header', $headers['Access-Control-Allow-Headers']);
		$this->assertSame('3600', $headers['Access-Control-Max-Age']);
	}
}
