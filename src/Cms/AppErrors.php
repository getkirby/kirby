<?php

namespace Kirby\Cms;

use Kirby\Http\Response;
use Whoops\Handler\CallbackHandler;
use Whoops\Handler\Handler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

/**
 * AppErrors
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait AppErrors
{
    protected function handleCliErrors(): void
    {
        $whoops = new Whoops();
        $whoops->pushHandler(new PlainTextHandler());
        $whoops->register();
    }

    protected function handleErrors()
    {
        $request = $this->request();

        // TODO: implement acceptance
        if ($request->ajax()) {
            return $this->handleJsonErrors();
        }

        if ($request->cli()) {
            return $this->handleCliErrors();
        }

        return $this->handleHtmlErrors();
    }

    protected function handleHtmlErrors()
    {
        $whoops = new Whoops();

        if ($this->option('debug') === true) {
            if ($this->option('whoops', true) === true) {
                $handler = new PrettyPageHandler();
                $handler->setPageTitle('Kirby CMS Debugger');

                if ($editor = $this->option('editor')) {
                    $handler->setEditor($editor);
                }

                $whoops->pushHandler($handler);
                $whoops->register();
            }
        } else {
            $handler = new CallbackHandler(function ($exception, $inspector, $run) {
                $fatal = $this->option('fatal');

                if (is_a($fatal, 'Closure') === true) {
                    echo $fatal($this);
                } else {
                    include static::$root . '/views/fatal.php';
                }

                return Handler::QUIT;
            });

            $whoops->pushHandler($handler);
            $whoops->register();
        }
    }

    protected function handleJsonErrors()
    {
        $whoops  = new Whoops();
        $handler = new CallbackHandler(function ($exception, $inspector, $run) {
            if (is_a($exception, 'Kirby\Exception\Exception') === true) {
                $httpCode = $exception->getHttpCode();
                $code     = $exception->getCode();
                $details  = $exception->getDetails();
            } else {
                $httpCode = 500;
                $code     = $exception->getCode();
                $details  = null;
            }

            if ($this->option('debug') === true) {
                echo Response::json([
                    'status'    => 'error',
                    'exception' => get_class($exception),
                    'code'      => $code,
                    'message'   => $exception->getMessage(),
                    'details'   => $details,
                    'file'      => ltrim($exception->getFile(), $_SERVER['DOCUMENT_ROOT'] ?? null),
                    'line'      => $exception->getLine(),
                ], $httpCode);
            } else {
                echo Response::json([
                    'status'  => 'error',
                    'code'    => $code,
                    'details' => $details,
                    'message' => 'An unexpected error occurred! Enable debug mode for more info: https://getkirby.com/docs/reference/system/options/debug',
                ], $httpCode);
            }

            return Handler::QUIT;
        });

        $whoops->pushHandler($handler);
        $whoops->register();
    }
}
