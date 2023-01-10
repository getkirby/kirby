<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\Exception;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Toolkit\I18n;
use Throwable;
use Whoops\Handler\CallbackHandler;
use Whoops\Handler\Handler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

/**
 * PHP error handling using the Whoops library
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait AppErrors
{
	/**
	 * Whoops instance cache
	 *
	 * @var \Whoops\Run
	 */
	protected $whoops;

	/**
	 * Registers the PHP error handler for CLI usage
	 *
	 * @return void
	 */
	protected function handleCliErrors(): void
	{
		$this->setWhoopsHandler(new PlainTextHandler());
	}

	/**
	 * Registers the PHP error handler
	 * based on the environment
	 *
	 * @return void
	 */
	protected function handleErrors(): void
	{
		if ($this->environment()->cli() === true) {
			$this->handleCliErrors();
			return;
		}

		if ($this->visitor()->prefersJson() === true) {
			$this->handleJsonErrors();
			return;
		}

		$this->handleHtmlErrors();
	}

	/**
	 * Registers the PHP error handler for HTML output
	 *
	 * @return void
	 */
	protected function handleHtmlErrors(): void
	{
		$handler = null;

		if ($this->option('debug') === true) {
			if ($this->option('whoops', true) === true) {
				$handler = new PrettyPageHandler();
				$handler->setPageTitle('Kirby CMS Debugger');
				$handler->setResourcesPath(dirname(__DIR__, 2) . '/assets');
				$handler->addCustomCss('whoops.css');

				if ($editor = $this->option('editor')) {
					$handler->setEditor($editor);
				}
			}
		} else {
			$handler = new CallbackHandler(function ($exception, $inspector, $run) {
				$fatal = $this->option('fatal');

				if ($fatal instanceof Closure) {
					echo $fatal($this, $exception);
				} else {
					include $this->root('kirby') . '/views/fatal.php';
				}

				return Handler::QUIT;
			});
		}

		if ($handler !== null) {
			$this->setWhoopsHandler($handler);
		} else {
			$this->unsetWhoopsHandler();
		}
	}

	/**
	 * Registers the PHP error handler for JSON output
	 *
	 * @return void
	 */
	protected function handleJsonErrors(): void
	{
		$handler = new CallbackHandler(function ($exception, $inspector, $run) {
			if ($exception instanceof Exception) {
				$httpCode = $exception->getHttpCode();
				$code     = $exception->getCode();
				$details  = $exception->getDetails();
			} elseif ($exception instanceof Throwable) {
				$httpCode = 500;
				$code     = $exception->getCode();
				$details  = null;
			} else {
				$httpCode = 500;
				$code     = 500;
				$details  = null;
			}

			if ($this->option('debug') === true) {
				echo Response::json([
					'status'    => 'error',
					'exception' => get_class($exception),
					'code'      => $code,
					'message'   => $exception->getMessage(),
					'details'   => $details,
					'file'      => F::relativepath($exception->getFile(), $this->environment()->get('DOCUMENT_ROOT', '')),
					'line'      => $exception->getLine(),
				], $httpCode);
			} else {
				echo Response::json([
					'status'  => 'error',
					'code'    => $code,
					'details' => $details,
					'message' => I18n::translate('error.unexpected'),
				], $httpCode);
			}

			return Handler::QUIT;
		});

		$this->setWhoopsHandler($handler);
		$this->whoops()->sendHttpCode(false);
	}

	/**
	 * Enables Whoops with the specified handler
	 *
	 * @param Callable|\Whoops\Handler\HandlerInterface $handler
	 * @return void
	 */
	protected function setWhoopsHandler($handler): void
	{
		$whoops = $this->whoops();
		$whoops->clearHandlers();
		$whoops->pushHandler($handler);
		$whoops->pushHandler($this->getAdditionalWhoopsHandler());
		$whoops->register(); // will only do something if not already registered
	}

	/**
	 * Whoops callback handler for additional error handling
	 * (`system.exception` hook and output to error log)
	 */
	protected function getAdditionalWhoopsHandler(): CallbackHandler
	{
		return new CallbackHandler(function ($exception, $inspector, $run) {
			$this->trigger('system.exception', compact('exception'));
			error_log($exception);
			return Handler::DONE;
		});
	}

	/**
	 * Clears the Whoops handlers and disables Whoops
	 *
	 * @return void
	 */
	protected function unsetWhoopsHandler(): void
	{
		$whoops = $this->whoops();
		$whoops->clearHandlers();
		$whoops->unregister(); // will only do something if currently registered
	}

	/**
	 * Returns the Whoops error handler instance
	 *
	 * @return \Whoops\Run
	 */
	protected function whoops()
	{
		if ($this->whoops !== null) {
			return $this->whoops;
		}

		return $this->whoops = new Whoops();
	}
}
