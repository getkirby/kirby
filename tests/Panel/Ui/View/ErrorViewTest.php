<?php

namespace Kirby\Panel\Ui\View;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ErrorView::class)]
class ErrorViewTest extends TestCase
{
	public function testProps(): void
	{
		$view = new ErrorView(
			message: 'My Message',
			access: true
		);

		$this->assertSame([
			'class'   => null,
			'style'   => null,
			'buttons' => [],
			'title'   => null,
			'error'   => 'My Message',
			'layout'  => 'inside'
		], $view->props());
	}

	public function testRender(): void
	{
		$view = new ErrorView(
			message: 'My Message',
			access: true
		);

		$result = $view->render();
		$this->assertSame('k-error-view', $result['component']);
	}
}
