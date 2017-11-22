<?php

namespace Kirby\Http;

use Exception;
use Kirby\Util\Str;

/**
 * Url parser and builder class
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Url
{

    /**
     * The original Url string
     *
     * @var string
     */
    protected $original;

    /**
     * https or http
     *
     * @var string
     */
    protected $scheme = 'http';

    /**
     * The host address
     *
     * @var string
     */
    protected $host;

    /**
     * The optional port number
     *
     * @var int|false
     */
    protected $port = false;

    /**
     * The optional path
     *
     * @var string|false
     */
    protected $path = false;

    /**
     * The optional username for basic authentication
     *
     * @var string|false
     */
    protected $username = false;

    /**
     * The optional password for basic authentication
     *
     * @var string|false
     */
    protected $password = false;

    /**
     * The optional query string without leading ?
     *
     * @var string|false
     */
    protected $query = false;

    /**
     * The fragment after the hash
     *
     * @var string|false
     */
    protected $fragment = false;

    /**
     * Detects the current URL and returns
     * the Url object for it
     *
     * @return Url
     */
    public static function current(): self
    {
        $uri = parse_url(Server::get('REQUEST_URI'));
        $url = new Url;
        $url->build([
            'scheme' => Server::https() === true ? 'https' : 'http',
            'host'   => Server::host(),
            'port'   => Server::port(),
            'path'   => $uri['path'] ?? null,
            'query'  => $uri['query'] ?? null
        ]);

        return $url;
    }

    /**
     * Creates a new URL object
     *
     * @param string|null $original
     */
    public function __construct(string $original = null)
    {
        $this->original = $original;

        if ($original !== null) {
            $parse = parse_url($original);
            if ($parse !== false) {
                $this->build($parse);
            }
        }
    }

    /**
     * Setter for multiple Url components via array
     *
     * @param  array $components
     * @return Url
     */
    public function build(array $components): self
    {
        $this->scheme($components['scheme'] ?? 'http');
        $this->username($components['user'] ?? false);
        $this->password($components['pass'] ?? false);
        $this->host($components['host'] ?? false);
        $this->port($components['port'] ?? false);
        $this->path($components['path'] ?? false);
        $this->query($components['query'] ?? false);
        $this->fragment($components['fragment'] ?? false);
        return $this;
    }

    /**
     * Returns the original Url string,
     * which was given to the constructor
     *
     * @return string
     */
    public function original(): string
    {
        return $this->original;
    }

    /**
     * Setter and getter for the scheme
     *
     * @param  string|null $scheme
     * @return string
     */
    public function scheme(string $scheme = null)
    {
        if ($scheme === null) {
            return $this->scheme;
        }

        if (in_array($scheme, ['http', 'https']) === false) {
            throw new Exception('Invalid URL scheme: ' . $scheme);
        }

        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Setter and getter for the host address
     *
     * @param  string|null  $host
     * @return string|Url
     */
    public function host(string $host = null)
    {
        if ($host === null) {
            return $this->host;
        }

        if (empty($host)) {
            throw new Exception('Invalid host format: ' . $host);
        }

        $this->host = $host;
        return $this;
    }

    /**
     * Setter and getter for the port number
     *
     * @param  int|false|null $port
     * @return int|false|Url
     */
    public function port($port = null)
    {
        if ($port === null) {
            return $this->port;
        }

        if ($port !== false) {
            if (is_int($port) === false || $port < 1 || $port > 65535) {
                throw new Exception('Invalid port format: ' . $port);
            }
        }

        $this->port = $port;
        return $this;
    }

    /**
     * Setter and getter for the username
     *
     * @param  string|false|null $username
     * @return string|false|Url
     */
    public function username($username = null)
    {
        if ($username === null) {
            return $this->username;
        }

        $this->username = $username;
        return $this;
    }

    /**
     * Setter and getter for the password
     *
     * @param  string|false|null $password
     * @return string|false|Url
     */
    public function password($password = null)
    {
        if ($password === null) {
            return $this->password;
        }

        $this->password = $password;
        return $this;
    }

    /**
     * Setter and getter for the path
     *
     * @param  string|false|null $path
     * @return string|false|Url
     */
    public function path($path = null)
    {
        if ($path === null) {
            return $this->path;
        }

        $this->path = $path;
        return $this;
    }

    /**
     * Setter and getter for the query
     *
     * @param  string|false|null $query
     * @return string|false|Url
     */
    public function query($query = null)
    {
        if ($query === null) {
            return $this->query;
        }

        $this->query = ltrim($query, '?');
        return $this;
    }

    /**
     * Setter and getter for the hash
     *
     * @param  string|false|null $fragment
     * @return string|false|Url
     */
    public function fragment($fragment = null)
    {
        if ($fragment === null) {
            return $this->fragment;
        }

        $this->fragment = ltrim($fragment, '#');
        return $this;
    }

    /**
     * Returns the auth details (username:password)
     *
     * @return string|false
     */
    public function auth()
    {
        $auth = $this->username() . ':' . $this->password();
        return $auth !== ':' ? $auth : false;
    }

    /**
     * Returns the base url (scheme + host)
     *
     * @return string
     */
    public function base(): string
    {

        if (empty($this->host) === true) {
            throw new Exception('The host address is missing');
        }

        $auth = $this->auth();
        $base = $this->scheme . '://';

        if ($auth !== false) {
            $base .= $auth . '@';
        }

        $base .= $this->host;

        if ($this->port !== false && in_array($this->port, [80, 443]) === false) {
            $base .= ':' . $this->port;
        }

        return $base;
    }

    /**
     * Shortens the URL
     *
     * @param   int|null $length
     * @return  string
     */
    public function short(int $length = null): string
    {
        $url = clone $this;

        $url->username(false);
        $url->password(false);
        $url->port(false);
        $url->query(false);
        $url->fragment(false);

        $url = str_replace(['http://', 'https://'], '', $url);
        $url = rtrim($url, '/');

        if ($length !== null) {
            $url = Str::short($url, $length);
        }

        return $url;
    }

    /**
     * Converts the Url object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'scheme'   => $this->scheme(),
            'host'     => $this->host(),
            'port'     => $this->port(),
            'path'     => $this->path(),
            'username' => $this->username(),
            'password' => $this->password(),
            'query'    => $this->query(),
            'fragment' => $this->fragment()
        ];
    }

    /**
     * Returns the full URL as string
     *
     * @return string
     */
    public function toString(): string
    {
        $url = $this->base();

        if (empty($this->path) === false) {
            $url .= '/' . ltrim($this->path, '/');
        }

        if (empty($this->query) === false) {
            $url .= '?' . $this->query;
        }

        if (empty($this->fragment) === false) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Converts the URL object to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
