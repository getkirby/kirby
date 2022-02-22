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

        // the current URL should be detected via possibly insecure HOST headers
        if ($allowed === true || $allowed === null) {
            Server::hosts(['*']);
            $this->uri = Uri::index();

        // the current URL should be detected via the server name
        } elseif ($allowed === false) {
            Server::hosts([]);
            $this->uri = Uri::index();

        // the current URL is predefined and not detected automatically
        } elseif (is_string($allowed) === true) {
            $this->uri = new Uri($allowed);
            Server::hosts([$this->uri->host()]);

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
