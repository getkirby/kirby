<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\I18n;
use Throwable;
use WeakReference;
use Whoops\Handler\CallbackHandler;
use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

/**
 * PHP error handling using the Whoops library
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait AppErrors
{
	/**
	 * Allows to disable Whoops globally in CI;
	 * can be overridden by explicitly setting
	 * the `whoops` option to `true` or `false`
	 */
	public static bool $enableWhoops = true;

	/**
	 * Whoops instance cache
	 */
	protected Whoops|null $whoops = null;

	/**
	 * Replaces absolute file paths with placeholders such as
	 * {kirby_folder}, {site_folder} or {index_folder} to avoid
	 * exposing too many details about the filesystem and keeping
	 * error responses short and readable in debug mode.
	 *
	 * @since 5.3.0
	 * @internal
	 */
	public function disguiseFilePath(string $file): string
	{
		$disguise = [
			$this->root('kirby') => '{kirby}',
			$this->root('site')  => '{site}',
			$this->root('index') => '{index}'
		];

		return str_replace(
			array_keys($disguise),
			array_values($disguise),
			$file
		);
	}

	/**
	 * Registers the PHP error handler for CLI usage
	 */
	protected function handleCliErrors(): void
	{
		$this->setWhoopsHandler(new PlainTextHandler());
	}

	/**
	 * Registers the PHP error handler
	 * based on the environment
	 */
	protected function handleErrors(): void
	{
		// no matter the environment, exit early if
		// Whoops was disabled globally
		// (but continue if the option was explicitly
		// set to `true` in the config)
		if (
			static::$enableWhoops === false &&
			$this->option('whoops') !== true
		) {
			return;
		}

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
	 */
	protected function handleHtmlErrors(): void
	{
		$handler = null;

		if ($this->option('debug') === true) {
			if ($this->option('whoops', true) !== false) {
				$handler = new PrettyPageHandler();
				$handler->setPageTitle('Kirby CMS Debugger');
				$handler->addResourcePath(dirname(__DIR__, 2) . '/assets');
				$handler->addCustomCss('whoops.css');

				if ($editor = $this->option('editor')) {
					$handler->setEditor($editor);
				}

				if ($blocklist = $this->option('whoops.blocklist')) {
					foreach ($blocklist as $superglobal => $vars) {
						foreach ($vars as $var) {
							$handler->blacklist($superglobal, $var);
						}
					}
				}
			}
		} else {
			// the app must only be referenced weakly,
			// see `::getAdditionalWhoopsHandler()`
			$app = WeakReference::create($this);

			$handler = new CallbackHandler(static function ($exception, $inspector, $run) use ($app) {
				/** @var \Kirby\Cms\App $kirby */
				$kirby = $app->get() ?? App::instance();
				$fatal = $kirby->option('fatal');

				if ($fatal instanceof Closure) {
					echo $fatal($kirby, $exception);
				} else {
					include $kirby->root('kirby') . '/views/fatal.php';
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
	 */
	protected function handleJsonErrors(): void
	{
		// the app must only be referenced weakly,
		// see `getAdditionalWhoopsHandler()`
		$app = WeakReference::create($this);

		$handler = new CallbackHandler(static function ($exception, $inspector, $run) use ($app) {
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

			/** @var \Kirby\Cms\App $kirby */
			$kirby  = $app->get() ?? App::instance();
			$editor = $kirby->option('editor', false);

			if ($kirby->option('debug') === true) {
				echo Response::json([
					'status'    => 'error',
					'exception' => $exception::class,
					'code'      => $code,
					'message'   => $exception->getMessage(),
					'details'   => $details,
					'file'      => $kirby->disguiseFilePath($file = $exception->getFile()),
					'line'      => $line = $exception->getLine(),
					'editor'    => Url::editor($editor, $file, $line),
					'trace'     => $kirby->trace($exception, $editor),
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
	 * @since 6.0.0
	 */
	protected function trace(Throwable $exception, string|false $editor): array
	{
		return array_map(function ($item) use ($editor) {
			if (isset($item['file']) === true) {
				$item['url']  = Url::editor($editor, $item['file'], $item['line']);
				$item['file'] = $this->disguiseFilePath($item['file']);
			}

			$item['function'] = $this->disguiseFilePath($item['function']);

			unset($item['args']);
			return $item;
		}, $exception->getTrace());
	}

	/**
	 * Enables Whoops with the specified handler
	 */
	protected function setWhoopsHandler(callable|HandlerInterface $handler): void
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
		// Whoops keeps every `Run` instance alive for the rest of the process
		// via `register_shutdown_function()`. A handler closure that captured
		// `$this` strongly would pin $app, potentially incl. site tree forever.
		// Referencing the app weakly allows it to be garbage collected.
		$app = WeakReference::create($this);

		return new CallbackHandler(static function ($exception, $inspector, $run) use ($app) {
			/** @var \Kirby\Cms\App $kirby */
			$kirby    = $app->get() ?? App::instance();
			$isLogged = true;

			// allow hook to modify whether the exception should be logged
			$isLogged = $kirby->apply(
				'system.exception',
				compact('exception', 'isLogged'),
				'isLogged'
			);

			if ($isLogged !== false) {
				error_log($exception);
			}

			return Handler::DONE;
		});
	}

	/**
	 * Clears the Whoops handlers and disables Whoops
	 */
	protected function unsetWhoopsHandler(): void
	{
		$whoops = $this->whoops();
		$whoops->clearHandlers();
		$whoops->unregister(); // will only do something if currently registered
	}

	/**
	 * Returns the Whoops error handler instance
	 */
	protected function whoops(): Whoops
	{
		return $this->whoops ??= new Whoops();
	}
}
