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

        // empty hosts for relative URLs may be allowed in
        // some scenarios. Otherwise an exception is thrown further down.
        $allowEmptyHost = false;

        // This is the default. The current URL should be detected via the server name.
        // Empty hosts are allowed. Should lead to no breaking changes with previous setups.
        if ($allowed === null) {
            $allowEmptyHost = true;

            Server::hosts([]);
            $this->uri = Uri::index();

        // The current URL should be detected via possibly insecure HOST headers
        } elseif ($allowed === true) {
            Server::hosts(['*']);
            $this->uri = Uri::index();

        // the current URL should be detected via the server name. no empty hosts allowed
        } elseif ($allowed === false) {
            Server::hosts([]);
            $this->uri = Uri::index();

        // the current URL is predefined with a single string
        // and not detected automatically
        } elseif (is_string($allowed) === true) {
            // if the url option is relative (i.e. '/' or '/some/subfolder')
            // the host will be empty and that's totally fine.
            $allowEmptyHost = true;

            // create the URI object directly from the given option
            // without any form of detection from the server
            $this->uri = new Uri($allowed);

            // only create an allow list from absolute URLs
            // otherwise the default secure host detection
            // behavior will be used
            if (empty($host = $this->uri->host()) === false) {
                Server::hosts([$host]);
            }

            // the current URL should be auto detected from a host allowlist
        } elseif (is_array($allowed) === true) {
            foreach ($allowed as $url) {
                $host = (new Uri($url))->host();
                $hosts[]    = $host;
            }

            Server::hosts($hosts);
            $this->uri = Uri::index();
        } else {
            throw new InvalidArgumentException('Invalid allow list setup for base URLs');
        }

        // check for empty hosts
        if ($allowEmptyHost === false && empty($this->uri->host()) === true) {
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
     * Returns the base URL for the environment
     *
     * @return string
     */
    public function url(): string
    {
        return $this->uri;
    }
}
