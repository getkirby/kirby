<?php

namespace Kirby\Cms;

use Kirby\Http\Response\Json;
use Whoops\Run as Whoops;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\CallbackHandler;

trait AppErrors
{

    protected function handleCliErrors()
    {
        $whoops = new Whoops;
        $whoops->pushHandler(new PlainTextHandler);
        $whoops->register();
    }

    protected function handleErrors()
    {
        // TODO: implement acceptance
        if ($this->request()->ajax()) {
            return $this->handleJsonErrors();
        }

        if ($this->request()->cli()) {
            return $this->handleCliErrors();
        }

        return $this->handleHtmlErrors();
    }

    protected function handleHtmlErrors()
    {
        $whoops = new Whoops;

        if($this->option('debug') === true) {
            $handler = new PrettyPageHandler;
            $handler->setPageTitle('Kirby CMS Debugger');
        } else {
            $handler = new CallbackHandler(function ($exception, $inspector, $run) {
                // TODO: implement fatal view
                die ("The site is currently offline");
                return Handler::QUIT;
            });
        }

        $whoops->pushHandler($handler);
        $whoops->register();
    }

    protected function handleJsonErrors()
    {
        $whoops  = new Whoops;
        $handler = new CallbackHandler(function ($exception, $inspector, $run) {

            if($this->option('debug') === true) {
                echo new Json([
                    'status'    => 'error',
                    'exception' => get_class($exception),
                    'code'      => $exception->getCode(),
                    'message'   => $exception->getMessage(),
                    'file'      => ltrim($exception->getFile(), $_SERVER['DOCUMENT_ROOT'] ?? null),
                    'line'      => $exception->getLine(),
                ], $exception->getCode() > 0 ? $exception->getCode() : 500);
            } else {
                echo new Json([
                    'status'  => 'error',
                    'code'    => 0,
                    'message' => 'An unexpected error occurred! Enable debug mode for more info: https://getkirby.com/docs/cheatsheet/options/debug',
                ], 500);
            }

            return Handler::QUIT;

        });

        $whoops->pushHandler($handler);
        $whoops->register();
    }

}
