<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * The environment object takes care of
 * secure host and base URL detection, as
 * well as loading the dedicated
 * environment options.
 * @since 3.7.0
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Environment
{
	/**
	 * Full base URL object
	 */
	protected Uri $baseUri;

	/**
	 * Full base URL
	 */
	protected string $baseUrl;

	/**
	 * Whether the request is being served by the CLI
	 */
	protected bool $cli;

	/**
	 * Current host name
	 */
	protected string|null $host;

	/**
	 * Whether the HTTPS protocol is used
	 */
	protected bool $https;

	/**
	 * Sanitized `$_SERVER` data
	 */
	protected array $info;

	/**
	 * Current server's IP address
	 */
	protected string|null $ip;

	/**
	 * Whether the site is behind a reverse proxy;
	 * `null` if not known (fixed allowed URL setup)
	 */
	protected bool|null $isBehindProxy;

	/**
	 * URI path to the base
	 */
	protected string $path;

	/**
	 * Port number in the site URL
	 */
	protected int|null $port;

	/**
	 * Intermediary value of the port
	 * extracted from the host name
	 */
	protected int|null $portInHost = null;

	/**
	 * Uri object for the full request URI.
	 * It is a combination of the base URL and `REQUEST_URI`
	 */
	protected Uri $requestUri;

	/**
	 * Full request URL
	 */
	protected string $requestUrl;

	/**
	 * Path to the php script within the
	 * document root without the
	 * filename of the script
	 */
	protected string $scriptPath;

	/**
	 * Class constructor
	 *
	 * @param array|null $info Optional override for `$_SERVER`
	 */
	public function __construct(array|null $options = null, array|null $info = null)
	{
		$this->detect($options, $info);
	}

	/**
	 * Returns the server's IP address
	 * @see static::ip
	 */
	public function address(): string|null
	{
		return $this->ip();
	}

	/**
	 * Returns the full base URL object
	 */
	public function baseUri(): Uri
	{
		return $this->baseUri;
	}

	/**
	 * Returns the full base URL
	 */
	public function baseUrl(): string
	{
		return $this->baseUrl;
	}

	/**
	 * Checks if the request is being served by the CLI
	 */
	public function cli(): bool
	{
		return $this->cli;
	}

	/**
	 * Sanitizes the server info and detects
	 * all relevant parts. This can be called
	 * again at a later point to overwrite all
	 * the stored information and re-detect the
	 * environment if necessary.
	 *
	 * @param array|null $info Optional override for `$_SERVER`
	 */
	public function detect(array $options = null, array $info = null): array
	{
		$info  ??= $_SERVER;
		$options = array_merge([
			'cli'     => null,
			'allowed' => null
		], $options ?? []);

		$this->info          = static::sanitize($info);
		$this->cli           = $this->detectCli($options['cli']);
		$this->ip            = $this->detectIp();
		$this->host          = null;
		$this->https         = false;
		$this->isBehindProxy = null;
		$this->scriptPath    = $this->detectScriptPath($this->get('SCRIPT_NAME'));
		$this->path          = $this->detectPath($this->scriptPath);
		$this->port          = null;

		// insecure auto-detection
		if ($options['allowed'] === '*' || $options['allowed'] === ['*']) {
			$this->detectAuto(true);

		// fixed environments
		} elseif (empty($options['allowed']) === false) {
			$this->detectAllowed($options['allowed']);

		// secure auto-detection
		} else {
			$this->detectAuto();
		}

		// build the URI based on the detected params
		$this->detectBaseUri();

		// build the request URI based on the detected URL
		$this->detectRequestUri($this->get('REQUEST_URI'));

		// return the sanitized $_SERVER array
		return $this->info;
	}

	/**
	 * Sets the host name, port, path and protocol from the
	 * fixed list of allowed URLs
	 */
	protected function detectAllowed(array|string $allowed): void
	{
		$allowed = A::wrap($allowed);

		// with a single allowed URL, the entire
		// environment will be based on that
		if (count($allowed) === 1) {
			$baseUrl = A::first($allowed);

			if (is_string($baseUrl) === false) {
				throw new InvalidArgumentException('Invalid allow list setup for base URLs');
			}

			$uri = new Uri($baseUrl, ['slash' => false]);

			$this->host  = $uri->host();
			$this->https = $uri->https();
			$this->port  = $uri->port();
			$this->path  = $uri->path()->toString();
			return;
		}

		// run insecure auto detection to get
		// host, port and https from the environment;
		// security is achieved by checking against
		// the fixed allowlist below
		$this->detectAuto(true);

		// build the baseUrl based on the detected environment
		// to compare it against what is allowed
		$this->detectBaseUri();

		foreach ($allowed as $url) {
			// skip invalid URLs
			if (is_string($url) === false) {
				continue;
			}

			$uri = new Uri($url, ['slash' => false]);

			if ($uri->toString() === $this->baseUrl) {
				// the current environment is allowed,
				// stop before the exception below is thrown
				return;
			}
		}

		throw new InvalidArgumentException('The environment is not allowed');
	}

	/**
	 * Sets the host name, port and protocol without configuration
	 *
	 * @param bool $insecure Include the `Host`, `Forwarded` and `X-Forwarded-*` headers in the search
	 */
	protected function detectAuto(bool $insecure = false): void
	{
		// proxy server setup
		if ($insecure === true) {
			$forwarded = $this->detectForwarded();

			$host  = $forwarded['host'];
			$port  = $forwarded['port'];
			$https = $forwarded['https'];

			if ($host || $port || $https) {
				$this->isBehindProxy = true;

				// if a port or scheme is defined but no host, assume
				// that the host is the same as PHP's own hostname
				// (which is often the case with reverse proxies)
				$this->host  = $host ?? $this->detectHost($insecure);
				$this->port  = $port;
				$this->https = $https;

				return;
			}
		}

		// local server setup
		$this->isBehindProxy = false;

		$this->host  = $this->detectHost($insecure);
		$this->https = $this->detectHttps();
		$this->port  = $this->detectPort();
	}

	/**
	 * Builds the base URL based on the
	 * given environment params
	 */
	protected function detectBaseUri(): Uri
	{
		$this->baseUri = new Uri([
			'host'   => $this->host,
			'path'   => $this->path,
			'port'   => $this->port,
			'scheme' => $this->https ? 'https' : 'http',
		]);

		$this->baseUrl = $this->baseUri->toString();

		return $this->baseUri;
	}

	/**
	 * Detects if the request is served by the CLI
	 *
	 * @param bool|null $override Set to a boolean to override detection (for testing)
	 */
	protected function detectCli(bool|null $override = null): bool
	{
		if (is_bool($override) === true) {
			return $override;
		}

		if (defined('STDIN') === true) {
			return true;
		}

		// @codeCoverageIgnoreStart
		$term = getenv('TERM');

		if (substr(PHP_SAPI, 0, 3) === 'cgi' && $term && $term !== 'unknown') {
			return true;
		}

		return false;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Detects the host, protocol, port and client IP
	 * from the `Forwarded` and `X-Forwarded-*` headers
	 */
	protected function detectForwarded(): array
	{
		$data = [
			'for'   => null,
			'host'  => null,
			'https' => false,
			'port'  => null
		];

		// prefer the standardized `Forwarded` header if defined
		$forwarded = $this->get('HTTP_FORWARDED');
		if ($forwarded) {
			// only use the first (outermost) proxy by using the first set of values
			// before the first comma (but only a comma outside of quotes)
			if (Str::contains($forwarded, ',') === true) {
				$forwarded = preg_split('/"[^"]*"(*SKIP)(*F)|,/', $forwarded)[0];
			}

			// split into separate key=value;key=value fields by semicolon,
			// but only split outside of quotes
			$rawFields = preg_split('/"[^"]*"(*SKIP)(*F)|;/', $forwarded);

			// split key and value into an associative array
			$fields = [];
			foreach ($rawFields as $field) {
				$key   = Str::lower(Str::before($field, '='));
				$value = Str::after($field, '=');

				// trim the surrounding quotes
				if (Str::substr($value, 0, 1) === '"') {
					$value = Str::substr($value, 1, -1);
				}

				$fields[$key] = $value;
			}

			// assemble the normalized data
			if (isset($fields['host']) === true) {
				$parts = $this->detectPortInHost($fields['host']);
				$data['host'] = $parts['host'];
				$data['port'] = $parts['port'];
			}

			if (isset($fields['proto']) === true) {
				$data['https'] = $this->detectHttpsProtocol($fields['proto']);
			}

			if ($data['https'] === true) {
				$data['port'] ??= 443;
			}

			$data['for'] = $parts['for'] ?? null;

			return $data;
		}

		// no success, try the `X-Forwarded-*` headers
		$data['host']  = $this->detectForwardedHost();
		$data['https'] = $this->detectForwardedHttps();
		$data['port']  = $this->detectForwardedPort($data['https']);
		$data['for']   = $this->get('HTTP_X_FORWARDED_FOR');

		return $data;
	}

	/**
	 * Detects the host name of the reverse proxy
	 * from the `X-Forwarded-Host` header
	 */
	protected function detectForwardedHost(): string|null
	{
		$host  = $this->get('HTTP_X_FORWARDED_HOST');
		$parts = $this->detectPortInHost($host);

		$this->portInHost = $parts['port'];

		return $parts['host'];
	}

	/**
	 * Detects the protocol of the reverse proxy from the
	 * `X-Forwarded-SSL` or `X-Forwarded-Proto` header
	 */
	protected function detectForwardedHttps(): bool
	{
		if ($this->detectHttpsOn($this->get('HTTP_X_FORWARDED_SSL')) === true) {
			return true;
		}

		if ($this->detectHttpsProtocol($this->get('HTTP_X_FORWARDED_PROTO')) === true) {
			return true;
		}

		return false;
	}

	/**
	 * Detects the port of the reverse proxy from the
	 * `X-Forwarded-Host` or `X-Forwarded-Port` header
	 *
	 * @param bool $https Whether HTTPS was detected
	 */
	protected function detectForwardedPort(bool $https): int|null
	{
		// based on forwarded port
		$port = $this->get('HTTP_X_FORWARDED_PORT');

		if (is_int($port) === true) {
			return $port;
		}

		// based on forwarded host
		if (is_int($this->portInHost) === true) {
			return $this->portInHost;
		}

		// based on the detected https state
		if ($https === true) {
			return 443;
		}

		return null;
	}

	/**
	 * Detects the host name from various headers
	 *
	 * @param bool $insecure Include the `Host` header in the search
	 */
	protected function detectHost(bool $insecure = false): string|null
	{
		$hosts = [];

		if ($insecure === true) {
			$hosts[] = $this->get('HTTP_HOST');
		}

		$hosts[] = $this->get('SERVER_NAME');
		$hosts[] = $this->get('SERVER_ADDR');

		// use the first header that is not empty
		$hosts = array_filter($hosts);
		$host  = A::first($hosts);

		$parts = $this->detectPortInHost($host);

		$this->portInHost = $parts['port'];

		return $parts['host'];
	}

	/**
	 * Detects the HTTPS status
	 */
	protected function detectHttps(): bool
	{
		if ($this->detectHttpsOn($this->get('HTTPS')) === true) {
			return true;
		}

		return false;
	}

	/**
	 * Normalizes the HTTPS status into a boolean
	 */
	protected function detectHttpsOn(string|int|bool|null $value): bool
	{
		// off can mean many things :)
		$off = ['off', null, '', 0, '0', false, 'false', -1, '-1'];

		return in_array($value, $off, true) === false;
	}

	/**
	 * Detects the HTTPS status from a `X-Forwarded-Proto` string
	 */
	protected function detectHttpsProtocol(string|null $protocol = null): bool
	{
		if ($protocol === null) {
			return false;
		}

		return in_array(strtolower($protocol), ['https', 'https, http']) === true;
	}

	/**
	 * Detects the server's IP address
	 */
	protected function detectIp(): string|null
	{
		return $this->get('SERVER_ADDR');
	}

	/**
	 * Detects the URI path unless in CLI mode
	 */
	protected function detectPath(string|null $path = null): string
	{
		if ($this->cli === true) {
			return '';
		}

		return $path ?? '';
	}

	/**
	 * Detects the port from various sources
	 */
	protected function detectPort(): int|null
	{
		// based on server port
		$port = $this->get('SERVER_PORT');

		if (is_int($port) === true) {
			return $port;
		}

		// based on the detected host
		if (is_int($this->portInHost) === true) {
			return $this->portInHost;
		}

		// based on the detected https state
		if ($this->https === true) {
			return 443;
		}

		return null;
	}

	/**
	 * Splits a hostname:port string into its components
	 */
	protected function detectPortInHost(string|null $host = null): array
	{
		if (empty($host) === true) {
			return [
				'host' => null,
				'port' => null
			];
		}

		$parts = Str::split($host, ':');

		return [
			'host' => $parts[0] ?? null,
			'port' => static::sanitizePort($parts[1] ?? null),
		];
	}

	/**
	 * Splits any URI into path and query
	 */
	protected function detectRequestUri(string|null $requestUri = null): Uri
	{
		// make sure the URL parser works properly when there's a
		// colon in the request URI but the URI is relative
		if (Url::isAbsolute($requestUri) === false) {
			$requestUri = 'https://getkirby.com' . $requestUri;
		}

		$uri = new Uri($requestUri);

		// create the URI object as a combination of base uri parts
		// and the parts from REQUEST_URI
		$this->requestUri = $this->baseUri()->clone([
			'fragment' => $uri->fragment(),
			'params'   => $uri->params(),
			'path'     => $uri->path(),
			'query'    => $uri->query()
		]);

		// build the full request URL
		$this->requestUrl = $this->requestUri->toString();

		return $this->requestUri;
	}

	/**
	 * Returns the sanitized script path unless in CLI mode
	 */
	protected function detectScriptPath(string|null $scriptPath = null): string
	{
		if ($this->cli === true) {
			return '';
		}

		return $this->sanitizeScriptPath($scriptPath);
	}

	/**
	 * Gets a value from the server environment array
	 *
	 * <code>
	 * $server->get('document_root');
	 * // sample output: /var/www/kirby
	 *
	 * $server->get();
	 * // returns the whole server array
	 * </code>
	 *
	 * @param string|false|null $key The key to look for. Pass `false` or `null`
	 *                               to return the entire server array.
	 * @param mixed $default Optional default value, which should be
	 *                       returned if no element has been found
	 */
	public function get(string|false|null $key = null, $default = null)
	{
		if (is_string($key) === false) {
			return $this->info;
		}

		if (isset($this->info[$key]) === false) {
			$key = strtoupper($key);
		}

		return $this->info[$key] ?? static::sanitize($key, $default);
	}

	/**
	 * Gets a value from the global server environment array
	 * of the current app instance; falls back to `$_SERVER` if
	 * no app instance is running
	 *
	 * @param string|false|null $key The key to look for. Pass `false` or `null`
	 *                               to return the entire server array.
	 * @param mixed $default Optional default value, which should be
	 *                       returned if no element has been found
	 */
	public static function getGlobally(string|false|null $key = null, $default = null)
	{
		// first try the global `Environment` object if the CMS is running
		$app = App::instance(null, true);
		if ($app) {
			return $app->environment()->get($key, $default);
		}

		if (is_string($key) === false) {
			return static::sanitize($_SERVER);
		}

		if (isset($_SERVER[$key]) === false) {
			$key = strtoupper($key);
		}

		return static::sanitize($key, $_SERVER[$key] ?? $default);
	}

	/**
	 * Returns the current host name
	 */
	public function host(): string|null
	{
		return $this->host;
	}

	/**
	 * Returns whether the HTTPS protocol is used
	 */
	public function https(): bool
	{
		return $this->https;
	}

	/**
	 * Returns the sanitized `$_SERVER` array
	 */
	public function info(): array
	{
		return $this->info;
	}

	/**
	 * Returns the server's IP address
	 */
	public function ip(): string|null
	{
		return $this->ip;
	}

	/**
	 * Returns if the server is behind a
	 * reverse proxy server
	 */
	public function isBehindProxy(): bool|null
	{
		return $this->isBehindProxy;
	}

	/**
	 * Checks if this is a local installation;
	 * returns `false` if in doubt
	 */
	public function isLocal(): bool
	{
		// check host
		$host = $this->host();

		if ($host === 'localhost') {
			return true;
		}

		if (Str::endsWith($host, '.local') === true) {
			return true;
		}

		if (Str::endsWith($host, '.test') === true) {
			return true;
		}

		// collect all possible visitor ips
		$ips = [
			$this->get('REMOTE_ADDR'),
			$this->get('HTTP_X_FORWARDED_FOR'),
			$this->get('HTTP_CLIENT_IP')
		];

		if ($this->get('HTTP_FORWARDED')) {
			$ips[] = $this->detectForwarded()['for'];
		}

		// remove duplicates and empty ips
		$ips = array_unique(array_filter($ips));

		// no known ip? Better not assume it's local
		if (empty($ips) === true) {
			return false;
		}

		// stop as soon as a non-local ip is found
		foreach ($ips as $ip) {
			if (in_array($ip, ['::1', '127.0.0.1']) === false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Loads and returns options from environment-specific
	 * PHP files (by host name and server IP address)
	 *
	 * @param string $root Root directory to load configs from
	 */
	public function options(string $root): array
	{
		$configHost = [];
		$configAddr = [];

		$host = $this->host();
		$addr = $this->ip();

		// load the config for the host
		if (empty($host) === false) {
			$configHost = F::load(
				file: $root . '/config.' . $host . '.php',
				fallback: [],
				allowOutput: false
			);
		}

		// load the config for the server IP
		if (empty($addr) === false) {
			$configAddr = F::load(
				file: $root . '/config.' . $addr . '.php',
				fallback: [],
				allowOutput: false
			);
		}

		return array_replace_recursive($configHost, $configAddr);
	}

	/**
	 * Returns the detected path
	 */
	public function path(): string|null
	{
		return $this->path;
	}

	/**
	 * Returns the correct port number
	 */
	public function port(): int|null
	{
		return $this->port;
	}

	/**
	 * Returns an URI object for the requested URL
	 */
	public function requestUri(): Uri
	{
		return $this->requestUri;
	}

	/**
	 * Returns the current URL, including the request path
	 * and query
	 */
	public function requestUrl(): string
	{
		return $this->requestUrl;
	}

	/**
	 * Sanitizes some `$_SERVER` keys
	 */
	public static function sanitize(string|array $key, $value = null)
	{
		if (is_array($key) === true) {
			foreach ($key as $k => $v) {
				$key[$k] = static::sanitize($k, $v);
			}

			return $key;
		}

		return match ($key) {
			'SERVER_ADDR',
			'SERVER_NAME',
			'HTTP_HOST',
			'HTTP_X_FORWARDED_HOST' => static::sanitizeHost($value),

			'SERVER_PORT',
			'HTTP_X_FORWARDED_PORT' => static::sanitizePort($value),

			default => $value
		};
	}

	/**
	 * Sanitizes the given host name
	 */
	protected static function sanitizeHost(string|null $host = null): string|null
	{
		if (empty($host) === true) {
			return null;
		}

		$host = Str::lower($host);
		$host = strip_tags($host);
		$host = basename($host);
		$host = preg_replace('![^\w.:-]+!iu', '', $host);
		$host = htmlspecialchars($host, ENT_COMPAT);
		$host = trim($host, '-');
		$host = trim($host, '.');
		$host = trim($host);

		if ($host === '') {
			return null;
		}

		return $host;
	}

	/**
	 * Sanitizes the given port number
	 */
	protected static function sanitizePort(string|int|false|null $port = null): int|null
	{
		// already fine
		if (is_int($port) === true) {
			return $port;
		}

		// no port given
		if ($port === null || $port === false || $port === '') {
			return null;
		}

		// remove any character that is not an integer
		$port = preg_replace('![^0-9]+!', '', $port);

		// no port
		if ($port === '') {
			return null;
		}

		// convert to integer
		return (int)$port;
	}

	/**
	 * Sanitizes the given script path
	 */
	protected function sanitizeScriptPath(string|null $scriptPath = null): string
	{
		$scriptPath ??= '';
		$scriptPath   = trim($scriptPath);

		// skip all the sanitizing steps if the path is empty
		if ($scriptPath === '') {
			return $scriptPath;
		}

		// replace Windows backslashes
		$scriptPath = str_replace('\\', '/', $scriptPath);
		// remove the script
		$scriptPath = dirname($scriptPath);
		// replace those fucking backslashes again
		$scriptPath = str_replace('\\', '/', $scriptPath);
		// remove the leading and trailing slashes
		$scriptPath = trim($scriptPath, '/');

		// top-level scripts don't have a path
		// and dirname() will return '.'
		if ($scriptPath === '.') {
			return '';
		}

		return $scriptPath;
	}

	/**
	 * Returns the path to the php script
	 * within the document root without the
	 * filename of the script.
	 *
	 * i.e. /subfolder/index.php -> subfolder
	 *
	 * This can be used to build the base baseUrl
	 * for subfolder installations
	 */
	public function scriptPath(): string
	{
		return $this->scriptPath;
	}

	/**
	 * Returns all environment data as array
	 */
	public function toArray(): array
	{
		return [
			'baseUrl'       => $this->baseUrl,
			'host'          => $this->host,
			'https'         => $this->https,
			'info'          => $this->info,
			'ip'            => $this->ip,
			'isBehindProxy' => $this->isBehindProxy,
			'path'          => $this->path,
			'port'          => $this->port,
			'requestUrl'    => $this->requestUrl,
			'scriptPath'    => $this->scriptPath,
		];
	}
}
