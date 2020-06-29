<?php

namespace Kirby\Cms;

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
     * @covers ::handleCliErrors
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
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PlainTextHandler', $handlers[0]);
    }

    /**
     * @covers ::handleErrors
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
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PlainTextHandler', $handlers[0]);

        // JSON
        Server::$cli = false;
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
        
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
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PrettyPageHandler', $handlers[0]);

        // reset global state
        Server::$cli            = $oldCli;
        $_SERVER['HTTP_ACCEPT'] = $oldAccept;
    }

    /**
     * @covers ::handleHtmlErrors
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
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);

        // disabling Whoops without debugging doesn't matter
        $optionsMethod->invoke($app, ['debug' => false, 'whoops' => false]);

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);

        // with debugging enabled
        $optionsMethod->invoke($app, ['debug' => true, 'whoops' => true]);

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PrettyPageHandler', $handlers[0]);
        $this->assertSame('Kirby CMS Debugger', $handlers[0]->getPageTitle());
        $this->assertSame(dirname(__DIR__, 3) . '/assets', $handlers[0]->getResourcePaths()[0]);
        $this->assertFalse($handlers[0]->getEditorHref('test', 1));

        // with debugging enabled and editor
        $optionsMethod->invoke($app, ['debug' => true, 'whoops' => true, 'editor' => 'vscode']);

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PrettyPageHandler', $handlers[0]);
        $this->assertSame('Kirby CMS Debugger', $handlers[0]->getPageTitle());
        $this->assertSame(dirname(__DIR__, 3) . '/assets', $handlers[0]->getResourcePaths()[0]);
        $this->assertSame('vscode://file/test:1', $handlers[0]->getEditorHref('test', 1));

        // with debugging enabled, but without Whoops
        $optionsMethod->invoke($app, ['debug' => true, 'whoops' => false]);

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(0, $handlers);
    }

    /**
     * @covers ::handleJsonErrors
     */
    public function testHandleJsonErrors()
    {
        $whoopsMethod = new ReflectionMethod(App::class, 'whoops');
        $whoopsMethod->setAccessible(true);
        
        $testMethod = new ReflectionMethod(App::class, 'handleJsonErrors');
        $testMethod->setAccessible(true);

        $app    = App::instance();
        $whoops = $whoopsMethod->invoke($app);

        $testMethod->invoke($app);
        $handlers = $whoops->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);
    }

    /**
     * @covers ::setWhoopsHandler
     * @covers ::unsetWhoopsHandler
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
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\PlaintextHandler', $handlers[0]);

        $setMethod->invoke($app, function () {
            // empty callback
        });
        $handlers = $whoops->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf('Whoops\Handler\CallbackHandler', $handlers[0]);

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
}
