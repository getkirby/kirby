<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Form\Field;
use Kirby\Util\Dir;

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
                throw new Exception(sprintf('Extensions for "%s" must be defined as array', $type));
            }

            $extends[$type] = Extend::$type($values, $plugin);
        }

        // extensions that need to be registered instantly
        $this->extendFieldMethods($extends['fieldMethods'] ?? []);
        $this->extendFields($extends['fields'] ?? []);
        $this->extendPageModels($extends['pageModels'] ?? []);

        return $this->extensions = array_replace_recursive($this->extensions, $extends);
    }

    protected function extendFieldMethods(array $methods)
    {
        ContentField::$methods = array_merge(ContentField::$methods, $methods);
    }

    protected function extendFields(array $fields)
    {
        foreach ($fields as $name => $field) {
            Field::$types[$name] = $field['class'];
        }
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
        $this->extend([
            'blueprints'   =>  include static::$root . '/config/blueprints.php',
            'fieldMethods' => (include static::$root . '/config/methods.php')($this),
            'tags'         =>  include static::$root . '/config/tags.php'
        ]);
    }

    /**
     * Kirby plugin factory and getter
     *
     * @param string|array $props If a string is passed it will be used as getter. Otherwise as factory.
     * @return Plugin|null
     */
    public static function plugin($props)
    {
        if (is_string($props) === true) {
            return static::$plugins[$props] ?? null;
        }

        if (is_array($props) === false) {
            throw new Exception('Invalid plugin definition');
        }

        // automatic root detection
        $props['root'] = $props['root'] ?? dirname(debug_backtrace()[0]['file']);

        $plugin = new Plugin($props);
        $name   = $plugin->name();

        if (isset(static::$plugins[$name]) === true) {
            throw new Exception(sprintf('The plugin "%s" has already been registered', $name));
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

    /**
     * Remove all registered plugins
     * This is especially useful in
     * testing scenarios
     *
     * @return void
     */
    public static function removePlugins()
    {
        static::$plugins = [];
    }

}
