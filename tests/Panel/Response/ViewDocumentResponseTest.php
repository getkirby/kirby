<?php

namespace Kirby\Panel\Response;

use Kirby\Cms\App;
use Kirby\FileSystem\Dir;
use Kirby\Panel\State;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewDocumentResponse::class)]
class ViewDocumentResponseTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Response.ViewDocumentResponse';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);
	}

	public function testCors(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertSame('frame-ancestors \'none\'', $response->cors());

		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => true
				]
			]
		]);

		$response = new ViewDocumentResponse();
		$this->assertSame('frame-ancestors \'self\'', $response->cors());

		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => ['*.example.com', 'https://example.com']
				]
			]
		]);

		$response = new ViewDocumentResponse();
		$this->assertSame('frame-ancestors \'self\' *.example.com https://example.com', $response->cors());

		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => '*.example.com https://example.com'
				]
			]
		]);

		$response = new ViewDocumentResponse();
		$this->assertSame('frame-ancestors *.example.com https://example.com', $response->cors());
	}

	public function testBody(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertNotNull($response->body());
	}

	public function testData(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertInstanceOf(State::class, $response->state());
	}

	public function testHeaders(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertArrayHasKey('Content-Security-Policy', $response->headers());
		$this->assertSame($response->cors(), $response->headers()['Content-Security-Policy']);
	}

	public function testType(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertSame('text/html', $response->type());
	}

	public function testUrl(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertSame('/panel/', $response->url());
	}
}
