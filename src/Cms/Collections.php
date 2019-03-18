<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Controller;

/**
 * Manages and loads all collections
 * in site/collections, which can then
 * be reused in controllers, templates, etc
 *
 * This class is mainly used in the `$kirby->collection()`
 * method to provide easy access to registered collections
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Collections
{

    /**
     * Each collection is cached once it
     * has been called, to avoid further
     * processing on sequential calls to
     * the same collection.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Store of all collections
     *
     * @var array
     */
    protected $collections = [];

    /**
     * Magic caller to enable something like
     * `$collections->myCollection()`
     *
     * @param  string $name
     * @param  array $arguments
     * @return Collection|null
     */
    public function __call(string $name, array $arguments = [])
    {
        return $this->get($name, ...$arguments);
    }

    /**
     * Loads a collection by name if registered
     *
     * @param string $name
     * @param array $data
     * @return Collection|null
     */
    public function get(string $name, array $data = [])
    {
        // if not yet loaded
        if (isset($this->collections[$name]) === false) {
            $this->collections[$name] = $this->load($name);
        }

        // if not yet cached
        if (isset($this->cache[$name]) === false) {
            $controller = new Controller($this->collections[$name]);
            $this->cache[$name] = $controller->call(null, $data);
        }

        // return cloned object
        if (is_object($this->cache[$name]) === true) {
            return clone $this->cache[$name];
        }

        return $this->cache[$name];
    }

    /**
     * Checks if a collection exists
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        if (isset($this->collections[$name]) === true) {
            return true;
        }

        try {
            $this->load($name);
            return true;
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * Loads collection from php file in a
     * given directory or from plugin extension.
     *
     * @param  string $name
     * @return mixed
     */
    public function load(string $name)
    {
        $kirby = App::instance();

        // first check for collection file
        $file = $kirby->root('collections') . '/' . $name . '.php';

        if (file_exists($file)) {
            $collection = require $file;

            if (is_a($collection, 'Closure')) {
                return $collection;
            }
        }

        // fallback to collections from plugins
        $collections = $kirby->extensions('collections');

        if (isset($collections[$name]) === true) {
            return $collections[$name];
        }

        throw new NotFoundException('The collection cannot be found');
    }
}
