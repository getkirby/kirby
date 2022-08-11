<?php

use Kirby\Cms\LanguageRoutes;
use Kirby\Cms\Media;
use Kirby\Cms\PluginAssets;
use Kirby\Panel\Panel;
use Kirby\Panel\Plugins;
use Kirby\Toolkit\Str;

return function ($kirby) {
	$api   = $kirby->option('api.slug', 'api');
	$panel = $kirby->option('panel.slug', 'panel');
	$index = $kirby->url('index');
	$media = $kirby->url('media');

	if (Str::startsWith($media, $index) === true) {
		$media = Str::after($media, $index);
	} else {
		// media URL is outside of the site, we can't make routing work;
		// fall back to the standard media route
		$media = 'media';
	}

	/**
	 * Before routes are running before the
	 * plugin routes and cannot be overwritten by
	 * plugins.
	 */
	$before = [
		[
			'pattern' => $api . '/(:all)',
			'method'  => 'ALL',
			'env'     => 'api',
			'action'  => function ($path = null) use ($kirby) {
				if ($kirby->option('api') === false) {
					return null;
				}

				$request = $kirby->request();

				return $kirby->api()->render($path, $this->method(), [
					'body'    => $request->body()->toArray(),
					'files'   => $request->files()->toArray(),
					'headers' => $request->headers(),
					'query'   => $request->query()->toArray(),
				]);
			}
		],
		[
			'pattern' => $media . '/plugins/index.(css|js)',
			'env'     => 'media',
			'action'  => function (string $type) use ($kirby) {
				$plugins = new Plugins();

				return $kirby
					->response()
					->type($type)
					->body($plugins->read($type));
			}
		],
		[
			'pattern' => $media . '/plugins/(:any)/(:any)/(:all).(css|map|gif|js|mjs|jpg|png|svg|webp|avif|woff2|woff|json)',
			'env'     => 'media',
			'action'  => function (string $provider, string $pluginName, string $filename, string $extension) {
				return PluginAssets::resolve($provider . '/' . $pluginName, $filename . '.' . $extension);
			}
		],
		[
			'pattern' => $media . '/pages/(:all)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function ($path, $hash, $filename) use ($kirby) {
				return Media::link($kirby->page($path), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/site/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function ($hash, $filename) use ($kirby) {
				return Media::link($kirby->site(), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/users/(:any)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function ($id, $hash, $filename) use ($kirby) {
				return Media::link($kirby->user($id), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/assets/(:all)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function ($path, $hash, $filename) {
				return Media::thumb($path, $hash, $filename);
			}
		],
		[
			'pattern' => $panel . '/(:all?)',
			'method'  => 'ALL',
			'env'     => 'panel',
			'action'  => function ($path = null) {
				return Panel::router($path);
			}
		],
	];

	// Multi-language setup
	if ($kirby->multilang() === true) {
		$after = LanguageRoutes::create($kirby);
	} else {

		// Single-language home
		$after[] = [
			'pattern' => '',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function () use ($kirby) {
				return $kirby->resolve();
			}
		];

		// redirect the home page folder to the real homepage
		$after[] = [
			'pattern' => $kirby->option('home', 'home'),
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function () use ($kirby) {
				return $kirby
					->response()
					->redirect($kirby->site()->url());
			}
		];

		// Single-language subpages
		$after[] = [
			'pattern' => '(:all)',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function (string $path) use ($kirby) {
				return $kirby->resolve($path);
			}
		];
	}

	return [
		'before' => $before,
		'after'  => $after
	];
};
