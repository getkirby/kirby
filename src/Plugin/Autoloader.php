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

    public array $data = [];

    /**
     * @param string $name Plugin name
     * @param string $root The root path of the plugin
     * @param array $data Predefined extension data to extend
     * @return void|$this 
     */
    public function __construct(
        public string $name,
        public string $root
    ) {

        //Load classes before everything else
        $classfolder = $root . '/classes/';

        if (Dir::exists($classfolder)) {
            $this->loadClasses($classfolder);
        }

        $folders = [
            'autoload'      => 'loadAutoload',
            'blueprints'    => 'loadBlueprints',
            'i18n'          => 'loadTranslations',
            'fields'        => 'loadFields',
            'sections'      => 'loadSections',
            'snippets'      => 'loadSnippets',
            'templates'     => 'loadTemplates'
        ];

        foreach (Dir::dirs($this->root) as $dir) {
            if (array_key_exists($dir, $folders)) {
                $method = $folders[$dir];
                $this->$method($this->root . '/' . $dir . '/');
            }
        }
    }

    /**
     * Autoload autoload
     * @param string $root 
     * @return void 
     */
    public function loadAutoload(string $root): void
    {
        foreach (Dir::files($root) as $path) {

            $key = F::name($path);
            $file = $root . $path;
            $this->data[$key] = Data::read($file);
        };
    }

    /**
     * Autoload blueprints
     * @param string $root 
     * @return void 
     */
    public function loadBlueprints(string $root): void
    {
        foreach (Dir::index($root) as $path) {

            $file = $root . $path;

            //Skip folder and file/folder thats starts with '_'
            if (F::exists($file) === false || Str::contains($path, '/_')) {
                continue;
            }

            //Has no Subfolder
            if (($dirname = F::dirname($path)) === '.') {
                $key = F::name($path);
            }

            $key ??= $dirname . '/' . F::name($path);

            $this->data['blueprints'][$key] = Data::read($file);
        };
    }

    /**
     * Load classes
     * @param string $root 
     * @return void 
     */
    public function loadClasses(string $root): void
    {

        $classes = [];

        foreach (Dir::index($root) as $path) {

            $file = $root . $path;

            //Skip folder and file/folder thats starts with '_'
            if (F::exists($file) === false || Str::contains($path, '/_')) {
                continue;
            }

            //Has no Subfolder
            if (($dirname = F::dirname($path)) === '.') {
                $key = F::name($path);
            }

            $key ??= $dirname . '/' . F::name($path);

            $prefix = array_map('ucfirst', explode('/', $this->name));
            $classname = A::merge($prefix, explode('/', $key));
            $classes[implode('\\', $classname)] = $file;
        };

        F::loadClasses($classes);
    }


    /**
     * Autoload translations
     * @param string $root 
     * @return void 
     */
    public function loadTranslations(string $root): void
    {
        foreach (Dir::index($root) as $path) {

            $file = $root . $path;

            //Skip folder and file/folder thats starts with '_'
            if (F::exists($file) === false || Str::contains($path, '/_')) {
                continue;
            }

            //Has no Subfolder
            if (($dirname = F::dirname($path)) === '.') {
                $key = F::name($path);
            }

            $key ??= $dirname . '/' . F::name($path);

            $this->data['translations'][$key] = Data::read($file);
        };
    }

    /**
     * Autoload fields
     * @param string $root 
     * @return void 
     */
    public function loadFields(string $root): void
    {
        foreach (Dir::index($root) as $path) {

            $file = $root . $path;

            //Skip folder and file/folder thats starts with '_'
            if (F::exists($file) === false || Str::contains($path, '/_')) {
                continue;
            }

            //Has no Subfolder
            if (($dirname = F::dirname($path)) === '.') {
                $key = F::name($path);
            }

            $key ??= $dirname . '/' . F::name($path);

            $this->data['fields'][$key] = Data::read($file);
        };
    }

    /**
     * Autoload sections
     * @param string $root 
     * @return void 
     */
    public function loadSections(string $root): void
    {
        foreach (Dir::index($root) as $path) {

            $file = $root . $path;

            //Skip folder and file/folder thats starts with '_'
            if (F::exists($file) === false || Str::contains($path, '/_')) {
                continue;
            }

            //Has no Subfolder
            if (($dirname = F::dirname($path)) === '.') {
                $key = F::name($path);
            }

            $key ??= $dirname . '/' . F::name($path);

            $this->data['sections'][$key] = Data::read($file);
        };
    }

    /**
     * Autoload snippets
     * @param string $root 
     * @return void 
     */
    public function loadSnippets(string $root): void
    {
        foreach (Dir::index($root) as $path) {

            $file = $root . $path;

            //Skip folder and file/folder thats starts with '_'
            if (F::exists($file) === false || Str::contains($path, '/_')) {
                continue;
            }

            //Has no Subfolder
            if (($dirname = F::dirname($path)) === '.') {
                $key = F::name($path);
            }

            $key ??= $dirname . '/' . F::name($path);

            $this->data['snippets'][$key] = $file;
        };
    }

    /**
     * Autoload templates
     * @param string $root 
     * @return void 
     */
    public function loadTemplates(string $root): void
    {
        foreach (Dir::index($root) as $path) {

            $file = $root . $path;

            //Skip folder and file/folder thats starts with '_'
            if (F::exists($file) === false || Str::contains($path, '/_')) {
                continue;
            }

            //Has no Subfolder
            if (($dirname = F::dirname($path)) === '.') {
                $key = F::name($path);
            }

            $key ??= $dirname . '/' . F::name($path);

            $this->data['templates'][$key] = $file;
        };
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
     * Returns the autoloaded data
     *  @return array  */
    public function toArray(): array
    {
        return $this->data;
    }
}
