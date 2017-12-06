<?php

namespace Kirby\Cms;

use Exception;

/**
 * The universal store object is used
 * to register actions for the site, pages, files and
 * users that can later be overwritten by plugins.
 *
 * The functional approach also makes it easy to test
 * those actions isolated without binding them too close
 * to the main objects
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Store
{

    /**
     * An optional object can be bound to each
     * store commit. Normally the App object would
     * be bound to the store for easier access
     * within commit functions with $this.
     *
     * @var Object
     */
    protected $bind;

    /**
     * All registered store actions.
     * Each action has a name/key and a callback
     * function with any number of arguments.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Creates the store object with all the actions
     *
     * @param array $actions
     * @param Object $bind
     */
    public function __construct(array $actions = [], Object $bind = null)
    {
        $this->bind    = $bind ?? $this;
        $this->actions = $actions;
    }

    /**
     * Commits a store action if available
     *
     * ```php
     * $store = new Store([
     *     'say' => function ($message) {
     *          echo $message;
     *      }
     * ]);
     * $store->commit('say', 'hello');
     * ```
     *
     * @param  string $action
     * @param  mixed ...$arguments
     * @return mixed
     */
    public function commit(string $action, ...$arguments)
    {
        if (isset($this->actions[$action]) === false) {
            throw new Exception(sprintf('Invalid store action: "%s"', $action));
        }

        return $this->actions[$action]->call($this->bind, ...$arguments);
    }

}
