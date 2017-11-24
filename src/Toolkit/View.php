<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * Simple PHP view engine
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class View
{

    /**
     * Store to set global data for all
     * following views
     *
     * @var array
     */
    protected static $globals = [];

    /**
     * The absolute path to the view file
     *
     * @var string
     */
    protected $file;

    /**
     * The view data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Setter and getter for global view variables
     *
     * @param  array|null $globals
     * @return array
     */
    public static function globals(array $globals = null): array
    {
        if ($globals === null) {
            return static::$globals;
        }

        return static::$globals = $globals;
    }

    /**
     * Creates a new view object
     *
     * @param string $file
     * @param array  $data
     */
    public function __construct(string $file, array $data = [])
    {
        if (!file_exists($file)) {
            throw new Exception('The view does not exist: ' . $file);
        }

        $this->file = $file;
        $this->data = $data;
    }

    /**
     * Returns the view file
     *
     * @return string
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * Returns the view's data array
     * without globals.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Renders the view
     *
     * @return string
     */
    public function toString(): string
    {
        ob_start();
        $array = array_merge(static::$globals, $this->data);
        extract($array);
        require($this->file);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Magic string converter to enable
     * converting view objects to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
