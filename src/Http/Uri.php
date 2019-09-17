<?php

namespace Kirby\Http;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Properties;
use Throwable;

/**
 * Uri builder class
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Uri
{
    use Properties;

    /**
     * Cache for the current Uri object
     *
     * @var Uri|null
     */
    public static $current;

    /**
     * The fragment after the hash
     *
     * @var string|false
     */
    protected $fragment;

    /**
     * The host address
     *
     * @var string
     */
    protected $host;

    /**
     * The optional password for basic authentication
     *
     * @var string|false
     */
    protected $password;

    /**
     * The optional list of params
     *
     * @var Params
     */
    protected $params;

    /**
     * The optional path
     *
     * @var Path
     */
    protected $path;

    /**
     * The optional port number
     *
     * @var int|false
     */
    protected $port;

    /**
     * All original properties
     *
     * @var array
     */
    protected $props;

    /**
     * The optional query string without leading ?
     *
     * @var Query
     */
    protected $query;

    /**
     * https or http
     *
     * @var string
     */
    protected $scheme = 'http';

    /**
     * @var boolean
     */
    protected $slash = false;

    /**
     * The optional username for basic authentication
     *
     * @var string|false
     */
    protected $username;

    /**
     * Magic caller to access all properties
     *
     * @param string $property
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $property, array $arguments = [])
    {
        return $this->$property ?? null;
    }

    /**
     * Make sure that cloning also clones
     * the path and query objects
     *
     * @return void
     */
    public function __clone()
    {
        $this->path   = clone $this->path;
        $this->query  = clone $this->query;
        $this->params = clone $this->params;
    }

    /**
     * Creates a new URI object
     *
     * @param array $props
     * @param array $inject
     */
    public function __construct($props = [], array $inject = [])
    {
        if (is_string($props) === true) {
            $props = parse_url($props);
            $props['username'] = $props['user'] ?? null;
            $props['password'] = $props['pass'] ?? null;

            $props = array_merge($props, $inject);
        }

        // parse the path and extract params
        if (empty($props['path']) === false) {
            $extract         = Params::extract($props['path']);
            $props['params'] = $props['params'] ?? $extract['params'];
            $props['path']   = $extract['path'];
            $props['slash']  = $props['slash'] ?? $extract['slash'];
        }

        $this->setProperties($this->props = $props);
    }

    /**
     * Magic getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->$property ?? null;
    }

    /**
     * Magic setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set(string $property, $value)
    {
        if (method_exists($this, 'set' . $property) === true) {
            $this->{'set' . $property}($value);
        }
    }

    /**
     * Converts the URL object to string
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->toString();
        } catch (Throwable $e) {
            return '';
        }
    }

    /**
     * Returns the auth details (username:password)
     *
     * @return string|null
     */
    public function auth(): ?string
    {
        $auth = trim($this->username . ':' . $this->password);
        return $auth !== ':' ? $auth : null;
    }

    /**
     * Returns the base url (scheme + host)
     * without trailing slash
     *
     * @return string|null
     */
    public function base(): ?string
    {
        if ($domain = $this->domain()) {
            return $this->scheme ? $this->scheme . '://' . $domain : $domain;
        }

        return null;
    }

    /**
     * Clones the Uri object and applies optional
     * new props.
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = [])
    {
        $clone = clone $this;

        foreach ($props as $key => $value) {
            $clone->__set($key, $value);
        }

        return $clone;
    }

    /**
     * @param array $props
     * @param boolean $forwarded
     * @return self
     */
    public static function current(array $props = [], bool $forwarded = false)
    {
        if (static::$current !== null) {
            return static::$current;
        }

        $uri = Server::get('REQUEST_URI');
        $uri = preg_replace('!^(http|https)\:\/\/' . Server::get('HTTP_HOST') . '!', '', $uri);
        $uri = parse_url('http://getkirby.com' . $uri);

        $url = new static(array_merge([
            'scheme' => Server::https() === true ? 'https' : 'http',
            'host'   => Server::host($forwarded),
            'port'   => Server::port($forwarded),
            'path'   => $uri['path'] ?? null,
            'query'  => $uri['query'] ?? null,
        ], $props));

        return $url;
    }

    /**
     * Returns the domain without scheme, path or query
     *
     * @return string|null
     */
    public function domain(): ?string
    {
        if (empty($this->host) === true || $this->host === '/') {
            return null;
        }

        $auth   = $this->auth();
        $domain = '';

        if ($auth !== null) {
            $domain .= $auth . '@';
        }

        $domain .= $this->host;

        if ($this->port !== null && in_array($this->port, [80, 443]) === false) {
            $domain .= ':' . $this->port;
        }

        return $domain;
    }

    /**
     * @return boolean
     */
    public function hasFragment(): bool
    {
        return empty($this->fragment) === false;
    }

    /**
     * @return boolean
     */
    public function hasPath(): bool
    {
        return $this->path()->isNotEmpty();
    }

    /**
     * @return boolean
     */
    public function hasQuery(): bool
    {
        return $this->query()->isNotEmpty();
    }

    /**
     * Tries to convert the internationalized host
     * name to the human-readable UTF8 representation
     *
     * @return self
     */
    public function idn()
    {
        if (empty($this->host) === false) {
            $this->setHost(Idn::decode($this->host));
        }
        return $this;
    }

    /**
     * Creates an Uri object for the URL to the index.php
     * or any other executed script.
     *
     * @param array $props
     * @param bool $forwarded
     * @return string
     */
    public static function index(array $props = [], bool $forwarded = false)
    {
        if (Server::cli() === true) {
            $path = null;
        } else {
            $path = Server::get('SCRIPT_NAME');
            // replace Windows backslashes
            $path = str_replace('\\', '/', $path);
            // remove the script
            $path = dirname($path);
            // replace those fucking backslashes again
            $path = str_replace('\\', '/', $path);
            // remove the leading and trailing slashes
            $path = trim($path, '/');
        }

        if ($path === '.') {
            $path = null;
        }

        return static::current(array_merge($props, [
            'path'     => $path,
            'query'    => null,
            'fragment' => null,
        ]), $forwarded);
    }


    /**
     * Checks if the host exists
     *
     * @return bool
     */
    public function isAbsolute(): bool
    {
        return empty($this->host) === false;
    }

    /**
     * @param  string|null $fragment
     * @return self
     */
    public function setFragment(string $fragment = null)
    {
        $this->fragment = $fragment ? ltrim($fragment, '#') : null;
        return $this;
    }

    /**
     * @param  string $host
     * @return self
     */
    public function setHost(string $host = null)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param  Kirby\Http\Params|string|array|null $params
     * @return self
     */
    public function setParams($params = null)
    {
        $this->params = is_a($params, 'Kirby\Http\Params') === true ? $params : new Params($params);
        return $this;
    }

    /**
     * @param  string|null $password
     * @return self
     */
    public function setPassword(string $password = null)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param \Kirby\Http\Path|string|array|null $path
     * @return self
     */
    public function setPath($path = null)
    {
        $this->path = is_a($path, 'Kirby\Http\Path') === true ? $path : new Path($path);
        return $this;
    }

    /**
     * @param int|null $port
     * @return self
     */
    public function setPort(int $port = null)
    {
        if ($port === 0) {
            $port = null;
        }

        if ($port !== null) {
            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException('Invalid port format: ' . $port);
            }
        }

        $this->port = $port;
        return $this;
    }

    /**
     * @param \Kirby\Http\Query|string|array|null $query
     * @return self
     */
    public function setQuery($query = null)
    {
        $this->query = is_a($query, 'Kirby\Http\Query') === true ? $query : new Query($query);
        return $this;
    }

    /**
     * @param  string $scheme
     * @return self
     */
    public function setScheme(string $scheme = null)
    {
        if ($scheme !== null && in_array($scheme, ['http', 'https', 'ftp']) === false) {
            throw new InvalidArgumentException('Invalid URL scheme: ' . $scheme);
        }

        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Set if a trailing slash should be added to
     * the path when the URI is being built
     *
     * @param bool $slash
     * @return self
     */
    public function setSlash(bool $slash = false)
    {
        $this->slash = $slash;
        return $this;
    }

    /**
     * @param  string|null $username
     * @return self
     */
    public function setUsername(string $username = null)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Converts the Url object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->propertyData as $key => $value) {
            $value = $this->$key;

            if (is_object($value) === true) {
                $value = $value->toArray();
            }

            $array[$key] = $value;
        }

        return $array;
    }

    public function toJson(...$arguments): string
    {
        return json_encode($this->toArray(), ...$arguments);
    }

    /**
     * Returns the full URL as string
     *
     * @return string
     */
    public function toString(): string
    {
        $url   = $this->base();
        $slash = true;

        if (empty($url) === true) {
            $url   = '/';
            $slash = false;
        }

        $path = $this->path->toString($slash) . $this->params->toString(true);

        if ($this->slash && $slash === true) {
            $path .= '/';
        }

        $url .= $path;
        $url .= $this->query->toString(true);

        if (empty($this->fragment) === false) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Tries to convert a URL with an internationalized host
     * name to the machine-readable Punycode representation
     *
     * @return self
     */
    public function unIdn()
    {
        if (empty($this->host) === false) {
            $this->setHost(Idn::encode($this->host));
        }
        return $this;
    }
}
