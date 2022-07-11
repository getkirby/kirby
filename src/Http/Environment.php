<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Cms\Helpers;
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
	 *
	 * @var \Kirby\Http\Uri
	 */
	protected $baseUri;

	/**
	 * Full base URL
	 *
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * Whether the request is being served by the CLI
	 *
	 * @var bool
	 */
	protected $cli;

	/**
	 * Current host name
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Whether the HTTPS protocol is used
	 *
	 * @var bool
	 */
	protected $https;

	/**
	 * Sanitized `$_SERVER` data
	 *
	 * @var array
	 */
	protected $info;

	/**
	 * Current server's IP address
	 *
	 * @var string
	 */
	protected $ip;

	/**
	 * Whether the site is behind a reverse proxy;
	 * `null` if not known (fixed allowed URL setup)
	 *
	 * @var bool|null
	 */
	protected $isBehindProxy;

	/**
	 * URI path to the base
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Port number in the site URL
	 *
	 * @var int|null
	 */
	protected $port;

	/**
	 * Intermediary value of the port
	 * extracted from the host name
	 *
	 * @var int|null
	 */
	protected $portInHost;

	/**
	 * Uri object for the full request URI.
	 * It is a combination of the base URL and `REQUEST_URI`
	 *
	 * @var \Kirby\Http\Uri
	 */
	protected $requestUri;

	/**
	 * Full request URL
	 *
	 * @var string
	 */
	protected $requestUrl;

	/**
	 * Path to the php script within the
	 * document root without the
	 * filename of the script
	 *
	 * @var string
	 */
	protected $scriptPath;

	/**
	 * Class constructor
	 *
	 * @param array|null $options
	 * @param array|null $info Optional override for `$_SERVER`
	 */
	public function __construct(?array $options = null, ?array $info = null)
	{
		$this->detect($options, $info);
	}

	/**
	 * Returns the server's IP address
	 *
	 * @see static::ip
	 * @return string|null
	 */
	public function address(): ?string
	{
		return $this->ip();
	}

	/**
	 * Returns the full base URL object
	 *
	 * @return \Kirby\Http\Uri
	 */
	public function baseUri()
	{
		return $this->baseUri;
	}

	/**
	 * Returns the full base URL
	 *
	 * @return string
	 */
	public function baseUrl(): string
	{
		return $this->baseUrl;
	}

	/**
	 * Checks if the request is being served by the CLI
	 *
	 * @return bool
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
	 * @param array|null $options
	 * @param array|null $info Optional override for `$_SERVER`
	 * @return array
	 */
	public function detect(array $options = null, array $info = null): array
	{
		$info ??= $_SERVER;
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

		// keep Server flags compatible for now
		// TODO: remove in 3.8.0
		// @codeCoverageIgnoreStart
		if (is_int($options['allowed']) === true) {
			Helpers::deprecated('
                Using `Server::` constants for the `allowed` option has been deprecated and support will be removed in 3.8.0. Use one of the following instead: a single fixed URL, an array of allowed URLs to match dynamically, `*` wildcard to match dynamically even from insecure headers, or `true` to match automtically from safe server variables.
            ');

			$options['allowed'] = $this->detectAllowedFromFlag($options['allowed']);
		}
		// @codeCoverageIgnoreEnd

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
	 *
	 * @param array|string $allowed
	 * @return void
	 */
	protected function detectAllowed($allowed): void
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
	 * The URL option receives a set of Server constant flags
	 *
	 * Server::HOST_FROM_SERVER
	 * Server::HOST_FROM_SERVER | Server::HOST_ALLOW_EMPTY
	 * Server::HOST_FROM_HEADER
	 * Server::HOST_FROM_HEADER | Server::HOST_ALLOW_EMPTY
	 * @todo Remove in 3.8.0
	 *
	 * @param int $flags
	 * @return string|null
	 */
	protected function detectAllowedFromFlag(int $flags): ?string
	{
		// allow host detection from host headers
		if ($flags & Server::HOST_FROM_HEADER) {
			return '*';
		}

		// detect host only from server name
		return null;
	}

	/**
	 * Sets the host name, port and protocol without configuration
	 *
	 * @param bool $insecure Include the `Host`, `Forwarded` and `X-Forwarded-*` headers in the search
	 * @return void
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
	 *
	 * @return \Kirby\Http\Uri
	 */
	protected function detectBaseUri()
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
	 * @return bool
	 */
	protected function detectCli(?bool $override = null): bool
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
	 *
	 * @return array
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

			if ($data['port'] === null && $data['https'] === true) {
				$data['port'] = 443;
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
	 *
	 * @return string|null
	 */
	protected function detectForwardedHost(): ?string
	{
		$host  = $this->get('HTTP_X_FORWARDED_HOST');
		$parts = $this->detectPortInHost($host);

		$this->portInHost = $parts['port'];

		return $parts['host'];
	}

	/**
	 * Detects the protocol of the reverse proxy from the
	 * `X-Forwarded-SSL` or `X-Forwarded-Proto` header
	 *
	 * @return bool
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
	 * @return int|null
	 */
	protected function detectForwardedPort(bool $https): ?int
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
	 * @return string|null
	 */
	protected function detectHost(bool $insecure = false): ?string
	{
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
	 *
	 * @return bool
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
	 *
	 * @param string|bool|null|int $value
	 * @return bool
	 */
	protected function detectHttpsOn($value): bool
	{
		// off can mean many things :)
		$off = ['off', null, '', 0, '0', false, 'false', -1, '-1'];

		return in_array($value, $off, true) === false;
	}

	/**
	 * Detects the HTTPS status from a `X-Forwarded-Proto` string
	 *
	 * @param string|null $protocol
	 * @return bool
	 */
	protected function detectHttpsProtocol(?string $protocol = null): bool
	{
		if ($protocol === null) {
			return false;
		}

		return in_array(strtolower($protocol), ['https', 'https, http']) === true;
	}

	/**
	 * Detects the server's IP address
	 *
	 * @return string|null
	 */
	protected function detectIp(): ?string
	{
		return $this->get('SERVER_ADDR');
	}

	/**
	 * Detects the URI path unless in CLI mode
	 *
	 * @param string|null $path
	 * @return string
	 */
	protected function detectPath(?string $path = null): string
	{
		if ($this->cli === true) {
			return '';
		}

		return $path ?? '';
	}

	/**
	 * Detects the port from various sources
	 *
	 * @return int|null
	 */
	protected function detectPort(): ?int
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
	 *
	 * @param string|null $host
	 * @return array
	 */
	protected function detectPortInHost(?string $host = null): array
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
	 *
	 * @param string|null $requestUri
	 * @return \Kirby\Http\Uri
	 */
	protected function detectRequestUri(?string $requestUri = null)
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
	 *
	 * @param string|null $scriptPath
	 * @return string
	 */
	protected function detectScriptPath(?string $scriptPath = null): string
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
	 * @return mixed
	 */
	public function get($key = null, $default = null)
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
	 * @return mixed
	 */
	public static function getGlobally($key = null, $default = null)
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
	 *
	 * @return string|null
	 */
	public function host(): ?string
	{
		return $this->host;
	}

	/**
	 * Returns whether the HTTPS protocol is used
	 *
	 * @return bool
	 */
	public function https(): bool
	{
		return $this->https;
	}

	/**
	 * Returns the sanitized `$_SERVER` array
	 *
	 * @return array
	 */
	public function info(): array
	{
		return $this->info;
	}

	/**
	 * Returns the server's IP address
	 *
	 * @return string|null
	 */
	public function ip(): ?string
	{
		return $this->ip;
	}

	/**
	 * Returns if the server is behind a
	 * reverse proxy server
	 *
	 * @return bool|null
	 */
	public function isBehindProxy(): ?bool
	{
		return $this->isBehindProxy;
	}

	/**
	 * Checks if this is a local installation;
	 * returns `false` if in doubt
	 *
	 * @return bool
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
	 * @return array
	 */
	public function options(string $root): array
	{
		$configHost = [];
		$configAddr = [];

		$host = $this->host();
		$addr = $this->ip();

		// load the config for the host
		if (empty($host) === false) {
			$configHost = F::load($root . '/config.' . $host . '.php', []);
		}

		// load the config for the server IP
		if (empty($addr) === false) {
			$configAddr = F::load($root . '/config.' . $addr . '.php', []);
		}

		return array_replace_recursive($configHost, $configAddr);
	}

	/**
	 * Returns the detected path
	 *
	 * @return string|null
	 */
	public function path(): ?string
	{
		return $this->path;
	}

	/**
	 * Returns the correct port number
	 *
	 * @return int|null
	 */
	public function port(): ?int
	{
		return $this->port;
	}

	/**
	 * Returns an URI object for the requested URL
	 *
	 * @return \Kirby\Http\Uri
	 */
	public function requestUri()
	{
		return $this->requestUri;
	}

	/**
	 * Returns the current URL, including the request path
	 * and query
	 *
	 * @return string
	 */
	public function requestUrl(): string
	{
		return $this->requestUrl;
	}

	/**
	 * Sanitizes some `$_SERVER` keys
	 *
	 * @param string|array $key
	 * @param mixed $value
	 * @return mixed
	 */
	public static function sanitize($key, $value = null)
	{
		if (is_array($key) === true) {
			foreach ($key as $k => $v) {
				$key[$k] = static::sanitize($k, $v);
			}

			return $key;
		}

		switch ($key) {
			case 'SERVER_ADDR':
			case 'SERVER_NAME':
			case 'HTTP_HOST':
			case 'HTTP_X_FORWARDED_HOST':
				return static::sanitizeHost($value);
			case 'SERVER_PORT':
			case 'HTTP_X_FORWARDED_PORT':
				return static::sanitizePort($value);
			default:
				return $value;
		}
	}

	/**
	 * Sanitizes the given host name
	 *
	 * @param string|null $host
	 * @return string|null
	 */
	protected static function sanitizeHost(?string $host = null): ?string
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
	 *
	 * @param string|int|null $port
	 * @return int|null
	 */
	protected static function sanitizePort($port = null): ?int
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
		$port = preg_replace('![^0-9]+!', '', (string)($port ?? ''));

		// no port
		if ($port === '') {
			return null;
		}

		// convert to integer
		return (int)$port;
	}

	/**
	 * Sanitizes the given script path
	 *
	 * @param string|null $scriptPath
	 * @return string
	 */
	protected function sanitizeScriptPath(?string $scriptPath = null): string
	{
		$scriptPath ??= '';
		$scriptPath = trim($scriptPath);

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
	 *
	 * @return string
	 */
	public function scriptPath(): string
	{
		return $this->scriptPath;
	}

	/**
	 * Returns all environment data as array
	 *
	 * @return array
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
