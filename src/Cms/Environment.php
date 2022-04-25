<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Http\Environment as BaseEnvironment;
use Kirby\Http\Server;

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
class Environment extends BaseEnvironment
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @param array|null $options
     * @param array|null $info
     */
    public function __construct(array $options = null, array $info = null)
    {
        $options = array_merge([
            'cli'     => null,
            'root'    => null,
            'allowed' => null,
        ], $options ?? []);

        $this->root = $options['root'];

        // keep Server flags compatible for now
        if (is_int($options['allowed']) === true) {
            $options['allowed'] = $this->allowedFromFlag();
        }

        parent::__construct($options, $info);
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
    public function allowedFromFlag(int $flags): ?string
    {
        // allow host detection from host headers
        if ($flags & Server::HOST_FROM_HEADER) {
            return '*';

        }

        // detect host only from server name
        return null;
    }
}
