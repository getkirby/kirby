<?php

namespace Kirby\Plugin;

/**
 * Autoloader 
 */

use Closure;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use Throwable;

class Autoloader
{

    public array $extends = [];

    /**
     * @param string $name Plugin name
     * @param string $root The root path of the plugin
     * @param array $data Predefined extension data to extend
     * @return void|$this 
     */
    public function __construct(
        public string $name,
        public string $root,
        public array $data = []
    ) {

        //Load classes before everything else
        $classfolder = $root . '/classes/';
        if (Dir::exists($classfolder)) {
            $this->_classes($classfolder);
        }

        foreach (Dir::dirs($this->root) as $dir) {
            if (method_exists($this, $dir)) {
                $this->$dir($this->root . '/' . $dir . '/');
            }
        }
    }

    /**
     * Walk throuh the given path
     * @param string $root Absolute path to walk
     * @param Closure $fnc Callback for each file
     * @return void 
     */
    public function _dirWalker(string $root, Closure $fnc)
    {

        foreach (Dir::index($root, true) as $path) {

            //Check if the path is active
            if (Str::contains($path, '/_')) {
                continue;
            }

            $file = $root . $path;
            if (F::exists($file)) {
                $dirname = F::dirname($path);
                $dirname = $dirname === '.' ? '' : $dirname . '/';
                $fnc($dirname . F::name($path), $file);
            }
        }
    }

    /**
     * Read file by given name and check if available
     * @param string $file 
     * @return array 
     * @throws Throwable 
     * @throws InvalidArgumentException 
     * @throws Exception 
     */
    public function _read(string $file): array
    {

        $content = Data::read($file);

        //Value may be a closure
        if (is_array($content) && count($content) === 0) {
            $name = F::relativepath($file, $this->root);
            throw new Exception("The content for '$name' cannot be resolved by the autoloader");
        }

        return $content;
    }

    /**
     * Autoload autoload
     * @param string $root 
     * @return void 
     */
    public function autoload(string $root)
    {
        $this->_dirWalker($root, function ($path, $file) {
            $this->extends[$path] = $this->_read($file);
        });
    }

    /**
     * Autoload blueprints
     * @param string $root 
     * @return void 
     */
    public function blueprints(string $root)
    {
        $this->_dirWalker($root, function ($path, $file) {

            $name = F::relativepath($file);
            //Set YAML-File or they content
            $this->extends['blueprints'][$path] = (F::extension($file) === 'yml') ? $file : $this->_read($file);
        });
    }

    /**
     * Load classes
     * @param string $root 
     * @return void 
     */
    public function _classes(string $root)
    {
        $classes = [];
        $this->_dirWalker($root, function ($path, $file) use (&$classes) {
            $prefix = array_map('ucfirst', explode('/', $this->name));
            $classname = A::merge($prefix, explode('/', $path));
            $classes[implode('\\', $classname)] = $file;
        });

        F::loadClasses($classes);
    }


    /**
     * Autoload translations
     * @param string $root 
     * @return void 
     */
    public function i18n(string $root)
    {
        $this->_dirWalker($root, function ($path, $file) {
            $this->extends['translations'][$path] = $this->_read($file);
        });
    }

    /**
     * Autoload fields
     * @param string $root 
     * @return void 
     */
    public function fields(string $root)
    {
        $this->_dirWalker($root, function ($path, $file) {
            $this->extends['fields'][$path] = $this->_read($file);
        });
    }

    /**
     * Autoload sections
     * @param string $root 
     * @return void 
     */
    public function sections(string $root)
    {
        $this->_dirWalker($root, function ($path, $file) {
            $this->extends['sections'][$path] = $this->_read($file);
        });
    }

    /**
     * Autoload snippets
     * @param string $root 
     * @return void 
     */
    public function snippets(string $root)
    {
        $this->_dirWalker($root, function ($path, $file) {
            $this->extends['snippets'][$path] = $file;
        });
    }

    /**
     * Autoload templates
     * @param string $root 
     * @return void 
     */
    public function templates(string $root)
    {
        $this->_dirWalker($root, function ($path, $file) {
            $this->extends['templates'][$path] = $file;
        });
    }

    /**
     * Run autoloader and return the results
     * @param mixed ...$params 
     * @return array 
     */
    public static function load(...$params): array
    {
        $self = new self(...$params);
        return $self->toArray();
    }

    /**
     * Returns the extension data
     *  @return array  */
    public function toArray(): array
    {
        return A::merge($this->extends, $this->data);
    }
}
