<?php

namespace Kirby\Panel\Response;

use Kirby\Cms\App;
use Kirby\FileSystem\Dir;
use Kirby\Http\Response;
use Kirby\Panel\Fiber;
use Kirby\Panel\Redirect;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Response\ViewResponse
 */
class ViewResponseTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Response.ViewResponse';

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

	/**
	 * @covers ::error
	 */
	public function testError()
	{
		$error = ViewResponse::error('Error message');

		$this->assertSame('k-error-view', $error->view()['component']);
		$this->assertSame('Error message', $error->view()['error']);
		$this->assertSame('Error', $error->view()['title']);
		$this->assertSame(404, $error->code());
	}

	/**
	 * @covers ::error
	 */
	public function testErrorWithCustomCode()
	{
		$error = ViewResponse::error('Error message', 403);
		$this->assertSame(403, $error->code());
	}

	/**
	 * @covers ::fiber
	 */
	public function testFiber()
	{
		$response = new ViewResponse();
		$this->assertInstanceOf(Fiber::class, $response->fiber());
	}

	/**
	 * @covers ::from
	 */
	public function testFrom()
	{
		$input    = new Redirect('https://getkirby.com');
		$response = ViewResponse::from($input);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame('https://getkirby.com', $response->header('Location'));
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$response = new ViewResponse();
		$this->assertSame('view', $response->key());
	}

	/**
	 * @covers ::view
	 */
	public function testView()
	{
		$response = new ViewResponse(
			view: $view = [
				'component' => 'k-pages-view',
				'props' => []
			]
		);

		$this->assertSame($view, $response->view());
	}
}
