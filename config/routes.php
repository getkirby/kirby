<?php

use Kirby\Cms\App;
use Kirby\Cms\Cors;
use Kirby\Cms\LanguageRoutes;
use Kirby\Cms\Media;
use Kirby\Http\Response;
use Kirby\Panel\Panel;
use Kirby\Panel\Plugins;
use Kirby\Plugin\Assets;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

return function (App $kirby) {
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
		// handle CORS preflight requests
		[
			'pattern' => '(:all)',
			'method'  => 'OPTIONS',
			'action'  => function () use ($kirby) {
				$request = $kirby->request();

				// skip if no CORS request
				if ($request->header('Access-Control-Request-Method') === null) {
					/** @var \Kirby\Http\Route $this */
					$this->next();
				}

				// skip if CORS is disabled
				if ($kirby->isCorsEnabled() === false) {
					/** @var \Kirby\Http\Route $this */
					$this->next();
				}

				$headers = Cors::headers(preflight: true);

				// block if origin doesn't match
				if ($headers === []) {
					return null;
				}

				return new Response('', null, 204, $headers);
			}
		],
		[
			'pattern' => $api . '/(:all)',
			'method'  => 'ALL',
			'env'     => 'api',
			'action'  => function (string|null $path = null) use ($kirby) {
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
			// TODO: change to '/plugins/(:any)/(:any)/(:any)/(:all)' once
			// the hash is made mandatory
			'pattern' => $media . '/plugins/(:any)/(:any)/(?:(:any)/)?(:all)',
			'env'     => 'media',
			'action'  => function (
				string $provider,
				string $pluginName,
				string $hash,
				string $path
			) {
				return Assets::resolve(
					$provider . '/' . $pluginName,
					$hash,
					$path
				);
			}
		],
		[
			'pattern' => $media . '/pages/(:all)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $path,
				string $hash,
				string $filename
			) use ($kirby) {
				return Media::link($kirby->page($path), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/site/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $hash,
				string $filename
			) use ($kirby) {
				return Media::link($kirby->site(), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/users/(:any)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $id,
				string $hash,
				string $filename
			) use ($kirby) {
				return Media::link($kirby->user($id), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/assets/(:all)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $path,
				string $hash,
				string $filename
			) {
				return Media::thumb($path, $hash, $filename);
			}
		],
		[
			'pattern' => $panel . '/(:all?)',
			'method'  => 'ALL',
			'env'     => 'panel',
			'action'  => function (string|null $path = null) {
				return Panel::router($path);
			}
		],
		// permalinks for page/file UUIDs
		[
			'pattern' => '@/(page|file)/(:all)',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function (string $type, string $id) use ($kirby) {
				// try to resolve to model, but only from UUID cache;
				// this ensures that only existing UUIDs can be queried
				// and attackers can't force Kirby to go through the whole
				// site index with a non-existing UUID
				if ($model = Uuid::for($type . '://' . $id)?->model(true)) {
					return $kirby
						->response()
						->redirect($model->url());
				}

				// render the error page
				return false;
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
