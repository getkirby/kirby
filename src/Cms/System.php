<?php

namespace Kirby\Cms;

use Kirby\Cms\System\UpdateStatus;
use Kirby\Data\Json;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
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
	protected array|bool|null $license = null;
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
				return $url . '/' . basename($this->app->site()->storage()->contentFile(
					'published',
					'default'
				));
			case 'git':
				return $url . '/config';
			case 'kirby':
				return $url . '/composer.json';
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
			throw new PermissionException('The accounts directory could not be created');
		}

		// init /site/sessions
		try {
			Dir::make($this->app->root('sessions'));
		} catch (Throwable) {
			throw new PermissionException('The sessions directory could not be created');
		}

		// init /content
		try {
			Dir::make($this->app->root('content'));
		} catch (Throwable) {
			throw new PermissionException('The content directory could not be created');
		}

		// init /media
		try {
			Dir::make($this->app->root('media'));
		} catch (Throwable) {
			throw new PermissionException('The media directory could not be created');
		}
	}

	/**
	 * Check if the panel is installable.
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
	 *
	 * @return string|bool License key or `false` if the current user has
	 *                     permissions for access.settings, otherwise just a
	 *                     boolean that tells whether a valid license is active
	 */
	public function license()
	{
		if ($this->license !== null) {
			return $this->license;
		}

		try {
			$license = Json::read($this->app->root('license'));
		} catch (Throwable) {
			return $this->license = false;
		}

		// check for all required fields for the validation
		if (isset(
			$license['license'],
			$license['order'],
			$license['date'],
			$license['email'],
			$license['domain'],
			$license['signature']
		) !== true) {
			return $this->license = false;
		}

		// build the license verification data
		$data = [
			'license' => $license['license'],
			'order'   => $license['order'],
			'email'   => hash('sha256', $license['email'] . 'kwAHMLyLPBnHEskzH9pPbJsBxQhKXZnX'),
			'domain'  => $license['domain'],
			'date'    => $license['date']
		];


		// get the public key
		$pubKey = F::read($this->app->root('kirby') . '/kirby.pub');

		// verify the license signature
		$data      = json_encode($data);
		$signature = hex2bin($license['signature']);
		if (openssl_verify($data, $signature, $pubKey, 'RSA-SHA256') !== 1) {
			return $this->license = false;
		}

		// verify the URL
		if ($this->licenseUrl() !== $this->licenseUrl($license['domain'])) {
			return $this->license = false;
		}

		// only return the actual license key if the
		// current user has appropriate permissions
		if ($this->app->user()?->isAdmin() === true) {
			return $this->license = $license['license'];
		}

		return $this->license = true;
	}

	/**
	 * Normalizes the app's index URL for
	 * licensing purposes
	 *
	 * @param string|null $url Input URL, by default the app's index URL
	 * @return string Normalized URL
	 */
	protected function licenseUrl(string $url = null): string
	{
		$url ??= $this->indexUrl();

		// remove common "testing" subdomains as well as www.
		// to ensure that installations of the same site have
		// the same license URL; only for installations at /,
		// subdirectory installations are difficult to normalize
		if (Str::contains($url, '/') === false) {
			if (Str::startsWith($url, 'www.')) {
				return substr($url, 4);
			}

			if (Str::startsWith($url, 'dev.')) {
				return substr($url, 4);
			}

			if (Str::startsWith($url, 'test.')) {
				return substr($url, 5);
			}

			if (Str::startsWith($url, 'staging.')) {
				return substr($url, 8);
			}
		}

		return $url;
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
			version_compare(PHP_VERSION, '8.0.0', '>=') === true &&
			version_compare(PHP_VERSION, '8.3.0', '<')  === true;
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
	public function register(string $license = null, string $email = null): bool
	{
		if (Str::startsWith($license, 'K3-PRO-') === false) {
			throw new InvalidArgumentException(['key' => 'license.format']);
		}

		if (V::email($email) === false) {
			throw new InvalidArgumentException(['key' => 'license.email']);
		}

		// @codeCoverageIgnoreStart
		$response = Remote::get('https://hub.getkirby.com/register', [
			'data' => [
				'license' => $license,
				'email'   => Str::lower(trim($email)),
				'domain'  => $this->indexUrl()
			]
		]);

		if ($response->code() !== 200) {
			throw new Exception($response->content());
		}

		// decode the response
		$json = Json::decode($response->content());

		// replace the email with the plaintext version
		$json['email'] = $email;

		// where to store the license file
		$file = $this->app->root('license');

		// save the license information
		Json::write($file, $json);

		// clear the license cache
		$this->license = null;

		if ($this->license() === false) {
			throw new InvalidArgumentException([
				'key' => 'license.verification'
			]);
		}
		// @codeCoverageIgnoreEnd

		return true;
	}

	/**
	 * Check for a valid server environment
	 */
	public function server(): bool
	{
		return $this->serverSoftware() !== null;
	}

	/**
	 * Returns the detected server software
	 */
	public function serverSoftware(): string|null
	{
		$servers = $this->app->option('servers', [
			'apache',
			'caddy',
			'litespeed',
			'nginx',
			'php'
		]);

		$software = $this->app->environment()->get('SERVER_SOFTWARE', '');

		preg_match('!(' . implode('|', A::wrap($servers)) . ')!i', $software, $matches);

		return $matches[0] ?? null;
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
			'accounts'  => $this->accounts(),
			'content'   => $this->content(),
			'curl'      => $this->curl(),
			'sessions'  => $this->sessions(),
			'mbstring'  => $this->mbstring(),
			'media'     => $this->media(),
			'php'       => $this->php(),
			'server'    => $this->server(),
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
