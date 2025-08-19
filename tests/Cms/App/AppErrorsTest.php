<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;
use Whoops\Handler\CallbackHandler;
use Whoops\Handler\PlainTextHandler;

#[CoversClass(AppErrors::class)]
class AppErrorsTest extends TestCase
{
	protected App|null $originalApp;

	public function setUp(): void
	{
		parent::setUp();

		ErrorLog::$log = '';

		// Whoops is normally enabled by default, but disabled in CI
		// to reduce memory leaks; in this test class we need it!
		$this->originalApp = $this->app;
		$this->app = $this->originalApp->clone([
			'options' => [
				'whoops' => true
			]
		]);
	}

	public function tearDown(): void
	{
		$unsetMethod = new ReflectionMethod(App::class, 'unsetWhoopsHandler');

		$app = App::instance();
		$unsetMethod->invoke($app);
		$unsetMethod->invoke($this->app);
		$unsetMethod->invoke($this->originalApp);

		parent::tearDown();
		$this->originalApp = null;

		// reset to the value set by tests/bootstrap.php
		App::$enableWhoops = false;
	}

	public function testExceptionHook(): void
	{
		$result = null;

		$app = $this->app->clone([
			'hooks' => [
				'system.exception' => function ($exception) use (&$result) {
					$result = $exception->getMessage();
				}
			]
		]);

		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');

		$whoops  = $whoopsMethod->invoke($app);
		$handler = $whoops->getHandlers()[1];

		// test CallbackHandler with \Exception class
		$exception = new \Exception('Some error message');
		$handler->setException($exception);

		// handle the exception
		$this->_getBufferedContent($handler);

		$this->assertSame('Some error message', $result);
		$this->assertStringContainsString('Exception: Some error message in', ErrorLog::$log);
	}

	public function testExceptionHookDisableLogging(): void
	{
		$result = null;

		$app = $this->app->clone([
			'hooks' => [
				'system.exception' => function ($exception) use (&$result) {
					$result = $exception->getMessage();
					return false;
				}
			]
		]);

		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');

		$whoops  = $whoopsMethod->invoke($app);
		$handler = $whoops->getHandlers()[1];

		// test CallbackHandler with \Exception class
		$exception = new \Exception('Some error message');
		$handler->setException($exception);

		// handle the exception
		$this->_getBufferedContent($handler);

		$this->assertSame('Some error message', $result);
		$this->assertSame('', ErrorLog::$log);
	}

