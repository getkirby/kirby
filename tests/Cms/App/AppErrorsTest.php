<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Filesystem\F;
use Kirby\Http\Server;
use ReflectionMethod;
use Whoops\Handler\PlainTextHandler;

/**
 * @coversDefaultClass \Kirby\Cms\AppErrors
 */
class AppErrorsTest extends TestCase
{
    public function tearDown(): void
    {
        $unsetMethod = new ReflectionMethod(App::class, 'unsetWhoopsHandler');
        $unsetMethod->setAccessible(true);

        $app = App::instance();
        $unsetMethod->invoke($app);

        parent::tearDown();
    }

    /**
     * @covers ::getExceptionHookWhoopsHandler
     */
    public function testExceptionHook()
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
        $whoopsMethod->setAccessible(true);

        $whoops  = $whoopsMethod->invoke($app);
        $handler = $whoops->getHandlers()[1];

        // test CallbackHandler with \Exception class
        $exception = new \Exception('Some error message');
        $handler->setException($exception);

        // handle the exception
        $this->_getBufferedContent($handler);

        $this->assertSame('Some error message', $result);
    }

    /**
     * @covers ::handleCliErrors
     * @covers ::getExceptionHookWhoopsHandler
     */
    public function testHandleCliErrors()
    {
        $whoopsMethod = new ReflectionMethod(App::class, 'whoops');
        $whoopsMethod->setAccessible(true);

        $testMethod = new ReflectionMethod(App::class, 'handleCliErrors');
        $testMethod->setAccessible(true);

        $app    = App::instance();
        $whoops = $whoopsMethod->invoke($app);

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PlainTextHandler', $handlers[0]);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);
    }

    /**
     * @covers ::handleErrors
     * @covers ::getExceptionHookWhoopsHandler
     */
    public function testHandleErrors()
    {
        $whoopsMethod = new ReflectionMethod(App::class, 'whoops');
        $whoopsMethod->setAccessible(true);

        $testMethod = new ReflectionMethod(App::class, 'handleErrors');
        $testMethod->setAccessible(true);

        $app    = App::instance();
        $whoops = $whoopsMethod->invoke($app);

        $oldCli    = Server::$cli;
        $oldAccept = $_SERVER['HTTP_ACCEPT'] ?? null;

        // CLI
        Server::$cli = true;

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PlainTextHandler', $handlers[0]);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

        // JSON
        Server::$cli = false;
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);

        // HTML
        Server::$cli = false;
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        $app = new App([
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

        // reset global state
        Server::$cli            = $oldCli;
        $_SERVER['HTTP_ACCEPT'] = $oldAccept;
    }

    /**
     * @covers ::handleHtmlErrors
     * @covers ::getExceptionHookWhoopsHandler
     */
    public function testHandleHtmlErrors()
    {
        $whoopsMethod = new ReflectionMethod(App::class, 'whoops');
        $whoopsMethod->setAccessible(true);

        $optionsMethod = new ReflectionMethod(App::class, 'optionsFromProps');
        $optionsMethod->setAccessible(true);

        $testMethod = new ReflectionMethod(App::class, 'handleHtmlErrors');
        $testMethod->setAccessible(true);

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
        $optionsMethod->invoke($app, ['fatal' => function () {
            return 'Fatal Error Test!';
        }]);

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

    /**
     * @covers ::handleJsonErrors
     * @covers ::getExceptionHookWhoopsHandler
     */
    public function testHandleJsonErrors()
    {
        $whoopsMethod = new ReflectionMethod(App::class, 'whoops');
        $whoopsMethod->setAccessible(true);

        $optionsMethod = new ReflectionMethod(App::class, 'optionsFromProps');
        $optionsMethod->setAccessible(true);

        $testMethod = new ReflectionMethod(App::class, 'handleJsonErrors');
        $testMethod->setAccessible(true);

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
        $exception = new Exception([
            'data' => [],
            'details'  => [
                'Some error message'
            ]
        ]);
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
            'exception' => 'Kirby\Exception\Exception',
            'code' => 'error.general',
            'message' => 'An error occurred',
            'details' => [
                'Some error message'
            ],
            'file' => __FILE__,
            'line' => $exception->getLine()
        ]), $this->_getBufferedContent($handlers[0]));
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[1]);
    }

    /**
     * @covers ::setWhoopsHandler
     * @covers ::unsetWhoopsHandler
     * @covers ::getExceptionHookWhoopsHandler
     */
    public function testSetUnsetWhoopsHandler()
    {
        $whoopsMethod = new ReflectionMethod(App::class, 'whoops');
        $whoopsMethod->setAccessible(true);

        $setMethod = new ReflectionMethod(App::class, 'setWhoopsHandler');
        $setMethod->setAccessible(true);

        $unsetMethod = new ReflectionMethod(App::class, 'unsetWhoopsHandler');
        $unsetMethod->setAccessible(true);

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

    /**
     * @covers ::whoops
     */
    public function testWhoops()
    {
        $whoopsMethod = new ReflectionMethod(App::class, 'whoops');
        $whoopsMethod->setAccessible(true);

        $app = App::instance();

        $whoops1 = $whoopsMethod->invoke($app);
        $this->assertInstanceOf('Whoops\Run', $whoops1);

        $whoops2 = $whoopsMethod->invoke($app);
        $this->assertInstanceOf('Whoops\Run', $whoops2);
        $this->assertSame($whoops1, $whoops2);
    }

    /**
     * Convert output to returned variable
     *
     * @param string|\Whoops\Handler\CallbackHandler $path
     * @return false|string
     */
    protected function _getBufferedContent($path)
    {
        ob_start();

        if (is_a($path, '\Whoops\Handler\CallbackHandler') === true) {
            $path->handle();
        } else {
            F::load($path);
        }

        $response = ob_get_clean();

        return $response;
    }
}
