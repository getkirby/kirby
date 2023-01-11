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
	/**
	 * @var \Kirby\Cms\App
	 */
	protected $app;

	// cache
	protected UpdateStatus|null $updateStatus = null;

	/**
	 * @param \Kirby\Cms\App $app
	 */
	public function __construct(App $app)
	{
		$this->app = $app;

		// try to create all folders that could be missing
		$this->init();
	}

	/**
	 * Improved `var_dump` output
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Check for a writable accounts folder
	 *
	 * @return bool
	 */
	public function accounts(): bool
	{
		return is_writable($this->app->root('accounts'));
	}

	/**
	 * Check for a writable content folder
	 *
	 * @return bool
	 */
	public function content(): bool
	{
		return is_writable($this->app->root('content'));
	}

	/**
	 * Check for an existing curl extension
	 *
	 * @return bool
	 */
	public function curl(): bool
	{
		return extension_loaded('curl');
	}

	/**
	 * Returns the URL to the file within a system folder
	 * if the file is located in the document
	 * root. Otherwise it will return null.
	 *
	 * @param string $folder 'git', 'content', 'site', 'kirby'
	 * @return string|null
	 */
	public function exposedFileUrl(string $folder): string|null
	{
		if (!$url = $this->folderUrl($folder)) {
			return null;
		}

		switch ($folder) {
			case 'content':
				return $url . '/' . basename($this->app->site()->contentFile());
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
	 * @return string|null
	 */
	public function folderUrl(string $folder): string|null
	{
		$index = $this->app->root('index');

		if ($folder === 'git') {
			$root = $index . '/.git';
		} else {
			$root = $this->app->root($folder);
		}

		if ($root === null || is_dir($root) === false || is_dir($index) === false) {
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
	 *
	 * @return string
	 */
	public function indexUrl(): string
	{
		return $this->app->url('index', true)->setScheme(null)->setSlash(false)->toString();
	}

	/**
	 * Create the most important folders
	 * if they don't exist yet
	 *
	 * @return void
	 * @throws \Kirby\Exception\PermissionException
	 */
	public function init()
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
	 *
	 * @return bool
	 */
	public function isInstallable(): bool
	{
		return $this->isLocal() === true || $this->app->option('panel.install', false) === true;
	}

	/**
	 * Check if Kirby is already installed
	 *
	 * @return bool
	 */
	public function isInstalled(): bool
	{
		return $this->app->users()->count() > 0;
	}

	/**
	 * Check if this is a local installation
	 *
	 * @return bool
	 */
	public function isLocal(): bool
	{
		return $this->app->environment()->isLocal();
	}

	/**
	 * Check if all tests pass
	 *
	 * @return bool
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
		try {
			$license = Json::read($this->app->root('license'));
		} catch (Throwable) {
			return false;
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
			return false;
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
		if (openssl_verify(json_encode($data), hex2bin($license['signature']), $pubKey, 'RSA-SHA256') !== 1) {
			return false;
		}

		// verify the URL
		if ($this->licenseUrl() !== $this->licenseUrl($license['domain'])) {
			return false;
		}

		// only return the actual license key if the
		// current user has appropriate permissions
		if ($this->app->user()?->isAdmin() === true) {
			return $license['license'];
		}

		return true;
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
		if ($url === null) {
			$url = $this->indexUrl();
		}

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
	 * @return array
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
	 *
	 * @return bool
	 */
	public function mbString(): bool
	{
		return extension_loaded('mbstring');
	}

	/**
	 * Check for a writable media folder
	 *
	 * @return bool
	 */
	public function media(): bool
	{
		return is_writable($this->app->root('media'));
	}

	/**
	 * Check for a valid PHP version
	 *
	 * @return bool
	 */
	public function php(): bool
	{
		return
			version_compare(PHP_VERSION, '8.0.0', '>=') === true &&
			version_compare(PHP_VERSION, '8.2.0', '<')  === true;
	}

	/**
	 * Returns a sorted collection of all
	 * installed plugins
	 *
	 * @return \Kirby\Cms\Collection
	 */
	public function plugins()
	{
		return (new Collection(App::instance()->plugins()))->sortBy('name', 'asc');
	}

	/**
	 * Validates the license key
	 * and adds it to the .license file in the config
	 * folder if possible.
	 *
	 * @param string|null $license
	 * @param string|null $email
	 * @return bool
	 * @throws \Kirby\Exception\Exception
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function register(string $license = null, string $email = null): bool
	{
		if (Str::startsWith($license, 'K3-PRO-') === false) {
			throw new InvalidArgumentException([
				'key' => 'license.format'
			]);
		}

		if (V::email($email) === false) {
			throw new InvalidArgumentException([
				'key' => 'license.email'
			]);
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
	 *
	 * @return bool
	 */
	public function server(): bool
	{
		return $this->serverSoftware() !== null;
	}

	/**
	 * Returns the detected server software
	 *
	 * @return string|null
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
	 *
	 * @return bool
	 */
	public function sessions(): bool
	{
		return is_writable($this->app->root('sessions'));
	}

	/**
	 * Get an status array of all checks
	 *
	 * @return array
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
	 *
	 * @return string
	 */
	public function title(): string
	{
		$site = $this->app->site();

		if ($site->title()->isNotEmpty()) {
			return $site->title()->value();
		}

		return $site->blueprint()->title();
	}

	/**
	 * @return array
	 */
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
		$option = $kirby->option('updates.kirby') ?? $kirby->option('updates') ?? true;

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
	 *
	 * @param string $root
	 * @return void
	 */
	public static function upgradeContent(string $root)
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
}
