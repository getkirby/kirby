<?php

namespace Kirby\Panel\Response;

use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\State;
use Kirby\Panel\TestCase;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewDocumentResponse::class)]
class ViewDocumentResponseTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Response.ViewDocumentResponse';

	public function testBody(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertNotNull($response->body());
	}

	public function testBodyEscapesTagsInState(): void
	{
		// `<!--<script>` would keep the inline script open
		// until the end of the document
		$response = new ViewDocumentResponse(
			view: ['title' => '<!--<script></script>']
		);

		$state = Str::after($response->body(), 'window.panelState = ');
		$state = rtrim(trim(Str::before($state, '</script>')), ';');

		$this->assertStringNotContainsString('<', $state);
		$this->assertSame(
			'<!--<script></script>',
			json_decode($state, true)['view']['title']
		);
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

	public function testData(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertInstanceOf(State::class, $response->state());
	}

	public function testFromKirbyException(): void
	{
		$response = ViewDocumentResponse::from(new PermissionException('test'));

		$this->assertSame(403, $response->code());
		$this->assertSame('test', $response->state()->view()['error']);
	}

	public function testFromNotFoundException(): void
	{
		$response = ViewDocumentResponse::from(new NotFoundException('test'));

		$this->assertSame(404, $response->code());
		$this->assertSame('test', $response->state()->view()['error']);
	}

	public function testHeaders(): void
	{
		$response = new ViewDocumentResponse();
		$this->assertArrayHasKey('Content-Security-Policy', $response->headers());
		$this->assertSame($response->cors(), $response->headers()['Content-Security-Policy']);
		$this->assertSame('noindex, nofollow', $response->headers()['X-Robots-Tag']);
		$this->assertSame('no-store, private', $response->headers()['Cache-Control']);
	}

	public function testHeadersWithFallbacks(): void
	{
		$response = new ViewDocumentResponse();
		$response->setHeaderFallbacks([
			'Cache-Control' => 'public',
			'X-Test'        => 'test'
		]);

		$this->assertSame('test', $response->headers()['X-Test']);
		$this->assertSame('no-store, private', $response->headers()['Cache-Control']);
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
