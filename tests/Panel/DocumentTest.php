<?php

namespace Kirby\Panel;

use Kirby\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Document';

	public function testResponse(): void
	{
		// create panel dist files first to avoid redirect
		(new Assets())->link();

		// get panel response
		$state    = new State(['test' => 'Test']);
		$response = Document::response($state);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->code());
		$this->assertSame('text/html', $response->type());
		$this->assertSame('UTF-8', $response->charset());
		$this->assertSame("frame-ancestors 'none'", $response->header('Content-Security-Policy'));
		$this->assertNotNull($response->body());
	}

	public function testResponseFrameAncestorsSelf(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => true
				]
			]
		]);

		$assets = new Assets();

		// create panel dist files first to avoid redirect
		$assets->link();

		// get panel response
		$state    = new State(['test' => 'Test']);
		$response = Document::response($state);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->code());
		$this->assertSame('text/html', $response->type());
		$this->assertSame('UTF-8', $response->charset());
		$this->assertSame("frame-ancestors 'self'", $response->header('Content-Security-Policy'));
		$this->assertNotNull($response->body());
	}

	public function testResponseFrameAncestorsArray(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => ['*.example.com', 'https://example.com']
				]
			]
		]);

		// create panel dist files first to avoid redirect
		$assets = new Assets();
		$assets->link();

		// get panel response
		$state    = new State(['test' => 'Test']);
		$response = Document::response($state);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->code());
		$this->assertSame('text/html', $response->type());
		$this->assertSame('UTF-8', $response->charset());
		$this->assertSame(
			"frame-ancestors 'self' *.example.com https://example.com",
			$response->header('Content-Security-Policy')
		);
		$this->assertNotNull($response->body());
	}

	public function testResponseFrameAncestorsString(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => '*.example.com https://example.com'
				]
			]
		]);

		// create panel dist files first to avoid redirect
		$assets = new Assets();
		$assets->link();

		// get panel response
		$state    = new State(['test' => 'Test']);
		$response = Document::response($state);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->code());
		$this->assertSame('text/html', $response->type());
		$this->assertSame('UTF-8', $response->charset());
		$this->assertSame(
			'frame-ancestors *.example.com https://example.com',
			$response->header('Content-Security-Policy')
		);
		$this->assertNotNull($response->body());
	}
}
