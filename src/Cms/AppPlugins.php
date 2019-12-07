<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\DuplicateException;
use Kirby\Form\Field as FormField;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\V;

/**
 * AppPlugins
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait AppPlugins
{
    /**
     * A list of all registered plugins
     *
     * @var array
     */
    protected static $plugins = [];

    /**
     * The extension registry
     *
     * @var array
     */
    protected $extensions = [
        // load options first to make them available for the rest
        'options' => [],

        // other plugin types
        'api' => [],
        'blueprints' => [],
        'cacheTypes' => [],
        'collections' => [],
        'components' => [],
        'controllers' => [],
        'collectionFilters' => [],
        'fieldMethods' => [],
        'fileMethods' => [],
        'filesMethods' => [],
        'fields' => [],
        'hooks' => [],
        'pages' => [],
        'pageMethods' => [],
        'pagesMethods' => [],
        'pageModels' => [],
        'routes' => [],
        'sections' => [],
        'siteMethods' => [],
        'snippets' => [],
        'tags' => [],
        'templates' => [],
        'translations' => [],
        'userMethods' => [],
        'userModels' => [],
        'usersMethods' => [],
        'validators' => []
    ];

    /**
     * Cache for system extensions
     *
     * @var array
     */
    protected static $systemExtensions = null;

    /**
     * Flag when plugins have been loaded
     * to not load them again
     *
     * @var bool
     */
    protected $pluginsAreLoaded = false;

    /**
     * Register all given extensions
     *
     * @internal
     * @param array $extensions
     * @param \Kirby\Cms\Plugin $plugin The plugin which defined those extensions
     * @return array
     */
    public function extend(array $extensions, Plugin $plugin = null): array
    {
        foreach ($this->extensions as $type => $registered) {
            if (isset($extensions[$type]) === true) {
                $this->{'extend' . $type}($extensions[$type], $plugin);
            }
        }

        return $this->extensions;
    }

    /**
     * Registers API extensions
     *
     * @param array|bool $api
     * @return array
     */
    protected function extendApi($api): array
    {
        if (is_array($api) === true) {
            if (is_a($api['routes'] ?? [], 'Closure') === true) {
                $api['routes'] = $api['routes']($this);
            }

            return $this->extensions['api'] = A::merge($this->extensions['api'], $api, A::MERGE_APPEND);
        } else {
            return $this->extensions['api'];
        }
    }

    /**
     * Registers additional blueprints
     *
     * @param array $blueprints
     * @return array
     */
    protected function extendBlueprints(array $blueprints): array
    {
        return $this->extensions['blueprints'] = array_merge($this->extensions['blueprints'], $blueprints);
    }

    /**
     * Registers additional cache types
     *
     * @param array $cacheTypes
     * @return array
     */
    protected function extendCacheTypes(array $cacheTypes): array
    {
        return $this->extensions['cacheTypes'] = array_merge($this->extensions['cacheTypes'], $cacheTypes);
    }

    /**
     * Registers additional collection filters
     *
     * @param array $filters
     * @return array
     */
    protected function extendCollectionFilters(array $filters): array
    {
        return $this->extensions['collectionFilters'] = Collection::$filters = array_merge(Collection::$filters, $filters);
    }

    /**
     * Registers additional collections
     *
     * @param array $collections
     * @return array
     */
    protected function extendCollections(array $collections): array
    {
        return $this->extensions['collections'] = array_merge($this->extensions['collections'], $collections);
    }

    /**
     * Registers core components
     *
     * @param array $components
     * @return array
     */
    protected function extendComponents(array $components): array
    {
        return $this->extensions['components'] = array_merge($this->extensions['components'], $components);
    }

    /**
     * Registers additional controllers
     *
     * @param array $controllers
     * @return array
     */
    protected function extendControllers(array $controllers): array
    {
        return $this->extensions['controllers'] = array_merge($this->extensions['controllers'], $controllers);
    }

    /**
     * Registers additional file methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendFileMethods(array $methods): array
    {
        return $this->extensions['fileMethods'] = File::$methods = array_merge(File::$methods, $methods);
    }

    /**
     * Registers additional files methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendFilesMethods(array $methods): array
    {
        return $this->extensions['filesMethods'] = Files::$methods = array_merge(Files::$methods, $methods);
    }

    /**
     * Registers additional field methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendFieldMethods(array $methods): array
    {
        return $this->extensions['fieldMethods'] = Field::$methods = array_merge(Field::$methods, array_change_key_case($methods));
    }

    /**
     * Registers Panel fields
     *
     * @param array $fields
     * @return array
     */
    protected function extendFields(array $fields): array
    {
        return $this->extensions['fields'] = FormField::$types = array_merge(FormField::$types, $fields);
    }

    /**
     * Registers hooks
     *
     * @param array $hooks
     * @return array
     */
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

    /**
     * Registers markdown component
     *
     * @param Closure $markdown
     * @return Closure
     */
    protected function extendMarkdown(Closure $markdown)
    {
        return $this->extensions['markdown'] = $markdown;
    }

    /**
     * Registers additional options
     *
     * @param array $options
     * @param \Kirby\Cms\Plugin|null $plugin
     * @return array
     */
    protected function extendOptions(array $options, Plugin $plugin = null): array
    {
        if ($plugin !== null) {
            $prefixed = [];

            foreach ($options as $key => $value) {
                $prefixed[$plugin->prefix() . '.' . $key] = $value;
            }

            $options = $prefixed;
        }

        return $this->extensions['options'] = $this->options = A::merge($options, $this->options, A::MERGE_REPLACE);
    }

    /**
     * Registers additional page methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendPageMethods(array $methods): array
    {
        return $this->extensions['pageMethods'] = Page::$methods = array_merge(Page::$methods, $methods);
    }

    /**
     * Registers additional pages methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendPagesMethods(array $methods): array
    {
        return $this->extensions['pagesMethods'] = Pages::$methods = array_merge(Pages::$methods, $methods);
    }

    /**
     * Registers additional page models
     *
     * @param array $models
     * @return array
     */
    protected function extendPageModels(array $models): array
    {
        return $this->extensions['pageModels'] = Page::$models = array_merge(Page::$models, $models);
    }

    /**
     * Registers pages
     *
     * @param array $pages
     * @return array
     */
    protected function extendPages(array $pages): array
    {
        return $this->extensions['pages'] = array_merge($this->extensions['pages'], $pages);
    }

    /**
     * Registers additional routes
     *
     * @param array|Closure $routes
     * @return array
     */
    protected function extendRoutes($routes): array
    {
        if (is_a($routes, 'Closure') === true) {
            $routes = $routes($this);
        }

        return $this->extensions['routes'] = array_merge($this->extensions['routes'], $routes);
    }

    /**
     * Registers Panel sections
     *
     * @param array $sections
     * @return array
     */
    protected function extendSections(array $sections): array
    {
        return $this->extensions['sections'] = Section::$types = array_merge(Section::$types, $sections);
    }

    /**
     * Registers additional site methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendSiteMethods(array $methods): array
    {
        return $this->extensions['siteMethods'] = Site::$methods = array_merge(Site::$methods, $methods);
    }

    /**
     * Registers SmartyPants component
     *
     * @param Closure $smartypants
     * @return Closure
     */
    protected function extendSmartypants(Closure $smartypants)
    {
        return $this->extensions['smartypants'] = $smartypants;
    }

    /**
     * Registers additional snippets
     *
     * @param array $snippets
     * @return array
     */
    protected function extendSnippets(array $snippets): array
    {
        return $this->extensions['snippets'] = array_merge($this->extensions['snippets'], $snippets);
    }

    /**
     * Registers additional KirbyTags
     *
     * @param array $tags
     * @return array
     */
    protected function extendTags(array $tags): array
    {
        return $this->extensions['tags'] = KirbyTag::$types = array_merge(KirbyTag::$types, array_change_key_case($tags));
    }

    /**
     * Registers additional templates
     *
     * @param array $templates
     * @return array
     */
    protected function extendTemplates(array $templates): array
    {
        return $this->extensions['templates'] = array_merge($this->extensions['templates'], $templates);
    }

    /**
     * Registers translations
     *
     * @param array $translations
     * @return array
     */
    protected function extendTranslations(array $translations): array
    {
        return $this->extensions['translations'] = array_replace_recursive($this->extensions['translations'], $translations);
    }

    /**
     * Registers additional user methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendUserMethods(array $methods): array
    {
        return $this->extensions['userMethods'] = User::$methods = array_merge(User::$methods, $methods);
    }

    /**
     * Registers additional user models
     *
     * @param array $models
     * @return array
     */
    protected function extendUserModels(array $models): array
    {
        return $this->extensions['userModels'] = User::$models = array_merge(User::$models, $models);
    }

    /**
     * Registers additional users methods
     *
     * @param array $methods
     * @return array
     */
    protected function extendUsersMethods(array $methods): array
    {
        return $this->extensions['usersMethods'] = Users::$methods = array_merge(Users::$methods, $methods);
    }

    /**
     * Registers additional custom validators
     *
     * @param array $validators
     * @return array
     */
    protected function extendValidators(array $validators): array
    {
        return $this->extensions['validators'] = V::$validators = array_merge(V::$validators, $validators);
    }

    /**
     * Returns a given extension by type and name
     *
     * @internal
     * @param string $type i.e. `'hooks'`
     * @param string $name i.e. `'page.delete:before'`
     * @param mixed $fallback
     * @return mixed
     */
    public function extension(string $type, string $name, $fallback = null)
    {
        return $this->extensions($type)[$name] ?? $fallback;
    }

    /**
     * Returns the extensions registry
     *
     * @internal
     * @param string|null $type
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
     * Load extensions from site folders.
     * This is only used for models for now, but
     * could be extended later
     */
    protected function extensionsFromFolders()
    {
        $models = [];

        foreach (glob($this->root('models') . '/*.php') as $model) {
            $name  = F::name($model);
            $class = str_replace(['.', '-', '_'], '', $name) . 'Page';

            // load the model class
            include_once $model;

            if (class_exists($class) === true) {
                $models[$name] = $class;
            }
        }

        $this->extendPageModels($models);
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
            'api'    => $this->options['api']    ?? [],
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
     * @param array $props
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
        // load static extensions only once
        if (static::$systemExtensions === null) {
            // Form Field Mixins
            FormField::$mixins['filepicker'] = include static::$root . '/config/fields/mixins/filepicker.php';
            FormField::$mixins['min']        = include static::$root . '/config/fields/mixins/min.php';
            FormField::$mixins['options']    = include static::$root . '/config/fields/mixins/options.php';
            FormField::$mixins['pagepicker'] = include static::$root . '/config/fields/mixins/pagepicker.php';
            FormField::$mixins['picker']     = include static::$root . '/config/fields/mixins/picker.php';
            FormField::$mixins['upload']     = include static::$root . '/config/fields/mixins/upload.php';
            FormField::$mixins['userpicker'] = include static::$root . '/config/fields/mixins/userpicker.php';

            // Tag Aliases
            KirbyTag::$aliases = [
                'youtube' => 'video',
                'vimeo'   => 'video'
            ];

            // Field method aliases
            Field::$aliases = [
                'bool'    => 'toBool',
                'esc'     => 'escape',
                'excerpt' => 'toExcerpt',
                'float'   => 'toFloat',
                'h'       => 'html',
                'int'     => 'toInt',
                'kt'      => 'kirbytext',
                'kti'     => 'kirbytextinline',
                'link'    => 'toLink',
                'md'      => 'markdown',
                'sp'      => 'smartypants',
                'v'       => 'isValid',
                'x'       => 'xml'
            ];

            // blueprint presets
            PageBlueprint::$presets['pages']   = include static::$root . '/config/presets/pages.php';
            PageBlueprint::$presets['page']    = include static::$root . '/config/presets/page.php';
            PageBlueprint::$presets['files']   = include static::$root . '/config/presets/files.php';

            // section mixins
            Section::$mixins['empty']          = include static::$root . '/config/sections/mixins/empty.php';
            Section::$mixins['headline']       = include static::$root . '/config/sections/mixins/headline.php';
            Section::$mixins['help']           = include static::$root . '/config/sections/mixins/help.php';
            Section::$mixins['layout']         = include static::$root . '/config/sections/mixins/layout.php';
            Section::$mixins['max']            = include static::$root . '/config/sections/mixins/max.php';
            Section::$mixins['min']            = include static::$root . '/config/sections/mixins/min.php';
            Section::$mixins['pagination']     = include static::$root . '/config/sections/mixins/pagination.php';
            Section::$mixins['parent']         = include static::$root . '/config/sections/mixins/parent.php';

            // section types
            Section::$types['info']            = include static::$root . '/config/sections/info.php';
            Section::$types['pages']           = include static::$root . '/config/sections/pages.php';
            Section::$types['files']           = include static::$root . '/config/sections/files.php';
            Section::$types['fields']          = include static::$root . '/config/sections/fields.php';

            static::$systemExtensions = [
                'components'   => include static::$root . '/config/components.php',
                'blueprints'   => include static::$root . '/config/blueprints.php',
                'fields'       => include static::$root . '/config/fields.php',
                'fieldMethods' => include static::$root . '/config/methods.php',
                'tags'         => include static::$root . '/config/tags.php'
            ];
        }

        // default cache types
        $this->extendCacheTypes([
            'apcu'      => 'Kirby\Cache\ApcuCache',
            'file'      => 'Kirby\Cache\FileCache',
            'memcached' => 'Kirby\Cache\MemCached',
            'memory'    => 'Kirby\Cache\MemoryCache',
        ]);

        $this->extendComponents(static::$systemExtensions['components']);
        $this->extendBlueprints(static::$systemExtensions['blueprints']);
        $this->extendFields(static::$systemExtensions['fields']);
        $this->extendFieldMethods((static::$systemExtensions['fieldMethods'])($this));
        $this->extendTags(static::$systemExtensions['tags']);
    }

    /**
     * Kirby plugin factory and getter
     *
     * @param string $name
     * @param array|null $extends If null is passed it will be used as getter. Otherwise as factory.
     * @return \Kirby\Cms\Plugin|null
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
            throw new DuplicateException('The plugin "' . $name . '" has already been registered');
        }

        return static::$plugins[$name] = $plugin;
    }

    /**
     * Loads and returns all plugins in the site/plugins directory
     * Loading only happens on the first call.
     *
     * @internal
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
        $loaded = [];

        foreach (Dir::read($root) as $dirname) {
            if (in_array(substr($dirname, 0, 1), ['.', '_']) === true) {
                continue;
            }

            $dir   = $root . '/' . $dirname;
            $entry = $dir . '/index.php';

            if (is_dir($dir) !== true || is_file($entry) !== true) {
                continue;
            }

            include_once $entry;

            $loaded[] = $dir;
        }

        return $loaded;
    }
}
