<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Url;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;

/**
 * The Assets class collects all js, css, icons and other
 * files for the Panel. It pushes them into the media folder
 * on demand and also makes sure to create proper asset URLs
 * depending on dev mode
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 */
class Assets
{
	protected bool $dev;
	protected App $kirby;
	protected string $nonce;
	protected Plugins $plugins;
	protected string $url;
	protected bool $vite;

	public function __construct()
	{
		$this->kirby   = App::instance();
		$this->nonce   = $this->kirby->nonce();
		$this->plugins = new Plugins();

		$vite       = $this->kirby->roots()->panel() . '/.vite-running';
		$this->vite = is_file($vite) === true;

		// get the assets from the Vite dev server in dev mode;
		// dev mode = explicitly enabled in the config AND Vite is running
		$dev       = $this->kirby->option('panel.dev', false);
		$this->dev = $dev !== false && $this->vite === true;

		// get the base URL
		$this->url = $this->url();
	}

	/**
	 * Get all CSS files
	 */
	public function css(): array
	{
		$css = [
			'index'   => $this->url . '/css/style.min.css',
			'plugins' => $this->plugins->url('css'),
			...$this->custom('panel.css')
		];

		// during dev mode we do not need to load
		// the general stylesheet (as styling will be inlined)
		if ($this->dev === true) {
			$css['index'] = null;
		}

		return array_filter($css);
	}

	/**
	 * Check for a custom asset file from the
	 * config (e.g. panel.css or panel.js)
	 */
	public function custom(string $option): array
	{
		$customs = [];

		if ($assets = $this->kirby->option($option)) {
			$assets  = A::wrap($assets);

			foreach ($assets as $index => $path) {
				if (Url::isAbsolute($path) === true) {
					$customs['custom-' . $index] = $path;
					continue;
				}

				$asset = new Asset($path);

				if ($asset->exists() === true) {
					$customs['custom-' . $index] =  $asset->url() . '?' . $asset->modified();
				}
			}
		}

		return $customs;
	}

	/**
	 * Generates an array with all assets
	 * that need to be loaded for the panel (js, css, icons)
	 */
	public function external(): array
	{
		return [
			'css'            => $this->css(),
			'icons'          => $this->favicons(),
			// loader for plugins' index.dev.mjs files – inlined,
			// so we provide the code instead of the asset URL
			'plugin-imports' => $this->plugins->read('mjs'),
			'js'             => $this->js()
		];
	}

	/**
	 * Returns array of favicon icons
	 * based on config option
	 *
	 * @todo Deprecate `url` option in v5, use `href` option instead
	 * @todo Deprecate `rel` usage as array key in v5, use `rel` option instead
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function favicons(): array
	{
		$icons = $this->kirby->option('panel.favicon', [
			[
				'rel'   => 'apple-touch-icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/apple-touch-icon.png'
			],
			[
				'rel'   => 'alternate icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/favicon.png'
			],
			[
				'rel'   => 'shortcut icon',
				'type'  => 'image/svg+xml',
				'href'  => $this->url . '/favicon.svg'
			],
			[
				'rel'   => 'apple-touch-icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/apple-touch-icon-dark.png',
				'media' => '(prefers-color-scheme: dark)'
			],
			[
				'rel'   => 'alternate icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/favicon-dark.png',
				'media' => '(prefers-color-scheme: dark)'
			]
		]);

		if (is_array($icons) === true) {
			// normalize options
			foreach ($icons as $rel => &$icon) {
				// TODO: remove this backward compatibility check in v6
				if (isset($icon['url']) === true) {
					$icon['href'] = $icon['url'];
					unset($icon['url']);
				}

				// TODO: remove this backward compatibility check in v6
				if (is_string($rel) === true && isset($icon['rel']) === false) {
					$icon['rel'] = $rel;
				}

				$icon['href']  = Url::to($icon['href']);
				$icon['nonce'] = $this->nonce;
			}

			return array_values($icons);
		}

		// make sure to convert favicon string to array
		if (is_string($icons) === true) {
			return [
				[
					'rel'   => 'shortcut icon',
					'type'  => F::mime($icons),
					'href'  => Url::to($icons),
					'nonce' => $this->nonce
				]
			];
		}

		throw new InvalidArgumentException('Invalid panel.favicon option');
	}

	/**
	 * Load the SVG icon sprite
	 * This will be injected in the
	 * initial HTML document for the Panel
	 */
	public function icons(): string
	{
		$dir   = $this->kirby->root('panel') . '/';
		$dir  .= $this->dev ? 'public' : 'dist';
		$icons = F::read($dir . '/img/icons.svg');
		$icons = preg_replace('/<!--(.|\s)*?-->/', '', $icons);
		return $icons;
	}

