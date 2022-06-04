<?php

namespace Kirby\Http;

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
     * Whether the site is behind a reverse proxy
     *
     * @var bool
     */
    protected $isBehindProxy = false;

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
     * Array with path and query
     * from the `REQUEST_URI`
     *
     * @var array
     */
    protected $requestUri;

    /**
     * Root directory for environment configs
     *
     * @var string
     */
    protected $root;

    /**
     * Path to the php script within the
     * document root without the
     * filename of the script
     *
     * @var string
     */
    protected $scriptPath;

    /**
     * Full base URL
     *
     * @var string
     */
    protected $url;

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
            'root'    => null,
            'allowed' => null
        ], $options ?? []);

        $this->info          = $this->sanitize($info);
        $this->cli           = $this->detectCli($options['cli']);
        $this->ip            = $this->detectIp();
        $this->host          = null;
        $this->https         = false;
        $this->isBehindProxy = $this->detectIfIsBehindProxy();
        $this->requestUri    = $this->detectRequestUri($this->get('REQUEST_URI'));
        $this->root          = $options['root'];
        $this->scriptPath    = $this->detectScriptPath($this->get('SCRIPT_NAME'));
        $this->path          = $this->detectPath($this->scriptPath);
        $this->port          = null;

        // keep Server flags compatible for now
        // TODO: remove in 3.8.0
        if (is_int($options['allowed']) === true) {
            Helpers::deprecated('
                Using `Server::` constants for the `url` option has been deprecated and support will be removed in 3.8.0. Use one of the following instead: a single fixed URL, an array of allowed URLs to match dynamically, `*` wildcard to match dynamically even from insecure headers, or `true` to match automtically from safe server variables.
            ');

            $options['allowed'] = $this->detectAllowedFromFlag($options['allowed']);
        }

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

        // build the URL based on the detected params
        $this->url = $this->detectUrl();

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
            $url = A::first($allowed);

            if (is_string($url) === false) {
                throw new InvalidArgumentException('Invalid allow list setup for base URLs');
            }

            $uri = new Uri($url, ['slash' => false]);

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

        // build the url based on the detected environment
        // to compare it against what is allowed
        $this->url = $this->detectUrl();

        foreach ($allowed as $url) {
            // skip invalid URLs
            if (is_string($url) === false) {
                continue;
            }

            $uri = new Uri($url, ['slash' => false]);

            if ($uri->toString() === $this->url) {
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
     * @param bool $insecure Include the `Host` and `X-Forwarded-*` headers in the search
     * @return void
     */
    protected function detectAuto(bool $insecure = false): void
    {
        // proxy server setup
        if ($this->isBehindProxy === true && $insecure === true) {
            $this->host  = $this->detectForwardedHost();
            $this->https = $this->detectForwardedHttps();
            $this->port  = $this->detectForwardedPort();

        // local server setup
        } else {
            $this->host  = $this->detectHost($insecure);
            $this->https = $this->detectHttps();
            $this->port  = $this->detectPort();
        }
    }

    /**
     * Detects if the request is served by the CLI
     *
     * @param bool|null $override Set to a boolean to override detection (for testing)
     * @return bool
     */
    protected function detectCli(?bool $override = null): bool
    {
        if ($override === false) {
            return false;
        }

        if ($override === true) {
            return true;
        }

        if (defined('STDIN') === true) {
            return true;
        }

        $term = getenv('TERM');

        if (substr(PHP_SAPI, 0, 3) === 'cgi' && $term && $term !== 'unknown') {
            return true;
        }

        return false;
    }

    /**
     * Detects the host name of the reverse proxy
     *
     * @return string|null
     */
    protected function detectForwardedHost(): ?string
    {
        $host = $this->get('HTTP_X_FORWARDED_HOST');

        if (empty($host) === false) {
            $parts = $this->detectPortInHost($host);

            $this->portInHost = $parts['port'];

            return $parts['host'];
        }

        return $this->detectHost(true);
    }

    /**
     * Detects the protocol of the reverse proxy
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

        // fall back to local setup
        return $this->detectHttps();
    }

    /**
     * Detects the port of the reverse proxy
     *
     * @return int|null
     */
    protected function detectForwardedPort(): ?int
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

        // fall back to local setup
        return $this->detectPort();
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
     * Checks if a reverse proxy is active
     *
     * @return bool
     */
    protected function detectIfIsBehindProxy(): bool
    {
        return empty($this->info['HTTP_X_FORWARDED_HOST']) === false;
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
            'port' => $this->sanitizePort($parts[1] ?? null),
        ];
    }

    /**
     * Splits any URI into path and query
     *
     * @param string|null $requestUri
     * @return array
     */
    protected function detectRequestUri(?string $requestUri = null): array
    {
        if (Url::isAbsolute($requestUri) === true) {
            $requestUri = parse_url($requestUri);
        } else {
            // the fake domain is needed to make sure the URL parsing is
            // always correct. Even if there's a colon in the path for params
            $requestUri = parse_url('http://getkirby.com' . $requestUri);
        }

        return [
            'path'  => $requestUri['path']  ?? null,
            'query' => $requestUri['query'] ?? null,
        ];
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
     * Builds the base URL based on the
     * given environment params
     *
     * @return string
     */
    protected function detectUrl(): string
    {
        $uri = new Uri([
            'host'   => $this->host,
            'path'   => $this->path,
            'port'   => $this->port,
            'scheme' => $this->https ? 'https' : 'http',
        ]);

        return $uri->toString();
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

        return $this->info[$key] ?? $this->sanitize($key, $default);
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
     * @return bool
     */
    public function isBehindProxy(): bool
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
     * Loads and returns the environment options
     *
     * @return array
     */
    public function options(): array
    {
        $configHost = [];
        $configAddr = [];

        $host = $this->host();
        $addr = $this->ip();

        // load the config for the host
        if (empty($host) === false) {
            $configHost = F::load($this->root . '/config.' . $host . '.php', []);
        }

        // load the config for the server IP
        if (empty($addr) === false) {
            $configAddr = F::load($this->root . '/config.' . $addr . '.php', []);
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
     * Returns the path after the script path
     * This is the one used for routing
     *
     * @return string
     */
    public function requestRoute(): string
    {
        $requestUri = trim($this->requestUri()['path'] ?? '', '/');
        $requestUri = str_replace('//', '/', $requestUri);

        // remove the script path only from the beginning of the URI
        $scriptPath = $this->scriptPath();

        return trim(preg_replace('!^' . preg_quote($scriptPath) . '!', '', $requestUri), '/');
    }

    /**
     * Returns an array with path and query
     * from the `REQUEST_URI`
     *
     * @return array
     */
    public function requestUri(): array
    {
        return $this->requestUri;
    }

    /**
     * Sanitizes some `$_SERVER` keys
     *
     * @param string|array $key
     * @param mixed $value
     * @return mixed
     */
    public function sanitize($key, $value = null)
    {
        if (is_array($key) === true) {
            foreach ($key as $k => $v) {
                $key[$k] = $this->sanitize($k, $v);
            }

            return $key;
        }

        switch ($key) {
            case 'SERVER_ADDR':
            case 'SERVER_NAME':
            case 'HTTP_HOST':
            case 'HTTP_X_FORWARDED_HOST':
                return $this->sanitizeHost($value);
            case 'SERVER_PORT':
            case 'HTTP_X_FORWARDED_PORT':
                return $this->sanitizePort($value);
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
    protected function sanitizeHost(?string $host = null): ?string
    {
        if (empty($host) === true) {
            return null;
        }

        $host = strtolower($host);
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
    protected function sanitizePort($port = null): ?int
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
     * This can be used to build the base url
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
            'host'          => $this->host,
            'https'         => $this->https,
            'info'          => $this->info,
            'ip'            => $this->ip,
            'isBehindProxy' => $this->isBehindProxy,
            'path'          => $this->path,
            'port'          => $this->port,
            'requestUri'    => $this->requestUri,
            'scriptPath'    => $this->scriptPath,
            'url'           => $this->url
        ];
    }

    /**
     * Returns the full base URL
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }
}
