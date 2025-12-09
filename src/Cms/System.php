<?php

namespace Kirby\Cms;

use Kirby\Cms\System\UpdateStatus;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The System class gathers all information
 * about the server, PHP and other environment
 * parameters and checks for a valid setup.
 *
 * This is mostly used by the panel installer
 * to check if the panel can be installed at all.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class System
{
	// cache
	protected License|null $license = null;
	protected UpdateStatus|null $updateStatus = null;

	public function __construct(protected App $app)
	{
		// try to create all folders that could be missing
		$this->init();
	}

	/**
	 * Check for a writable accounts folder
	 */
	public function accounts(): bool
	{
		return is_writable($this->app->root('accounts')) === true;
	}

	/**
	 * Check for a writable content folder
	 */
	public function content(): bool
	{
		return is_writable($this->app->root('content')) === true;
	}

	/**
	 * Check for an existing curl extension
	 */
	public function curl(): bool
	{
		return extension_loaded('curl') === true;
	}

	/**
	 * Returns the URL to the file within a system folder
	 * if the file is located in the document
	 * root. Otherwise it will return null.
	 *
	 * @param string $folder 'git', 'content', 'site', 'kirby'
	 */
	public function exposedFileUrl(string $folder): string|null
	{
		if (!$url = $this->folderUrl($folder)) {
			return null;
		}

		switch ($folder) {
			case 'content':
				return $url . '/' . basename($this->app->site()->version('latest')->contentFile());
			case 'git':
				return $url . '/config';
			case 'kirby':
				return $url . '/LICENSE.md';
			case 'site':
				$root  = $this->app->root('site');
				$files = glob($root . '/blueprints/*.yml');

				if (empty($files) === true) {
					$files = glob($root . '/templates/*.*');
				}

				if (empty($files) === true) {
					$files = glob($root . '/snippets/*.*');
				}

				if (empty($files) === true || empty($files[0]) === true) {
					return $url;
				}

				$file = $files[0];
				$file = basename(dirname($file)) . '/' . basename($file);

				return $url . '/' . $file;
			default:
				return null;
		}
	}

	/**
	 * Returns the URL to a system folder
	 * if the folder is located in the document
	 * root. Otherwise it will return null.
	 *
	 * @param string $folder 'git', 'content', 'site', 'kirby'
	 */
	public function folderUrl(string $folder): string|null
	{
		$index = $this->app->root('index');
		$root  = match ($folder) {
			'git'   => $index . '/.git',
			default => $this->app->root($folder)
		};

		if (
			$root === null ||
			is_dir($root) === false ||
			is_dir($index) === false
		) {
			return null;
		}

		$root  = realpath($root);
		$index = realpath($index);

		// windows
		$root  = str_replace('\\', '/', $root);
		$index = str_replace('\\', '/', $index);

		// the folder is not within the document root?
		if (Str::startsWith($root, $index) === false) {
			return null;
		}

		// get the path after the document root
		$path = trim(Str::after($root, $index), '/');

		// build the absolute URL to the folder
		return Url::to($path);
	}

	/**
	 * Returns the app's human-readable
	 * index URL without scheme
	 */
	public function indexUrl(): string
	{
		return $this->app->url('index', true)
			->setScheme(null)
			->setSlash(false)
			->toString();
	}

	/**
	 * Returns an array with relevant system information
	 * used for debugging
	 * @since 4.3.0
	 */
	public function info(): array
	{
		return [
			'kirby'     => $this->app->version(),
			'php'       => phpversion(),
			'server'    => $this->serverSoftware(),
			'license'   => $this->license()->label(),
			'languages' => $this->app->languages()->values(
				fn ($lang) => $lang->code()
			)
		];
	}

	/**
	 * Create the most important folders
	 * if they don't exist yet
	 *
	 * @throws \Kirby\Exception\PermissionException
	 */
	public function init(): void
	{
		// init /site/accounts
		try {
			Dir::make($this->app->root('accounts'));
		} catch (Throwable) {
			throw new PermissionException(
				message: 'The accounts directory could not be created'
			);
		}

		// init /site/sessions
		try {
			Dir::make($this->app->root('sessions'));
		} catch (Throwable) {
			throw new PermissionException(
				message: 'The sessions directory could not be created'
			);
		}

		// init /content
		try {
			Dir::make($this->app->root('content'));
		} catch (Throwable) {
			throw new PermissionException(
				message: 'The content directory could not be created'
			);
		}

		// init /media
		try {
			Dir::make($this->app->root('media'));
		} catch (Throwable) {
			throw new PermissionException(
				message: 'The media directory could not be created'
			);
		}
	}

	/**
	 * Check if the Panel has 2FA activated
	 */
	public function is2FA(): bool
	{
		return ($this->loginMethods()['password']['2fa'] ?? null) === true;
	}

	/**
	 * Check if the Panel has 2FA with TOTP activated
	 */
	public function is2FAWithTOTP(): bool
	{
		return
			$this->is2FA() === true &&
			in_array('totp', $this->app->auth()->enabledChallenges(), true) === true;
	}

	/**
	 * Check if the Panel is installable.
	 * On a public server the panel.install
	 * option must be explicitly set to true
	 * to get the installer up and running.
	 */
	public function isInstallable(): bool
	{
		return
			$this->isLocal() === true ||
			$this->app->option('panel.install', false) === true;
	}

	/**
	 * Check if Kirby is already installed
	 */
	public function isInstalled(): bool
	{
		return $this->app->users()->count() > 0;
	}

	/**
	 * Check if this is a local installation
	 */
	public function isLocal(): bool
	{
		return $this->app->environment()->isLocal();
	}

	/**
	 * Check if all tests pass
	 */
	public function isOk(): bool
	{
		return in_array(false, array_values($this->status()), true) === false;
	}

	/**
	 * Loads the license file and returns
	 * the license information if available
	 */
	public function license(): License
	{
		return $this->license ??= License::read();
	}

	/**
	 * Returns the configured UI modes for the login form
	 * with their respective options
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the configuration is invalid
	 *                                                   (only in debug mode)
	 */
	public function loginMethods(): array
	{
		$default = ['password' => []];
		$methods = A::wrap($this->app->option('auth.methods', $default));

		// normalize the syntax variants
		$normalized = [];
		$uses2fa = false;
		foreach ($methods as $key => $value) {
			if (is_int($key) === true) {
				// ['password']
				$normalized[$value] = [];
			} elseif ($value === true) {
				// ['password' => true]
				$normalized[$key] = [];
			} else {
				// ['password' => [...]]
				$normalized[$key] = $value;

				if (isset($value['2fa']) === true && $value['2fa'] === true) {
					$uses2fa = true;
				}
			}
		}

		// 2FA must not be circumvented by code-based modes
		foreach (['code', 'password-reset'] as $method) {
			if ($uses2fa === true && isset($normalized[$method]) === true) {
				unset($normalized[$method]);

				if ($this->app->option('debug') === true) {
					$message = 'The "' . $method . '" login method cannot be enabled when 2FA is required';
					throw new InvalidArgumentException($message);
				}
			}
		}

		// only one code-based mode can be active at once
		if (
			isset($normalized['code']) === true &&
			isset($normalized['password-reset']) === true
		) {
			unset($normalized['code']);

			if ($this->app->option('debug') === true) {
				$message = 'The "code" and "password-reset" login methods cannot be enabled together';
				throw new InvalidArgumentException($message);
			}
		}

		return $normalized;
	}

	/**
	 * Check for an existing mbstring extension
	 */
	public function mbString(): bool
	{
		return extension_loaded('mbstring') === true;
	}

	/**
	 * Check for a writable media folder
	 */
	public function media(): bool
	{
		return is_writable($this->app->root('media')) === true;
	}

	/**
	 * Check for a valid PHP version
	 */
	public function php(): bool
	{
		return
			version_compare(PHP_VERSION, '8.2.0', '>=') === true &&
			version_compare(PHP_VERSION, '8.6.0', '<')  === true;
	}

	/**
	 * Returns a sorted collection of all
	 * installed plugins
	 */
	public function plugins(): Collection
	{
		$plugins = new Collection($this->app->plugins());
		return $plugins->sortBy('name', 'asc');
	}

	/**
	 * Validates the license key
	 * and adds it to the .license file in the config
	 * folder if possible.
	 *
	 * @throws \Kirby\Exception\Exception
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function register(string|null $license = null, string|null $email = null): bool
	{
		$license = new License(
			code: $license,
			domain: $this->indexUrl(),
			email: $email,
		);

		$this->license = $license->register();
		return true;
	}

	/**
	 * Returns the detected server software
	 */
	public function serverSoftware(): string
	{
		return $this->app->environment()->get('SERVER_SOFTWARE', '–');
	}

	/**
	 * Returns the short version of the detected server software
	 * @since 4.6.0
	 */
	public function serverSoftwareShort(): string
	{
		$software = $this->serverSoftware();
		return strtok($software, ' ');
	}

	/**
	 * Check for a writable sessions folder
	 */
	public function sessions(): bool
	{
		return is_writable($this->app->root('sessions')) === true;
	}

	/**
	 * Get an status array of all checks
	 */
	public function status(): array
	{
		return [
			'accounts' => $this->accounts(),
			'content'  => $this->content(),
			'curl'     => $this->curl(),
			'sessions' => $this->sessions(),
			'mbstring' => $this->mbstring(),
			'media'    => $this->media(),
			'php'      => $this->php()
		];
	}

	/**
	 * Returns the site's title as defined in the
	 * content file or `site.yml` blueprint
	 * @since 3.6.0
	 */
	public function title(): string
	{
		$site = $this->app->site();

		if ($site->title()->isNotEmpty() === true) {
			return $site->title()->value();
		}

		return $site->blueprint()->title();
	}

	public function toArray(): array
	{
		return $this->status();
	}

	/**
	 * Returns the update status object unless
	 * the update check for Kirby has been disabled
	 * @since 3.8.0
	 *
	 * @param array|null $data Custom override for the getkirby.com update data
	 */
	public function updateStatus(array|null $data = null): UpdateStatus|null
	{
		if ($this->updateStatus !== null) {
			return $this->updateStatus;
		}

		$kirby  = $this->app;
		$option =
			$kirby->option('updates.kirby') ??
			$kirby->option('updates', true);

		if ($option === false) {
			return null;
		}

		return $this->updateStatus = new UpdateStatus(
			$kirby,
			$option === 'security',
			$data
		);
	}

	/**
	 * Upgrade to the new folder separator
	 */
	public static function upgradeContent(string $root): void
	{
		$index = Dir::read($root);

		foreach ($index as $dir) {
			$oldRoot = $root . '/' . $dir;
			$newRoot = preg_replace('!\/([0-9]+)\-!', '/$1_', $oldRoot);

			if (is_dir($oldRoot) === true) {
				Dir::move($oldRoot, $newRoot);
				static::upgradeContent($newRoot);
			}
		}
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}
}
