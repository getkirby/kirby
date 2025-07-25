<?php

namespace Kirby\Panel\Response;

use Kirby\Http\Response;
use Kirby\Panel\Redirect;
use Kirby\Panel\State;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewResponse::class)]
class ViewResponseTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Response.ViewResponse';

	public function testError(): void
	{
		$error = ViewResponse::error('Error message');

		$this->assertSame('k-error-view', $error->view()['component']);
		$this->assertSame('Error message', $error->view()['error']);
		$this->assertSame('Error', $error->view()['title']);
		$this->assertSame(404, $error->code());
	}

	public function testErrorWithCustomCode(): void
	{
		$error = ViewResponse::error('Error message', 403);
		$this->assertSame(403, $error->code());
	}

	public function testState(): void
	{
		$response = new ViewResponse();
		$this->assertInstanceOf(State::class, $response->state());
	}

	public function testFrom(): void
	{
		$input    = new Redirect('https://getkirby.com');
		$response = ViewResponse::from($input);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame('https://getkirby.com', $response->header('Location'));
	}

	public function testKey(): void
	{
		$response = new ViewResponse();
		$this->assertSame('view', $response->key());
	}

	public function testView(): void
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
