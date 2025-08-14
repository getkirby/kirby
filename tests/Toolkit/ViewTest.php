<?php

namespace Kirby\Toolkit;

use Exception;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/view';

	protected function view(array $data = []): View
	{
		return new View(static::FIXTURES . '/view.php', $data);
	}

	public function testData(): void
	{
		$view = $this->view();
		$this->assertSame([], $view->data());

		$view = $this->view(['test']);
		$this->assertSame(['test'], $view->data());
	}

	public function testExists(): void
	{
		$view = $this->view();
		$this->assertTrue($view->exists());

		$view = new View(static::FIXTURES . '/foo.php');
		$this->assertFalse($view->exists());
	}

	public function testFile(): void
	{
		$view = $this->view();
		$this->assertSame(static::FIXTURES . '/view.php', $view->file());
	}

	public function testRender(): void
	{
		$view = $this->view(['name' => 'Homer']);
		$this->assertSame('Hello Homer', $view->render());
	}

	public function testRenderWithMissingFile(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The view does not exist: invalid-file.php');

		$view = new View('invalid-file.php');
		$view->render();
	}

	public function testRenderWithException(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('View exception');

		$view = new View(static::FIXTURES . '/view-with-exception.php');
		$view->render();
	}

	public function testToString(): void
	{
		$view = $this->view(['name' => 'Tester']);
		$this->assertSame('Hello Tester', $view->toString());
		$this->assertSame('Hello Tester', $view->__toString());
		$this->assertSame('Hello Tester', (string)$view);
	}
}