	public function testHandleCliErrors(): void
	{
		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');
		$testMethod   = new ReflectionMethod(App::class, 'handleCliErrors');

		$app    = App::instance();
		$whoops = $whoopsMethod->invoke($app);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\PlainTextHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);
	}

	public function testHandleErrors1(): void
	{
		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');
		$testMethod   = new ReflectionMethod(App::class, 'handleErrors');

		$app = $this->app->clone([
			'cli' => true
		]);

		$whoops = $whoopsMethod->invoke($app);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\PlainTextHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);
	}

	public function testHandleErrors2(): void
	{
		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');
		$testMethod   = new ReflectionMethod(App::class, 'handleErrors');

		$app = $this->app->clone([
			'cli' => false,
			'server' => [
				'HTTP_ACCEPT' => 'application/json'
			]
		]);

		$whoops = $whoopsMethod->invoke($app);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);
	}

	public function testHandleErrors3(): void
	{
		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');
		$testMethod   = new ReflectionMethod(App::class, 'handleErrors');

		$app = $this->app->clone([
			'cli' => false,
			'server' => [
				'HTTP_ACCEPT' => 'text/html'
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'debug' => true
			]
		]);
		$whoops = $whoopsMethod->invoke($app);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\PrettyPageHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);
	}

	public function testHandleErrorsGlobalSetting(): void
	{
		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');
		$testMethod   = new ReflectionMethod(App::class, 'handleErrors');

		$whoopsEnabled  = $whoopsMethod->invoke($this->app);
		$whoopsDisabled = $whoopsMethod->invoke($this->originalApp);

		$testMethod->invoke($this->app);
		$handlers = $whoopsEnabled->getHandlers();
		$this->assertCount(2, $handlers);

		$testMethod->invoke($this->originalApp);
		$handlers = $whoopsDisabled->getHandlers();
		$this->assertCount(0, $handlers);

		App::$enableWhoops = true;

		$testMethod->invoke($this->app);
		$handlers = $whoopsEnabled->getHandlers();
		$this->assertCount(2, $handlers);

		$testMethod->invoke($this->originalApp);
		$handlers = $whoopsDisabled->getHandlers();
		$this->assertCount(2, $handlers);
	}

	public function testHandleHtmlErrors(): void
	{
		$whoopsMethod  = new ReflectionMethod(App::class, 'whoops');
		$optionsMethod = new ReflectionMethod(App::class, 'optionsFromProps');
		$testMethod    = new ReflectionMethod(App::class, 'handleHtmlErrors');

		$app    = App::instance();
		$whoops = $whoopsMethod->invoke($app);

		// without options
		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
		$this->assertSame($this->_getBufferedContent($app->root('kirby') . '/views/fatal.php'), $this->_getBufferedContent($handlers[0]));
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		// without fatal closure
		$optionsMethod->invoke($app, ['fatal' => fn () => 'Fatal Error Test!']);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
		$this->assertSame('Fatal Error Test!', $this->_getBufferedContent($handlers[0]));
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		// disabling Whoops without debugging doesn't matter
		$optionsMethod->invoke($app, ['debug' => false, 'whoops' => false]);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		// with debugging enabled
		$optionsMethod->invoke($app, ['debug' => true, 'whoops' => true]);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\PrettyPageHandler', $handlers[0]);
		$this->assertSame('Kirby CMS Debugger', $handlers[0]->getPageTitle());
		$this->assertSame(dirname(__DIR__, 3) . '/assets', $handlers[0]->getResourcePaths()[0]);
		$this->assertFalse($handlers[0]->getEditorHref('test', 1));
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		// with debugging enabled and editor
		$optionsMethod->invoke($app, ['debug' => true, 'whoops' => true, 'editor' => 'vscode']);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);

		$this->assertInstanceOf('Whoops\Handler\PrettyPageHandler', $handlers[0]);
		$this->assertSame('Kirby CMS Debugger', $handlers[0]->getPageTitle());
		$this->assertSame(dirname(__DIR__, 3) . '/assets', $handlers[0]->getResourcePaths()[0]);
		$this->assertSame('vscode://file/test:1', $handlers[0]->getEditorHref('test', 1));
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		// with debugging enabled, but without Whoops
		$optionsMethod->invoke($app, ['debug' => true, 'whoops' => false]);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(0, $handlers);
	}

	public function testHandleJsonErrors(): void
	{
		$whoopsMethod  = new ReflectionMethod(App::class, 'whoops');
		$optionsMethod = new ReflectionMethod(App::class, 'optionsFromProps');
		$testMethod    = new ReflectionMethod(App::class, 'handleJsonErrors');

		$app    = App::instance();
		$whoops = $whoopsMethod->invoke($app);

		$testMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		// test CallbackHandler with default
		$this->assertSame(json_encode([
			'status' => 'error',
			'code' => 500,
			'details' => null,
			'message' => 'An unexpected error occurred! Enable debug mode for more info: https://getkirby.com/docs/reference/system/options/debug'
		]), $this->_getBufferedContent($handlers[0]));

		// test CallbackHandler with \Exception class
		$exception = new \Exception('Some error message', 30);
		$handlers[0]->setException($exception);

		$this->assertSame(json_encode([
			'status' => 'error',
			'code' => 30,
			'details' => null,
			'message' => 'An unexpected error occurred! Enable debug mode for more info: https://getkirby.com/docs/reference/system/options/debug'
		]), $this->_getBufferedContent($handlers[0]));

		// test CallbackHandler with \Kirby\Exception\Exception class
		$exception = new Exception(
			data: [],
			details: ['Some error message']
		);
		$handlers[0]->setException($exception);

		$this->assertSame(json_encode([
			'status' => 'error',
			'code' => 'error.general',
			'details' => [
				'Some error message'
			],
			'message' => 'An unexpected error occurred! Enable debug mode for more info: https://getkirby.com/docs/reference/system/options/debug'
		]), $this->_getBufferedContent($handlers[0]));

		// with debugging enabled
		$optionsMethod->invoke($app, ['debug' => true, 'whoops' => true]);

		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);

		$this->assertSame(json_encode([
			'status' => 'error',
			'exception' => Exception::class,
			'code' => 'error.general',
			'message' => 'An error occurred',
			'details' => [
				'Some error message'
			],
			'file' => basename(__FILE__),
			'line' => $exception->getLine()
		]), $this->_getBufferedContent($handlers[0]));
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);
	}

	public function testSetUnsetWhoopsHandler(): void
	{
		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');
		$setMethod    = new ReflectionMethod(App::class, 'setWhoopsHandler');
		$unsetMethod  = new ReflectionMethod(App::class, 'unsetWhoopsHandler');

		$app    = App::instance();
		$whoops = $whoopsMethod->invoke($app);

		$setMethod->invoke($app, new PlainTextHandler());
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\PlaintextHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		$setMethod->invoke($app, function () {
			// empty callback
		});
		$handlers = $whoops->getHandlers();
		$this->assertCount(2, $handlers);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
		$this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

		$unsetMethod->invoke($app);
		$handlers = $whoops->getHandlers();
		$this->assertCount(0, $handlers);
	}

	public function testWhoops(): void
	{
		$whoopsMethod = new ReflectionMethod(App::class, 'whoops');

		$app = App::instance();

		$whoops1 = $whoopsMethod->invoke($app);
		$this->assertInstanceOf('Whoops\Run', $whoops1);

		$whoops2 = $whoopsMethod->invoke($app);
		$this->assertInstanceOf('Whoops\Run', $whoops2);
		$this->assertSame($whoops1, $whoops2);
	}

	/**
	 * Convert output to returned variable
	 */
	protected function _getBufferedContent(string|CallbackHandler $path): false|string
	{
		ob_start();

		if ($path instanceof CallbackHandler) {
			$path->handle();
		} else {
			F::load($path);
		}

		$response = ob_get_clean();

		return $response;
	}
}
