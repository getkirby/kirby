<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Http\Server;
use Kirby\Http\Uri;

/**
 * The environment object takes care of
 * secure host and base URL detection, as
 * well as loading the dedicated
 * environment options.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Environment
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @var \Kirby\Http\Uri
     */
    protected $uri;

    /**
     * @param string $root
     * @param bool|string|array|null $allowed
     */
    public function __construct(string $root, $allowed = null)
    {
        $this->root = $root;

        if (is_string($allowed) === true) {
            $this->setupFromString($allowed);
            return;
        }

        if (is_array($allowed) === true) {
            $this->setupFromArray($allowed);
            return;
        }

        if (is_int($allowed) === true) {
            $this->setupFromFlag($allowed);
            return;
        }

        if (is_null($allowed) === true) {
            $this->setupFromFlag(Server::HOST_FROM_SERVER | Server::HOST_ALLOW_EMPTY);
            return;
        }

        throw new InvalidArgumentException('Invalid allow list setup for base URLs');
    }

    /**
     * Throw an exception if the host in the URI
     * object is empty
     *
     * @throws \Kirby\Exception\InvalidArgumentException
     * @return void
     */
    protected function blockEmptyHost(): void
    {
        if (empty($this->uri->host()) === true) {
            throw new InvalidArgumentException('Invalid host setup. The detected host is not allowed.');
        }
    }

    /**
     * Returns the detected host name
     *
     * @return string|null
     */
    public function host(): ?string
    {
        return $this->uri->host();
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
        $addr = Server::address();

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
     * The current URL should be auto detected from a host allowlist
     *
     * @param array $allowed
     * @return \Kirby\Http\Uri
     */
    public function setupFromArray(array $allowed)
    {
        $allowedStrings = [];
        $allowedUris    = [];
        $hosts          = [];

        foreach ($allowed as $url) {
            $allowedUris[]    = $uri = new Uri($url, ['slash' => false]);
            $allowedStrings[] = $uri->toString();
            $hosts[]          = $uri->host();
        }

        // register all allowed hosts
        Server::hosts($hosts);

        // get the index URL, including the subfolder if it exists
        $this->uri = Uri::index();

        // empty URLs don't make sense in an allow list
        $this->blockEmptyHost();

        // validate against the list of allowed base URLs
        if (in_array($this->uri->toString(), $allowedStrings) === false) {
            throw new InvalidArgumentException('The subfolder is not in the allowed base URL list');
        }

        return $this->uri;
    }

    /**
     * The URL option receives a set of Server constant flags
     *
     * Server::HOST_FROM_SERVER
     * Server::HOST_FROM_SERVER | Server::HOST_ALLOW_EMPTY
     * Server::HOST_FROM_HOST
     * Server::HOST_FROM_HOST | Server::HOST_ALLOW_EMPTY
     *
     * @param int $allowed
     * @return \Kirby\Http\Uri
     */
    public function setupFromFlag(int $allowed)
    {
        // allow host detection from host headers
        if ($allowed & Server::HOST_FROM_HEADER) {
            Server::hosts(Server::HOST_FROM_HEADER);

        // detect host only from server name
        } else {
            Server::hosts(Server::HOST_FROM_SERVER);
        }

        // get the base URL
        $this->uri = Uri::index();

        // accept empty hosts
        if ($allowed & Server::HOST_ALLOW_EMPTY) {
            return $this->uri;
        }

        // block empty hosts
        $this->blockEmptyHost();

        return $this->uri;
    }

    /**
     * The current URL is predefined with a single string
     * and not detected automatically.
     *
     * If the url option is relative (i.e. '/' or '/some/subfolder')
     * The host will be empty and that's totally fine.
     * No need to block an empty host here
     *
     * @param string $allowed
     * @return \Kirby\Http\Uri
     */
    public function setupFromString(string $allowed)
    {
        // create the URI object directly from the given option
        // without any form of detection from the server
        $this->uri = new Uri($allowed);

        // only create an allow list from absolute URLs
        // otherwise the default secure host detection
        // behavior will be used
        if (empty($host = $this->uri->host()) === false) {
            Server::hosts([$host]);
        }

        return $this->uri;
    }

    /**
     * Returns the base URL for the environment
     *
     * @return string
     */
    public function url(): string
    {
        return $this->uri;
    }
}
