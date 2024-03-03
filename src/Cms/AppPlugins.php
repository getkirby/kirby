<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\Field;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Filesystem\Mime;
use Kirby\Form\Field as FormField;
use Kirby\Image\Image;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection as ToolkitCollection;
use Kirby\Toolkit\V;

/**
 * AppPlugins
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait AppPlugins
{
	/**
	 * A list of all registered plugins
	 */
	protected static array $plugins = [];

	/**
	 * The extension registry
	 */
	protected array $extensions = [
		// load options first to make them available for the rest
		'options' => [],

		// other plugin types
		'api' => [],
		'areas' => [],
		'assetMethods' => [],
		'authChallenges' => [],
		'blockMethods' => [],
		'blockModels' => [],
		'blocksMethods' => [],
		'blueprints' => [],
		'cacheTypes' => [],
		'collections' => [],
		'commands' => [],
		'components' => [],
		'controllers' => [],
		'collectionFilters' => [],
		'collectionMethods' => [],
		'fieldMethods' => [],
		'fileMethods' => [],
		'fileTypes' => [],
		'filesMethods' => [],
		'fields' => [],
		'hooks' => [],
		'layoutMethods' => [],
		'layoutColumnMethods' => [],
		'layoutsMethods' => [],
		'pages' => [],
		'pageMethods' => [],
		'pagesMethods' => [],
		'pageModels' => [],
		'permissions' => [],
		'routes' => [],
		'sections' => [],
		'siteMethods' => [],
		'snippets' => [],
		'structureMethods' => [],
		'structureObjectMethods' => [],
		'tags' => [],
		'templates' => [],
		'thirdParty' => [],
		'translations' => [],
		'userMethods' => [],
		'userModels' => [],
		'usersMethods' => [],
		'validators' => [],
	];

	/**
	 * Flag when plugins have been loaded
	 * to not load them again
	 */
	protected bool $pluginsAreLoaded = false;

	/**
	 * Register all given extensions
	 *
	 * @internal
	 * @param \Kirby\Cms\Plugin $plugin|null The plugin which defined those extensions
	 */
	public function extend(
		array $extensions,
		Plugin $plugin = null
	): array {
		foreach ($this->extensions as $type => $registered) {
			if (isset($extensions[$type]) === true) {
				$this->{'extend' . $type}($extensions[$type], $plugin);
			}
		}

		return $this->extensions;
	}

	/**
	 * Registers API extensions
	 */
	protected function extendApi(array|bool $api): array
	{
		if (is_array($api) === true) {
			if (($api['routes'] ?? []) instanceof Closure) {
				$api['routes'] = $api['routes']($this);
			}

			return $this->extensions['api'] = A::merge($this->extensions['api'], $api, A::MERGE_APPEND);
		}

		return $this->extensions['api'];
	}

	/**
	 * Registers additional custom Panel areas
	 */
	protected function extendAreas(array $areas): array
	{
		foreach ($areas as $id => $area) {
			$this->extensions['areas'][$id] ??= [];
			$this->extensions['areas'][$id][] = $area;
		}

		return $this->extensions['areas'];
	}

	/**
	 * Registers additional asset methods
	 */
	protected function extendAssetMethods(array $methods): array
	{
		return $this->extensions['assetMethods'] = Asset::$methods = array_merge(Asset::$methods, $methods);
	}

	/**
	 * Registers additional authentication challenges
	 */
	protected function extendAuthChallenges(array $challenges): array
	{
		return $this->extensions['authChallenges'] = Auth::$challenges = array_merge(Auth::$challenges, $challenges);
	}

	/**
	 * Registers additional block methods
	 */
	protected function extendBlockMethods(array $methods): array
	{
		return $this->extensions['blockMethods'] = Block::$methods = array_merge(Block::$methods, $methods);
	}

	/**
	 * Registers additional block models
	 */
	protected function extendBlockModels(array $models): array
	{
		return $this->extensions['blockModels'] = Block::$models = array_merge(Block::$models, $models);
	}

	/**
	 * Registers additional blocks methods
	 */
	protected function extendBlocksMethods(array $methods): array
	{
		return $this->extensions['blockMethods'] = Blocks::$methods = array_merge(Blocks::$methods, $methods);
	}

	/**
	 * Registers additional blueprints
	 */
	protected function extendBlueprints(array $blueprints): array
	{
		return $this->extensions['blueprints'] = array_merge($this->extensions['blueprints'], $blueprints);
	}

	/**
	 * Registers additional cache types
	 */
	protected function extendCacheTypes(array $cacheTypes): array
	{
		return $this->extensions['cacheTypes'] = array_merge($this->extensions['cacheTypes'], $cacheTypes);
	}

	/**
	 * Registers additional CLI commands
	 */
	protected function extendCommands(array $commands): array
	{
		return $this->extensions['commands'] = array_merge($this->extensions['commands'], $commands);
	}

	/**
	 * Registers additional collection filters
	 */
	protected function extendCollectionFilters(array $filters): array
	{
		return $this->extensions['collectionFilters'] = ToolkitCollection::$filters = array_merge(ToolkitCollection::$filters, $filters);
	}

	/**
	 * Registers additional collection methods
	 */
	protected function extendCollectionMethods(array $methods): array
	{
		return $this->extensions['collectionMethods'] = Collection::$methods = array_merge(Collection::$methods, $methods);
	}

	/**
	 * Registers additional collections
	 */
	protected function extendCollections(array $collections): array
	{
		return $this->extensions['collections'] = array_merge($this->extensions['collections'], $collections);
	}

	/**
	 * Registers core components
	 */
	protected function extendComponents(array $components): array
	{
		return $this->extensions['components'] = array_merge($this->extensions['components'], $components);
	}

	/**
	 * Registers additional controllers
	 */
	protected function extendControllers(array $controllers): array
	{
		return $this->extensions['controllers'] = array_merge($this->extensions['controllers'], $controllers);
	}

	/**
	 * Registers additional file methods
	 */
	protected function extendFileMethods(array $methods): array
	{
		return $this->extensions['fileMethods'] = File::$methods = array_merge(File::$methods, $methods);
	}

	/**
	 * Registers additional custom file types and mimes
	 */
	protected function extendFileTypes(array $fileTypes): array
	{
		// normalize array
		foreach ($fileTypes as $ext => $file) {
			$extension = $file['extension'] ?? $ext;
			$type      = $file['type'] ?? null;
			$mime      = $file['mime'] ?? null;
			$resizable = $file['resizable'] ?? false;
			$viewable  = $file['viewable'] ?? false;

			if (is_string($type) === true) {
				if (isset(F::$types[$type]) === false) {
					F::$types[$type] = [];
				}

				if (in_array($extension, F::$types[$type]) === false) {
					F::$types[$type][] = $extension;
				}
			}

			if ($mime !== null) {
				if (array_key_exists($extension, Mime::$types) === true) {
					// if `Mime::$types[$extension]` is not already an array, make it one
					// and append the new MIME type unless it's already in the list
					Mime::$types[$extension] = array_unique(array_merge((array)Mime::$types[$extension], (array)$mime));
				} else {
					Mime::$types[$extension] = $mime;
				}
			}

			if ($resizable === true && in_array($extension, Image::$resizableTypes) === false) {
				Image::$resizableTypes[] = $extension;
			}

			if ($viewable === true && in_array($extension, Image::$viewableTypes) === false) {
				Image::$viewableTypes[] = $extension;
			}
		}

		return $this->extensions['fileTypes'] = [
			'type'      => F::$types,
			'mime'      => Mime::$types,
			'resizable' => Image::$resizableTypes,
			'viewable'  => Image::$viewableTypes
		];
	}

	/**
	 * Registers additional files methods
	 */
	protected function extendFilesMethods(array $methods): array
	{
		return $this->extensions['filesMethods'] = Files::$methods = array_merge(Files::$methods, $methods);
	}

	/**
	 * Registers additional field methods
	 */
	protected function extendFieldMethods(array $methods): array
	{
		return $this->extensions['fieldMethods'] = Field::$methods = array_merge(Field::$methods, array_change_key_case($methods));
	}

	/**
	 * Registers Panel fields
	 */
	protected function extendFields(array $fields): array
	{
		return $this->extensions['fields'] = FormField::$types = array_merge(FormField::$types, $fields);
	}

	/**
	 * Registers hooks
	 */
	protected function extendHooks(array $hooks): array
	{
		foreach ($hooks as $name => $callbacks) {
			$this->extensions['hooks'][$name] ??= [];

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
	 */
	protected function extendMarkdown(Closure $markdown): Closure
	{
		return $this->extensions['markdown'] = $markdown;
	}

	/**
	 * Registers additional layout methods
	 */
	protected function extendLayoutMethods(array $methods): array
	{
		return $this->extensions['layoutMethods'] = Layout::$methods = array_merge(Layout::$methods, $methods);
	}

	/**
	 * Registers additional layout column methods
	 */
	protected function extendLayoutColumnMethods(array $methods): array
	{
		return $this->extensions['layoutColumnMethods'] = LayoutColumn::$methods = array_merge(LayoutColumn::$methods, $methods);
	}

	/**
	 * Registers additional layouts methods
	 */
	protected function extendLayoutsMethods(array $methods): array
	{
		return $this->extensions['layoutsMethods'] = Layouts::$methods = array_merge(Layouts::$methods, $methods);
	}

	/**
	 * Registers additional options
	 */
	protected function extendOptions(
		array $options,
		Plugin $plugin = null
	): array {
		if ($plugin !== null) {
			$options = [$plugin->prefix() => $options];
		}

		return $this->extensions['options'] = $this->options = A::merge($options, $this->options, A::MERGE_REPLACE);
	}

	/**
	 * Registers additional page methods
	 */
	protected function extendPageMethods(array $methods): array
	{
		return $this->extensions['pageMethods'] = Page::$methods = array_merge(Page::$methods, $methods);
	}

	/**
	 * Registers additional pages methods
	 */
	protected function extendPagesMethods(array $methods): array
	{
		return $this->extensions['pagesMethods'] = Pages::$methods = array_merge(Pages::$methods, $methods);
	}

	/**
	 * Registers additional page models
	 */
	protected function extendPageModels(array $models): array
	{
		return $this->extensions['pageModels'] = Page::$models = array_merge(Page::$models, $models);
	}

	/**
	 * Registers pages
	 */
	protected function extendPages(array $pages): array
	{
		return $this->extensions['pages'] = array_merge($this->extensions['pages'], $pages);
	}

	/**
	 * Registers additional permissions
	 */
	protected function extendPermissions(
		array $permissions,
		Plugin $plugin = null
	): array {
		if ($plugin !== null) {
			$permissions = [$plugin->prefix() => $permissions];
		}

		return $this->extensions['permissions'] = Permissions::$extendedActions = array_merge(Permissions::$extendedActions, $permissions);
	}

	/**
	 * Registers additional routes
	 */
	protected function extendRoutes(array|Closure $routes): array
	{
		if ($routes instanceof Closure) {
			$routes = $routes($this);
		}

		return $this->extensions['routes'] = array_merge($this->extensions['routes'], $routes);
	}

	/**
	 * Registers Panel sections
	 */
	protected function extendSections(array $sections): array
	{
		return $this->extensions['sections'] = Section::$types = array_merge(Section::$types, $sections);
	}

	/**
	 * Registers additional site methods
	 */
	protected function extendSiteMethods(array $methods): array
	{
		return $this->extensions['siteMethods'] = Site::$methods = array_merge(Site::$methods, $methods);
	}

	/**
	 * Registers SmartyPants component
	 */
	protected function extendSmartypants(Closure $smartypants): Closure
	{
		return $this->extensions['smartypants'] = $smartypants;
	}

	/**
	 * Registers additional snippets
	 */
	protected function extendSnippets(array $snippets): array
	{
		return $this->extensions['snippets'] = array_merge($this->extensions['snippets'], $snippets);
	}

	/**
	 * Registers additional structure methods
	 */
	protected function extendStructureMethods(array $methods): array
	{
		return $this->extensions['structureMethods'] = Structure::$methods = array_merge(Structure::$methods, $methods);
	}

	/**
	 * Registers additional structure object methods
	 */
	protected function extendStructureObjectMethods(array $methods): array
	{
		return $this->extensions['structureObjectMethods'] = StructureObject::$methods = array_merge(StructureObject::$methods, $methods);
	}

	/**
	 * Registers additional KirbyTags
	 */
	protected function extendTags(array $tags): array
	{
		return $this->extensions['tags'] = KirbyTag::$types = array_merge(KirbyTag::$types, array_change_key_case($tags));
	}

	/**
	 * Registers additional templates
	 */
	protected function extendTemplates(array $templates): array
	{
		return $this->extensions['templates'] = array_merge($this->extensions['templates'], $templates);
	}

	/**
	 * Registers translations
	 */
	protected function extendTranslations(array $translations): array
	{
		return $this->extensions['translations'] = array_replace_recursive($this->extensions['translations'], $translations);
	}

	/**
	 * Add third party extensions to the registry
	 * so they can be used as plugins for plugins
	 * for example.
	 */
	protected function extendThirdParty(array $extensions): array
	{
		return $this->extensions['thirdParty'] = array_replace_recursive($this->extensions['thirdParty'], $extensions);
	}

	/**
	 * Registers additional user methods
	 */
	protected function extendUserMethods(array $methods): array
	{
		return $this->extensions['userMethods'] = User::$methods = array_merge(User::$methods, $methods);
	}

	/**
	 * Registers additional user models
	 */
	protected function extendUserModels(array $models): array
	{
		return $this->extensions['userModels'] = User::$models = array_merge(User::$models, $models);
	}

	/**
	 * Registers additional users methods
	 */
	protected function extendUsersMethods(array $methods): array
	{
		return $this->extensions['usersMethods'] = Users::$methods = array_merge(Users::$methods, $methods);
	}

	/**
	 * Registers additional custom validators
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
	 */
	public function extension(
		string $type,
		string $name,
		mixed $fallback = null
	): mixed {
		return $this->extensions($type)[$name] ?? $fallback;
	}

	/**
	 * Returns the extensions registry
	 *
	 * @internal
	 */
	public function extensions(string $type = null): array
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
	protected function extensionsFromFolders(): void
	{
		$models = [];

		foreach (glob($this->root('models') . '/*.php') as $model) {
			$name  = F::name($model);
			$class = str_replace(['.', '-', '_'], '', $name) . 'Page';

			// load the model class
			F::loadOnce($model, allowOutput: false);

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
	 */
	protected function extensionsFromOptions(): void
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
	 */
	protected function extensionsFromPlugins(): void
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
	 */
	protected function extensionsFromProps(array $props): void
	{
		$this->extend($props);
	}

	/**
	 * Apply all default extensions
	 */
	protected function extensionsFromSystem(): void
	{
		// Always start with fresh fields and sections
		// from the core and add plugins on top of that
		FormField::$types = [];
		Section::$types   = [];

		// mixins
		FormField::$mixins = $this->core->fieldMixins();
		Section::$mixins   = $this->core->sectionMixins();

		// aliases
		KirbyTag::$aliases = $this->core->kirbyTagAliases();
		Field::$aliases    = $this->core->fieldMethodAliases();

		// blueprint presets
		PageBlueprint::$presets = $this->core->blueprintPresets();

		$this->extendAuthChallenges($this->core->authChallenges());
		$this->extendCacheTypes($this->core->cacheTypes());
		$this->extendComponents($this->core->components());
		$this->extendBlueprints($this->core->blueprints());
		$this->extendFieldMethods($this->core->fieldMethods());
		$this->extendFields($this->core->fields());
		$this->extendSections($this->core->sections());
		$this->extendSnippets($this->core->snippets());
		$this->extendTags($this->core->kirbyTags());
		$this->extendTemplates($this->core->templates());
	}

	/**
	 * Checks if a native component was extended
	 * @since 3.7.0
	 */
	public function isNativeComponent(string $component): bool
	{
		return $this->component($component) === $this->nativeComponent($component);
	}

	/**
	 * Returns the native implementation
	 * of a core component
	 */
	public function nativeComponent(string $component): Closure|false
	{
		return $this->core->components()[$component] ?? false;
	}

	/**
	 * Kirby plugin factory and getter
	 *
	 * @param array|null $extends If null is passed it will be used as getter. Otherwise as factory.
	 * @throws \Kirby\Exception\DuplicateException
	 */
	public static function plugin(
		string $name,
		array $extends = null
	): PLugin|null {
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
	 * @param array|null $plugins Can be used to overwrite the plugins registry
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

			$dir = $root . '/' . $dirname;

			if (is_dir($dir) !== true) {
				continue;
			}

			$entry  = $dir . '/index.php';
			$script = $dir . '/index.js';
			$styles = $dir . '/index.css';

			if (is_file($entry) === true) {
				F::loadOnce($entry, allowOutput: false);
			} elseif (is_file($script) === true || is_file($styles) === true) {
				// if no PHP file is present but an index.js or index.css,
				// register as anonymous plugin (without actual extensions)
				// to be picked up by the Panel\Document class when
				// rendering the Panel view
				static::plugin('plugins/' . $dirname, ['root' => $dir]);
			} else {
				continue;
			}

			$loaded[] = $dir;
		}

		return $loaded;
	}
}
