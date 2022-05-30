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
     * @var string
     */
    protected $host;

    /**
     * @var bool
     */
    protected $https;

    /**
     * @var array
     */
    protected $info;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var bool
     */
    protected $isBehindProxy = false;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var int|null
     */
    protected $port;

    /**
     * @var int|null
     */
    protected $portInHost;

    /**
     * @var array
     */
    protected $requestUri;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $scriptPath;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param array|null $options
     * @param array|null $info
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
     * @param array|null $info
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
        // host, port and https from the environment
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
     * Server::HOST_FROM_HOST
     * Server::HOST_FROM_HOST | Server::HOST_ALLOW_EMPTY
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
     * @param bool $insecure
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
     * @param bool|null $override
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
     * @param bool $insecure Include the HTTP_HOST header in the search
     * @return string|null
     */
    protected function detectHost(bool $insecure = false): ?string
    {
        if ($insecure === true) {
            $hosts[] = $this->get('HTTP_HOST');
        }

        $hosts[] = $this->get('SERVER_NAME');
        $hosts[] = $this->get('SERVER_ADDR');

        $hosts = array_filter($hosts);
        $host  = A::first($hosts);
        $parts = $this->detectPortInHost($host);

        $this->portInHost = $parts['port'];

        return $parts['host'];
    }

    /**
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
     * @return bool
     */
    protected function detectIfIsBehindProxy(): bool
    {
        return empty($this->info['HTTP_X_FORWARDED_HOST']) === false;
    }

    /**
     * @return string|null
     */
    protected function detectIp(): ?string
    {
        return $this->get('SERVER_ADDR');
    }

    /**
     * @return string
     * @param string|null $path
     */
    protected function detectPath(?string $path = null): string
    {
        if ($this->cli === true) {
            return '';
        }

        return $path ?? '';
    }

    /**
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
     * @param string|false|null $key The key to look for. Pass false or null to
     *                               return the entire server array.
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
     * Returns the correct host
     *
     * @return string|null
     */
    public function host(): ?string
    {
        return $this->host;
    }

    /**
     * Checks for a https request
     *
     * @return bool
     */
    public function https(): bool
    {
        return $this->https;
    }

    /**
     * Returns the sanitized $_SERVER array
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
     * Checks if the server is behind a
     * proxy server.
     *
     * @return bool
     */
    public function isBehindProxy(): bool
    {
        return $this->isBehindProxy;
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
        $scriptPath = $this->scriptPath();

        return trim(preg_replace('!^' . preg_quote($scriptPath) . '!', '', $requestUri), '/');
    }

    /**
     * Returns an array with path and query
     * from the REQUEST_URI
     *
     * @return array
     */
    public function requestUri(): array
    {
        return $this->requestUri;
    }

    /**
     * Help to sanitize some _SERVER keys
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
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }
}
