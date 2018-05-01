<?php

namespace Kirby\Cms;

use Kirby\Form\Field;
use Kirby\Util\Dir;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;

trait AppPlugins
{
    protected static $plugins = [];

    protected $extensions = [];
    protected $pluginsAreLoaded = false;

    public function extend(array $extensions, Plugin $plugin = null): array
    {
        $extends = [];

        foreach ($extensions as $type => $values) {
            if (method_exists(Extend::class, $type) === false) {
                // ignore invalid extensions
                continue;
            }

            if (is_array($values) === false) {
                throw new InvalidArgumentException('Extensions for "' . $type . '" must be defined as array');
            }

            $extends[$type] = Extend::$type($values, $plugin);
        }

        // extensions that need to be registered instantly
        $this->extendFieldMethods($extends['fieldMethods'] ?? []);
        $this->extendFields($extends['fields'] ?? []);
        $this->extendPageMethods($extends['pageMethods'] ?? []);
        $this->extendPageModels($extends['pageModels'] ?? []);

        return $this->extensions = array_replace_recursive($this->extensions, $extends);
    }

    protected function extendFieldMethods(array $methods)
    {
        ContentField::$methods = array_merge(ContentField::$methods, $methods);
    }

    protected function extendFields(array $fields)
    {
        Field::$types = array_merge(Field::$types, $fields);
    }

    protected function extendPageMethods(array $methods)
    {
        Page::$methods = array_merge(Page::$methods, $methods);
    }

    protected function extendPageModels(array $models)
    {
        // page models
        Page::$models = array_merge(Page::$models, $models);
    }

    public function extension(string $type, string $name, $fallback = null)
    {
        return $this->extensions($type)[$name] ?? $fallback;
    }

    /**
     * Returns the extensions registry
     *
     * @return array
     */
    public function extensions(string $type = null)
    {
        if ($type === null) {
            return $this->extensions;
        }

        return $this->extensions[$type] ?? [];
    }


    /**
     * Apply all plugin extensions
     *
     * @param array $plugins
     * @return void
     */
    protected function extensionsFromPlugins()
    {
        // register all their extensions
        foreach ($this->plugins() as $plugin) {
            $this->extend($plugin->extends(), $plugin);
        }
    }

    /**
     * Apply all passed extensions
     *
     * @return void
     */
    protected function extensionsFromProps(array $props)
    {
        $this->extend($props);
    }

    /**
     * Apply all default extensions
     *
     * @return void
     */
    protected function extensionsFromSystem()
    {
        // Field Mixins
        Field::$mixins['options'] = include static::$root . '/config/field-mixins/options.php';

        // extendable stuff
        $this->extend([
            'blueprints'   =>  include static::$root . '/config/blueprints.php',
            'fields'       =>  include static::$root . '/config/fields.php',
            'fieldMethods' => (include static::$root . '/config/methods.php')($this),
            'tags'         =>  include static::$root . '/config/tags.php'
        ]);
    }

    /**
     * Kirby plugin factory and getter
     *
     * @param string $name
     * @param array|null $extends If null is passed it will be used as getter. Otherwise as factory.
     * @return Plugin|null
     */
    public static function plugin(string $name, array $extends = null)
    {
        if ($extends === null) {
            return static::$plugins[$name] ?? null;
        }

        // get the correct root for the plugin
        $extends['root'] = $extends['root'] ?? dirname(debug_backtrace()[0]['file']);

        $plugin = new Plugin($name, $extends);
        $name   = $plugin->name();

        if (isset(static::$plugins[$name]) === true) {
            throw new DuplicateException('The plugin "'. $name . '" has already been registered');
        }

        return static::$plugins[$name] = $plugin;
    }

    /**
     * Loads and returns all plugins in the site/plugins directory
     * Loading only happens on the first call.
     *
     * @param array $plugins Can be used to overwrite the plugins registry
     * @return array
     */
    public function plugins(array $plugins = null): array
    {
        // overwrite the existing plugins registry
        if ($plugins !== null) {
            $this->pluginsAreLoaded = true;
            return static::$plugins = $plugins;
        }

        // don't load plugins twice
        if ($this->pluginsAreLoaded === true) {
            return static::$plugins;
        }

        // load all plugins from site/plugins
        $this->pluginsLoader();

        // mark plugins as loaded to stop doing it twice
        $this->pluginsAreLoaded = true;
        return static::$plugins;
    }

    /**
     * Loads all plugins from site/plugins
     *
     * @return array Array of loaded directories
     */
    protected function pluginsLoader(): array
    {
        $root   = $this->root('plugins');
        $kirby  = $this;
        $loaded = [];

        foreach (Dir::read($root) as $dirname) {
            if (is_dir($root . '/' . $dirname) === false) {
                continue;
            }

            $dir   = $root . '/' . $dirname;
            $entry = $dir . '/index.php';

            if (file_exists($entry) === false) {
                continue;
            }

            include_once $entry;

            $loaded[] = $dir;
        }

        return $loaded;
    }

}
