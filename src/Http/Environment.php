<?php

namespace Kirby\Http;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;

/**
 * A set of methods that make it more convenient to get variables
 * from the global server array
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
     * @var boolean
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
     * @var boolean
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
     * @var array
     */
    protected $requestUri;

    /**
     * @var string
     */
    protected $scriptPath;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param array $options
     * @param array|null $info
     */
    public function __construct(array $options = [], ?array $info = null)
    {
        $defaults = [
            'cli'      => null,
            'insecure' => false,
            'allowed'  => []
        ];

        $this->options = array_merge($defaults, $options ?? []);

        $this->detect($info ?? $_SERVER);
    }

    /**
     * Returns the server's IP address
     *
     * @see static::ip
     * @return string
     */
    public function address(): string
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
     * @param array $info
     * @return array
     */
    public function detect(array $info): array
    {
        $this->info          = $this->sanitize($info);
        $this->cli           = $this->detectCli();
        $this->ip            = $this->detectIp();
        $this->isBehindProxy = $this->detectIfIsBehindProxy();
        $this->requestUri    = $this->detectRequestUri($this->get('REQUEST_URI'));
        $this->scriptPath    = $this->detectScriptPath($this->get('SCRIPT_NAME'));
        $this->path          = $this->scriptPath;

        // cli
        if ($this->cli === true) {
            $this->host  = null;
            $this->port  = null;
            $this->https = false;
            $this->path  = null;

        // fixed environments
        } else if (empty($this->options['allowed']) === false) {
            $this->detectAllowed($this->options['allowed']);

        // auto-detection
        } else {
            $this->detectAuto($this->options['insecure']);
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
    public function detectAllowed($allowed): void
    {
        $allowed = A::wrap($allowed);

        // with a single allowed URL, the entire
        // environment will be based on that
        if (count($allowed) === 1) {
            $url = A::first($allowed);
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
            $uri = new Uri($url, ['slash' => false]);

            if ($uri->toString() === $this->url) {
                return;
            }
        }

        throw new InvalidArgumentException('The environment setup is now allowed');
    }

    /**
     * @param boolean $insecure
     * @return void
     */
    public function detectAuto(bool $insecure = false): void
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
     * @return boolean
     */
    public function detectCli(): bool
    {
        if ($this->options['cli'] === false) {
            return false;
        }

        if ($this->options['cli'] === true) {
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

    public function detectForwardedHost(): ?string
    {
        return $this->get('HTTP_X_FORWARDED_HOST') ?? $this->detectHost(true);
    }

    /**
     * @return boolean
     */
    public function detectForwardedHttps(): bool
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
     * @return integer|null
     */
    public function detectForwardedPort(): ?int
    {
        // based on forwarded port
        $port = $this->get('HTTP_X_FORWARDED_PORT');

        if (is_int($port) === true) {
            return $port;
        }

        // based on forwarded host
        $port = $this->detectPortFromHost($this->host);

        if (is_int($port) === true) {
            return $port;
        }

        // fall back to local setup
        return $this->detectPort();
    }

    /**
     * @param boolean $insecure Include the HTTP_HOST header in the search
     * @return string|null
     */
    public function detectHost(bool $insecure = false): ?string
    {
        if ($insecure === true) {
            $hosts[] = $this->get('HTTP_HOST');
        }

        $hosts[] = $this->get('SERVER_NAME');
        $hosts[] = $this->get('SERVER_ADDR');

        $hosts = array_filter($hosts);

        return A::first($hosts);
    }

    /**
     * @return boolean
     */
    public function detectHttps(): bool
    {
        if ($this->detectHttpsOn($this->get('HTTPS')) === true) {
            return true;
        }

        return false;
    }

    /**
     * @param string|boolean|null|int $value
     * @return boolean
     */
    public function detectHttpsOn($value): bool
    {
        // off can mean many things :)
        $off = ['off', null, '', 0, '0', false, 'false', -1, '-1'];

        return in_array($value, $off, true) === false;
    }

    /**
     * @param string|null $protocol
     * @return bool
     */
    public function detectHttpsProtocol(?string $protocol = null): bool
    {
        if ($protocol === null) {
            return false;
        }

        return in_array(strtolower($protocol), ['https', 'https, http']) === true;
    }

    /**
     * @return boolean
     */
    public function detectIfIsBehindProxy(): bool
    {
        if ($this->cli === true) {
            return false;
        }

        return empty($this->info['HTTP_X_FORWARDED_HOST']) === false;
    }

    /**
     * @return string|null
     */
    public function detectIp(): ?string
    {
        return $this->get('SERVER_ADDR');
    }

    /**
     * @return integer|null
     */
    public function detectPort(): ?int
    {
        // based on server port
        $port = $this->get('SERVER_PORT');

        if (is_int($port) === true) {
            return $port;
        }

        // based on the detected host
        $port = $this->detectPortFromHost($this->host);

        if (is_int($port) === true) {
            return $port;
        }

        // based on the detected https state
        if ($this->https === true) {
            return 443;
        }

        return null;
    }

    /**
     * @param string|null $host
     * @return integer|null
     */
    public function detectPortFromHost(?string $host = null): ?int
    {
        if (empty($host) === true) {
            return null;
        }

        $port = parse_url($host, PHP_URL_PORT);

        if (is_int($port) === true) {
            return $port;
        }

        return null;
    }


    /**
     * @param string|null $requestUri
     * @return array
     */
    public function detectRequestUri(?string $requestUri = null): array
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
    public function detectScriptPath(?string $scriptPath = null): string
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
    public function detectUrl(): string
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
        if ($key === null) {
            return $this->info;
        }

        $key = strtoupper($key);

        return $this->info[$key] ?? $this->sanitize($key, $default);
    }

    /**
     * Returns the correct host
     *
     * @return string
     */
    public function host(): string
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
     * Checks for allowed host names
     *
     * @param string $host
     * @return bool
     */
    public function isAllowedHost(string $host): bool
    {
        if (empty($this->hosts) === true) {
            return true;
        }

        foreach ($this->hosts as $pattern) {
            if (empty($pattern) === true) {
                continue;
            }

            if (fnmatch($pattern, $host) === true) {
                return true;
            }
        }

        return false;
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
     * Returns the server's IP address
      *
     * @return string
     */
    public function ip(): string
    {
        return $this->ip;
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
     * @return integer|null
     */
    public function port(): ?int
    {
        return $this->port;
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
    public function sanitizeHost(?string $host = null): ?string
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
     * @return integer|null
     */
    public function sanitizePort($port = null): ?int
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
    public function sanitizeScriptPath(?string $scriptPath = null): string
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
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }


}
