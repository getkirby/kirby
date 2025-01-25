<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Document
 * @covers ::__construct
 */
class DocumentTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Document';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);

		// create Panel dist files first to avoid redirect
		(new Assets())->link();
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

		// clear fake json requests
		$_GET = [];

		// clean up $_SERVER
		unset($_SERVER['SERVER_SOFTWARE']);
	}

	/**
	 * @covers ::assets
	 */
	public function testAssets(): void
	{
		$document = new Document();
		$this->assertInstanceOf(Assets::class, $document->assets());
	}

	/**
	 * @covers ::body
	 */
	public function testBody(): void
	{
		$document = new Document();
		$body     = $document->body(['foo' => 'bar']);
		$this->assertStringContainsString('window.fiber = {"foo":"bar"}', $body);
	}

	/**
	 * @covers ::cors
	 */
	public function testCorsSelf(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => true
				]
			]
		]);

		$document = new Document();
		$this->assertSame("frame-ancestors 'self'", $document->cors());
	}

	/**
	 * @covers ::cors
	 */
	public function testCorsArray(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => ['*.example.com', 'https://example.com']
				]
			]
		]);

		$document = new Document();
		$this->assertSame(
			"frame-ancestors 'self' *.example.com https://example.com",
			$document->cors()
		);
	}

	/**
	 * @covers ::cors
	 */
	public function testCorsString(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'frameAncestors' => '*.example.com https://example.com'
				]
			]
		]);

		$document = new Document();

		$this->assertSame(
			'frame-ancestors *.example.com https://example.com',
			$document->cors()
		);
	}

	/**
	 * @covers ::render
	 */
	public function testRender(): void
	{
		$document = new Document();

		// get panel response
		$response = $document->render([
			'test' => 'Test'
		]);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->code());
		$this->assertSame('text/html', $response->type());
		$this->assertSame('UTF-8', $response->charset());
		$this->assertSame("frame-ancestors 'none'", $response->header('Content-Security-Policy'));
		$this->assertNotNull($response->body());
	}

	/**
	 * @covers ::url
	 */
	public function testUrl(): void
	{
		$document = new Document();
		$this->assertSame('/panel/', $document->url());
	}
}