	/**
	 * Get all js files
	 */
	public function js(): array
	{
		$js = [
			'vue' => [
				'nonce' => $this->nonce,
				'src'   => $this->vue(),
			],
			'vendor' => [
				'nonce' => $this->nonce,
				'src'   => $this->url . '/js/vendor.min.js',
				'type'  => 'module'
			],
			'pluginloader' => [
				'nonce' => $this->nonce,
				'src'   => $this->url . '/js/plugins.js',
				'type'  => 'module'
			],
			'plugins' => [
				'nonce' => $this->nonce,
				'src'   => $this->plugins->url('js'),
				'defer' => true
			],
			...A::map($this->custom('panel.js'), fn ($src) => [
				'nonce' => $this->nonce,
				'src'   => $src,
				'type'  => 'module'
			]),
			'index' => [
				'nonce' => $this->nonce,
				'src'   => $this->url . '/js/index.min.js',
				'type'  => 'module'
			],
		];


		// during dev mode, add vite client and adapt
		// path to `index.js` - vendor does not need
		// to be loaded in dev mode
		if ($this->dev === true) {
			// load the non-minified index.js, remove vendor script and
			//  development version of Vue
			$js['vendor']['src'] = null;
			$js['index']['src']  = $this->url . '/src/index.js';
			$js['vue']['src']    = $this->vue(production: false);

			// add vite dev client
			$js['vite'] = [
				'nonce' => $this->nonce,
				'src'   => $this->url . '/@vite/client',
				'type'  => 'module'
			];
		}

		return array_filter($js, fn ($js) => empty($js['src']) === false);
	}

	/**
	 * Links all dist files in the media folder
	 * and returns the link to the requested asset
	 *
	 * @throws \Kirby\Exception\Exception If Panel assets could not be moved to the public directory
	 */
	public function link(): bool
	{
		$mediaRoot   = $this->kirby->root('media') . '/panel';
		$panelRoot   = $this->kirby->root('panel') . '/dist';
		$versionHash = $this->kirby->versionHash();
		$versionRoot = $mediaRoot . '/' . $versionHash;

		// check if the version already exists
		if (is_dir($versionRoot) === true) {
			return false;
		}

		// delete the panel folder and all previous versions
		Dir::remove($mediaRoot);

		// recreate the panel folder
		Dir::make($mediaRoot, true);

		// copy assets to the dist folder
		if (Dir::copy($panelRoot, $versionRoot) !== true) {
			throw new Exception('Panel assets could not be linked');
		}

		return true;
	}

	/**
	 * Get the base URL for all assets depending on dev mode
	 */
	public function url(): string
	{
		// vite is not running, use production assets
		if ($this->dev === false) {
			return $this->kirby->url('media') . '/panel/' . $this->kirby->versionHash();
		}

		// explicitly configured base URL
		$dev = $this->kirby->option('panel.dev');
		if (is_string($dev) === true) {
			return $dev;
		}

		// port 3000 of the current Kirby request
		return rtrim($this->kirby->request()->url([
			'port'   => 3000,
			'path'   => null,
			'params' => null,
			'query'  => null
		])->toString(), '/');
	}

	/**
	 * Get the correct Vue script URL depending on dev mode
	 * and the enabled/disabled template compiler
	 */
	public function vue(bool $production = true): string
	{
		$script = $this->kirby->option('panel.vue.compiler', true) === true ? 'vue' : 'vue.runtime';

		if ($production === false) {
			return $this->url . '/node_modules/vue/dist/' . $script . '.js';
		}

		return $this->url . '/js/' . $script . '.min.js';
	}
}
