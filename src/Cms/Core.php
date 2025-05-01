<?php

namespace Kirby\Cms;

use Kirby\Cache\ApcuCache;
use Kirby\Cache\FileCache;
use Kirby\Cache\MemCached;
use Kirby\Cache\MemoryCache;
use Kirby\Cache\RedisCache;
use Kirby\Cms\Auth\EmailChallenge;
use Kirby\Cms\Auth\TotpChallenge;
use Kirby\Form\Field\BlocksField;
use Kirby\Form\Field\EntriesField;
use Kirby\Form\Field\LayoutField;
use Kirby\Panel\Ui\FilePreviews\AudioFilePreview;
use Kirby\Panel\Ui\FilePreviews\ImageFilePreview;
use Kirby\Panel\Ui\FilePreviews\PdfFilePreview;
use Kirby\Panel\Ui\FilePreviews\VideoFilePreview;

/**
 * The Core class lists all parts of Kirby
 * that need to be loaded or initalized in order
 * to make the system work. Most core parts can
 * be overwritten by plugins.
 *
 * You can get such lists as kirbytags, components,
 * areas, etc. by accessing them through `$kirby->core()`
 *
 * I.e. `$kirby->core()->areas()`
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Core
{
	/**
	 * Optional override for the auto-detected index root
	 */
	public static string|null $indexRoot = null;

	protected array $cache = [];
	protected string $root;

	public function __construct(protected App $kirby)
	{
		$this->root = dirname(__DIR__, 2) . '/config';
	}

	/**
	 * Fetches the definition array of a particular area.
	 *
	 * This is a shortcut for `$kirby->core()->load()->area()`
	 * to give faster access to original area code in plugins.
	 */
	public function area(string $name): array|null
	{
		return $this->load()->area($name);
	}

	/**
	 * Returns a list of all paths to area definition files
	 *
	 * They are located in `/kirby/config/areas`
	 */
	public function areas(): array
	{
		return [
			'account'      => $this->root . '/areas/account.php',
			'installation' => $this->root . '/areas/installation.php',
			'lab'          => $this->root . '/areas/lab.php',
			'languages'    => $this->root . '/areas/languages.php',
			'login'        => $this->root . '/areas/login.php',
			'logout'       => $this->root . '/areas/logout.php',
			'search'       => $this->root . '/areas/search.php',
			'site'         => $this->root . '/areas/site.php',
			'system'       => $this->root . '/areas/system.php',
			'users'        => $this->root . '/areas/users.php',
		];
	}

	/**
	 * Returns a list of all default auth challenge classes
	 */
	public function authChallenges(): array
	{
		return [
			'email' => EmailChallenge::class,
			'totp'  => TotpChallenge::class,
		];
	}

	/**
	 * Returns a list of all paths to blueprint presets
	 *
	 * They are located in `/kirby/config/presets`
	 */
	public function blueprintPresets(): array
	{
		return [
			'pages' => $this->root . '/presets/pages.php',
			'page'  => $this->root . '/presets/page.php',
			'files' => $this->root . '/presets/files.php',
		];
	}

	/**
	 * Returns a list of paths to core blueprints or
	 * the blueprint in array form
	 *
	 * Block blueprints are located in `/kirby/config/blocks`
	 */
	public function blueprints(): array
	{
		return [
			// blocks
			'blocks/code'     => $this->root . '/blocks/code/code.yml',
			'blocks/gallery'  => $this->root . '/blocks/gallery/gallery.yml',
			'blocks/heading'  => $this->root . '/blocks/heading/heading.yml',
			'blocks/image'    => $this->root . '/blocks/image/image.yml',
			'blocks/line'     => $this->root . '/blocks/line/line.yml',
			'blocks/list'     => $this->root . '/blocks/list/list.yml',
			'blocks/markdown' => $this->root . '/blocks/markdown/markdown.yml',
			'blocks/quote'    => $this->root . '/blocks/quote/quote.yml',
			'blocks/table'    => $this->root . '/blocks/table/table.yml',
			'blocks/text'     => $this->root . '/blocks/text/text.yml',
			'blocks/video'    => $this->root . '/blocks/video/video.yml',

			// file blueprints
			'files/default' => ['title' => 'File'],

			// page blueprints
			'pages/default' => ['title' => 'Page'],

			// site blueprints
			'site' => [
				'title' => 'Site',
				'sections' => [
					'pages' => [
						'headline' => ['*' => 'pages'],
						'type'	   => 'pages'
					]
				]
			]
		];
	}

	/**
	 * Returns a list of all core caches
	 */
	public function caches(): array
	{
		return [
			'changes' => true,
			'updates' => true,
			'uuid'    => true,
		];
	}

	/**
	 * Returns a list of all cache driver classes
	 */
	public function cacheTypes(): array
	{
		return [
			'apcu'      => ApcuCache::class,
			'file'      => FileCache::class,
			'memcached' => MemCached::class,
			'memory'    => MemoryCache::class,
			'redis'     => RedisCache::class
		];
	}

	/**
	 * Returns an array of all core component functions
	 *
	 * The component functions can be found in
	 * `/kirby/config/components.php`
	 */
	public function components(): array
	{
		return $this->cache['components'] ??= include $this->root . '/components.php';
	}

	/**
	 * Returns a map of all field method aliases
	 */
	public function fieldMethodAliases(): array
	{
		return [
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
	}

	/**
	 * Returns an array of all field method functions
	 *
	 * Field methods are stored in `/kirby/config/methods.php`
	 */
	public function fieldMethods(): array
	{
		return $this->cache['fieldMethods'] ??= (include $this->root . '/methods.php')($this->kirby);
	}

	/**
	 * Returns an array of paths for field mixins
	 *
	 * They are located in `/kirby/config/fields/mixins`
	 */
	public function fieldMixins(): array
	{
		return [
			'datetime'   => $this->root . '/fields/mixins/datetime.php',
			'filepicker' => $this->root . '/fields/mixins/filepicker.php',
			'layout'     => $this->root . '/fields/mixins/layout.php',
			'min'        => $this->root . '/fields/mixins/min.php',
			'options'    => $this->root . '/fields/mixins/options.php',
			'pagepicker' => $this->root . '/fields/mixins/pagepicker.php',
			'picker'     => $this->root . '/fields/mixins/picker.php',
			'upload'     => $this->root . '/fields/mixins/upload.php',
			'userpicker' => $this->root . '/fields/mixins/userpicker.php',
		];
	}

	/**
	 * Returns an array of all paths and class names of panel fields
	 *
	 * Traditional panel fields are located in `/kirby/config/fields`
	 *
	 * The more complex field classes can be found in
	 * `/kirby/src/Form/Fields`
	 */
	public function fields(): array
	{
		return [
			'blocks'      => BlocksField::class,
			'checkboxes'  => $this->root . '/fields/checkboxes.php',
			'color'       => $this->root . '/fields/color.php',
			'date'        => $this->root . '/fields/date.php',
			'email'       => $this->root . '/fields/email.php',
			'entries'     => EntriesField::class,
			'files'       => $this->root . '/fields/files.php',
			'gap'         => $this->root . '/fields/gap.php',
			'headline'    => $this->root . '/fields/headline.php',
			'hidden'      => $this->root . '/fields/hidden.php',
			'info'        => $this->root . '/fields/info.php',
			'layout'      => LayoutField::class,
			'line'        => $this->root . '/fields/line.php',
			'link'        => $this->root . '/fields/link.php',
			'list'        => $this->root . '/fields/list.php',
			'multiselect' => $this->root . '/fields/multiselect.php',
			'number'      => $this->root . '/fields/number.php',
			'object'      => $this->root . '/fields/object.php',
			'pages'       => $this->root . '/fields/pages.php',
			'radio'       => $this->root . '/fields/radio.php',
			'range'       => $this->root . '/fields/range.php',
			'select'      => $this->root . '/fields/select.php',
			'slug'        => $this->root . '/fields/slug.php',
			'structure'   => $this->root . '/fields/structure.php',
			'tags'        => $this->root . '/fields/tags.php',
			'tel'         => $this->root . '/fields/tel.php',
			'text'        => $this->root . '/fields/text.php',
			'textarea'    => $this->root . '/fields/textarea.php',
			'time'        => $this->root . '/fields/time.php',
			'toggle'      => $this->root . '/fields/toggle.php',
			'toggles'     => $this->root . '/fields/toggles.php',
			'url'         => $this->root . '/fields/url.php',
			'users'       => $this->root . '/fields/users.php',
			'writer'      => $this->root . '/fields/writer.php'
		];
	}

	/**
	 * Returns a map of all default file preview handlers
	 */
	public function filePreviews(): array
	{
		return [
			AudioFilePreview::class,
			ImageFilePreview::class,
			PdfFilePreview::class,
			VideoFilePreview::class,
		];
	}

	/**
	 * Returns a map of all kirbytag aliases
	 */
	public function kirbyTagAliases(): array
	{
		return [
			'youtube' => 'video',
			'vimeo'   => 'video'
		];
	}

	/**
	 * Returns an array of all kirbytag definitions
	 *
	 * They are located in `/kirby/config/tags.php`
	 */
	public function kirbyTags(): array
	{
		return $this->cache['kirbytags'] ??= include $this->root . '/tags.php';
	}

	/**
	 * Loads a core part of Kirby
	 *
	 * The loader is set to not include plugins.
	 * This way, you can access original Kirby core code
	 * through this load method.
	 */
	public function load(): Loader
	{
		return new Loader($this->kirby, false);
	}

	/**
	 * Returns all absolute paths to important directories
	 *
	 * Roots are resolved and baked in `\Kirby\Cms\App::bakeRoots()`
	 */
	public function roots(): array
	{
		return $this->cache['roots'] ??= [
			'kirby'       => fn (array $roots) => dirname(__DIR__, 2),
			'i18n'        => fn (array $roots) => $roots['kirby'] . '/i18n',
			'i18n:translations' => fn (array $roots) => $roots['i18n'] . '/translations',
			'i18n:rules'  => fn (array $roots) => $roots['i18n'] . '/rules',

			'index'       => fn (array $roots) => static::$indexRoot ?? dirname(__DIR__, 3),
			'assets'      => fn (array $roots) => $roots['index'] . '/assets',
			'content'     => fn (array $roots) => $roots['index'] . '/content',
			'media'       => fn (array $roots) => $roots['index'] . '/media',
			'panel'       => fn (array $roots) => $roots['kirby'] . '/panel',
			'site'        => fn (array $roots) => $roots['index'] . '/site',
			'accounts'    => fn (array $roots) => $roots['site'] . '/accounts',
			'blueprints'  => fn (array $roots) => $roots['site'] . '/blueprints',
			'cache'       => fn (array $roots) => $roots['site'] . '/cache',
			'collections' => fn (array $roots) => $roots['site'] . '/collections',
			'commands'    => fn (array $roots) => $roots['site'] . '/commands',
			'config'      => fn (array $roots) => $roots['site'] . '/config',
			'controllers' => fn (array $roots) => $roots['site'] . '/controllers',
			'languages'   => fn (array $roots) => $roots['site'] . '/languages',
			'license'     => fn (array $roots) => $roots['config'] . '/.license',
			'logs'        => fn (array $roots) => $roots['site'] . '/logs',
			'models'      => fn (array $roots) => $roots['site'] . '/models',
			'plugins'     => fn (array $roots) => $roots['site'] . '/plugins',
			'sessions'    => fn (array $roots) => $roots['site'] . '/sessions',
			'snippets'    => fn (array $roots) => $roots['site'] . '/snippets',
			'templates'   => fn (array $roots) => $roots['site'] . '/templates',
			'roles'       => fn (array $roots) => $roots['blueprints'] . '/users',
		];
	}

	/**
	 * Returns an array of all routes for Kirby’s router
	 *
	 * Routes are split into `before` and `after` routes.
	 *
	 * Plugin routes will be injected inbetween.
	 */
	public function routes(): array
	{
		return $this->cache['routes'] ??= (include $this->root . '/routes.php')($this->kirby);
	}

	/**
	 * Returns a list of all paths to core block snippets
	 *
	 * They are located in `/kirby/config/blocks`
	 */
	public function snippets(): array
	{
		return [
			'blocks/code'     => $this->root . '/blocks/code/code.php',
			'blocks/gallery'  => $this->root . '/blocks/gallery/gallery.php',
			'blocks/heading'  => $this->root . '/blocks/heading/heading.php',
			'blocks/image'    => $this->root . '/blocks/image/image.php',
			'blocks/line'     => $this->root . '/blocks/line/line.php',
			'blocks/list'     => $this->root . '/blocks/list/list.php',
			'blocks/markdown' => $this->root . '/blocks/markdown/markdown.php',
			'blocks/quote'    => $this->root . '/blocks/quote/quote.php',
			'blocks/table'    => $this->root . '/blocks/table/table.php',
			'blocks/text'     => $this->root . '/blocks/text/text.php',
			'blocks/video'    => $this->root . '/blocks/video/video.php',
		];
	}

	/**
	 * Returns a list of paths to section mixins
	 *
	 * They are located in `/kirby/config/sections/mixins`
	 */
	public function sectionMixins(): array
	{
		return [
			'batch'      => $this->root . '/sections/mixins/batch.php',
			'details'    => $this->root . '/sections/mixins/details.php',
			'empty'      => $this->root . '/sections/mixins/empty.php',
			'headline'   => $this->root . '/sections/mixins/headline.php',
			'help'       => $this->root . '/sections/mixins/help.php',
			'layout'     => $this->root . '/sections/mixins/layout.php',
			'max'        => $this->root . '/sections/mixins/max.php',
			'min'        => $this->root . '/sections/mixins/min.php',
			'pagination' => $this->root . '/sections/mixins/pagination.php',
			'parent'     => $this->root . '/sections/mixins/parent.php',
			'search'     => $this->root . '/sections/mixins/search.php',
			'sort'        => $this->root . '/sections/mixins/sort.php',
		];
	}

	/**
	 * Returns a list of all section definitions
	 *
	 * They are located in `/kirby/config/sections`
	 */
	public function sections(): array
	{
		return [
			'fields' => $this->root . '/sections/fields.php',
			'files'  => $this->root . '/sections/files.php',
			'info'   => $this->root . '/sections/info.php',
			'pages'  => $this->root . '/sections/pages.php',
			'stats'  => $this->root . '/sections/stats.php',
		];
	}

	/**
	 * Returns a list of paths to all system templates
	 *
	 * They are located in `/kirby/config/templates`
	 */
	public function templates(): array
	{
		return [
			'emails/auth/login'          => $this->root . '/templates/emails/auth/login.php',
			'emails/auth/password-reset' => $this->root . '/templates/emails/auth/password-reset.php'
		];
	}

	/**
	 * Returns an array with all system URLs
	 *
	 * URLs are resolved and baked in `\Kirby\Cms\App::bakeUrls()`
	 */
	public function urls(): array
	{
		return $this->cache['urls'] ??= [
			'index'   => fn () => $this->kirby->environment()->baseUrl(),
			'base'    => fn (array $urls) => rtrim($urls['index'], '/'),
			'current' => function (array $urls) {
				$path = trim($this->kirby->path(), '/');

				if (empty($path) === true) {
					return $urls['index'];
				}

				return $urls['base'] . '/' . $path;
			},
			'assets' => fn (array $urls) => $urls['base'] . '/assets',
			'api'    => fn (array $urls) => $urls['base'] . '/' . $this->kirby->option('api.slug', 'api'),
			'media'  => fn (array $urls) => $urls['base'] . '/media',
			'panel'  => fn (array $urls) => $urls['base'] . '/' . $this->kirby->option('panel.slug', 'panel')
		];
	}
}
