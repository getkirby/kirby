<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field as FormField;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\V;

trait AppPlugins
{
    protected static $plugins = [];

    protected $extensions = [
        'blueprints' => [],
        'collections' => [],
        'components' => [],
        'controllers' => [],
        'fieldMethods' => [],
        'fields' => [],
        'hooks' => [],
        'options' => [],
        'pages' => [],
        'pageMethods' => [],
        'pageModels' => [],
        'routes' => [],
        'snippets' => [],
        'tags' => [],
        'templates' => [],
        'validators' => []
    ];

    protected $pluginsAreLoaded = false;

    public function extend(array $extensions, Plugin $plugin = null): array
    {
        foreach ($this->extensions as $type => $registered) {
            if (isset($extensions[$type]) === true) {
                $this->{'extend' . $type}($extensions[$type], $plugin);
            }
        }

        return $this->extensions;
    }

    protected function extendBlueprints(array $blueprints): array
    {
        return $this->extensions['blueprints'] = array_merge($this->extensions['blueprints'], $blueprints);
    }

    protected function extendCollections(array $collections): array
    {
        return $this->extensions['collections'] = array_merge($this->extensions['collections'], $collections);
    }

    protected function extendComponents(array $components): array
    {
        return $this->extensions['components'] = array_merge($this->extensions['components'], $components);
    }

    protected function extendControllers(array $controllers): array
    {
        return $this->extensions['controllers'] = array_merge($this->extensions['controllers'], $controllers);
    }

    protected function extendFieldMethods(array $methods): array
    {
        return $this->extensions['fieldMethods'] = Field::$methods = array_merge(Field::$methods, $methods);
    }

    protected function extendFields(array $fields): array
    {
        return $this->extensions['fields'] = FormField::$types = array_merge(FormField::$types, $fields);
    }

    protected function extendHooks(array $hooks): array
    {
        foreach ($hooks as $name => $callbacks) {
            if (isset($this->extensions['hooks'][$name]) === false) {
                $this->extensions['hooks'][$name] = [];
            }

            if (is_array($callbacks) === false) {
                $callbacks = [$callbacks];
            }

            foreach ($callbacks as $callback) {
                $this->extensions['hooks'][$name][] = $callback;
            }
        }

        return $this->extensions['hooks'];
    }

    protected function extendMarkdown(Closure $markdown): array
    {
        return $this->extensions['markdown'] = $markdown;
    }

    protected function extendOptions(array $options, Plugin $plugin = null): array
    {
        if ($plugin !== null) {
            $prefixed = [];

            foreach ($options as $key => $value) {
                $prefixed[$plugin->prefix() . '.' . $key] = $value;
            }

            $options = $prefixed;
        }

        return $this->extensions['options'] = $this->options = array_replace_recursive($this->options, $options);
    }

    protected function extendPageMethods(array $methods): array
    {
        return $this->extensions['pageMethods'] = Page::$methods = array_merge(Page::$methods, $methods);
    }

    protected function extendPageModels(array $models): array
    {
        return $this->extensions['pageModels'] = Page::$models = array_merge(Page::$models, $models);
    }

    protected function extendPages(array $pages): array
    {
        return $this->extensions['pages'] = array_merge($this->extensions['pages'], $pages);
    }

    protected function extendRoutes(array $routes): array
    {
        return $this->extensions['routes'] = array_merge($this->extensions['routes'], $routes);
    }

    protected function extendSmartypants(Closure $smartypants): array
    {
        return $this->extensions['smartypants'] = $smartypants;
    }

    protected function extendSnippets(array $snippets): array
    {
        return $this->extensions['snippets'] = array_merge($this->extensions['snippets'], $snippets);
    }

    protected function extendTags(array $tags): array
    {
        return $this->extensions['tags'] = KirbyTag::$types = array_merge(KirbyTag::$types, $tags);
    }

    protected function extendTemplates(array $templates): array
    {
        return $this->extensions['templates'] = array_merge($this->extensions['templates'], $templates);
    }

    protected function extendValidators(array $validators): array
    {
        return $this->extensions['validators'] = V::$validators = array_merge(V::$validators, $validators);
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
     * Register extensions that could be located in
     * the options array. I.e. hooks and routes can be
     * setup from the config.
     *
     * @return array
     */
    protected function extensionsFromOptions()
    {
        // register routes and hooks from options
        $this->extend([
            'routes' => $this->options['routes'] ?? [],
            'hooks'  => $this->options['hooks']  ?? []
        ]);
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
            $extends = $plugin->extends();

            if (empty($extends) === false) {
                $this->extend($extends, $plugin);
            }
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
        // Form Field Mixins
        FormField::$mixins['options'] = include static::$root . '/config/field-mixins/options.php';

        // Tag Aliases
        KirbyTag::$aliases = [
            'youtube' => 'video',
            'vimeo'   => 'video'
        ];

        $this->extendComponents(include static::$root . '/config/components.php');
        $this->extendBlueprints(include static::$root . '/config/blueprints.php');
        $this->extendFields(include static::$root . '/config/fields.php');
        $this->extendFieldMethods((include static::$root . '/config/methods.php')($this));
        $this->extendTags(include static::$root . '/config/tags.php');
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
