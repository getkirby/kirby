<?php

namespace Kirby\Panel;

use Kirby\Api\Upload;
use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The View response class handles Fiber
 * requests to render either a JSON object
 * or a full HTML document for Panel views
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class View
{
	/**
	 * Filters the data array based on headers or
	 * query parameters. Requests can return only
	 * certain data fields that way or globals can
	 * be injected on demand.
	 */
	public static function apply(array $data): array
	{
		$request = App::instance()->request();
		$only    = $request->header('X-Fiber-Only') ?? $request->get('_only');

		if (empty($only) === false) {
			return static::applyOnly($data, $only);
		}

		$globals =
			$request->header('X-Fiber-Globals') ??
			$request->get('_globals');

		if (empty($globals) === false) {
			return static::applyGlobals($data, $globals);
		}

		return A::apply($data);
	}

	/**
	 * Checks if globals should be included in a JSON Fiber request. They are normally
	 * only loaded with the full document request, but sometimes need to be updated.
	 *
	 * A global request can be activated with the `X-Fiber-Globals` header or the
	 * `_globals` query parameter.
	 */
	public static function applyGlobals(
		array $data,
		string|null $globals = null
	): array {
		// split globals string into an array of fields
		$globalKeys = Str::split($globals, ',');

		// add requested globals
		if ($globalKeys === []) {
			return $data;
		}

		$globals = static::globals();

		foreach ($globalKeys as $globalKey) {
			if (isset($globals[$globalKey]) === true) {
				$data[$globalKey] = $globals[$globalKey];
			}
		}

		// merge with shared data
		return A::apply($data);
	}

	/**
	 * Checks if the request should only return a limited
	 * set of data. This can be activated with the `X-Fiber-Only`
	 * header or the `_only` query parameter in a request.
	 *
	 * Such requests can fetch shared data or globals.
	 * Globals will be loaded on demand.
	 */
	public static function applyOnly(
		array $data,
		string|null $only = null
	): array {
		// split include string into an array of fields
		$onlyKeys = Str::split($only, ',');

		// if a full request is made, return all data
		if ($onlyKeys === []) {
			return $data;
		}

		// otherwise filter data based on
		// dot notation, e.g. `$props.tab.columns`
		$result = [];

		// check if globals are requested and need to be merged
		if (Str::contains($only, '$')) {
			$data = array_merge_recursive(static::globals(), $data);
		}

		// make sure the data is already resolved to make
		// nested data fetching work
		$data = A::apply($data);

		// build a new array with all requested data
		foreach ($onlyKeys as $onlyKey) {
			$result[$onlyKey] = A::get($data, $onlyKey);
		}

		// Nest dotted keys in array but ignore $translation
		return A::nest($result, ['$translation']);
	}

	/**
	 * Creates the shared data array for the individual views
	 * The full shared data is always sent on every JSON and
	 * full document request unless the `X-Fiber-Only` header or
	 * the `_only` query parameter is set.
	 */
	public static function data(array $view = [], array $options = []): array
	{
		$kirby = App::instance();

		// multilang setup check
		$multilang = Panel::multilang();

		// get the authenticated user
		$user = $kirby->user();

		// user permissions
		$permissions = $user?->role()->permissions()->toArray() ?? [];

		// current content language
		$language = $kirby->language();

		// shared data for all requests
		return [
			'$direction' => function () use ($kirby, $multilang, $language, $user) {
				if ($multilang === true && $language && $user) {
					$default = $kirby->defaultLanguage();

					if (
						$language->direction() !== $default->direction() &&
						$language->code() !== $user->language()
					) {
						return $language->direction();
					}
				}
			},
			'$dialog'   => null,
			'$drawer'   => null,
			'$language' => fn () => match ($multilang) {
				false => null,
				true  => $language?->toArray()
			},
			'$languages' => fn (): array => match ($multilang) {
				false => [],
				true  => $kirby->languages()->values(
					fn ($language) => $language->toArray()
				)
			},
			'$menu'       => function () use ($options, $permissions) {
				$menu = new Menu(
					$options['areas'] ?? [],
					$permissions,
					$options['area']['id'] ?? null
				);
				return $menu->entries();
			},
			'$permissions' => $permissions,
			'$license'     => $kirby->system()->license()->status()->value(),
			'$multilang'   => $multilang,
			'$searches'    => static::searches($options['areas'] ?? [], $permissions),
			'$url'         => $kirby->request()->url()->toString(),
			'$user'        => fn () => match ($user) {
				null    => null,
				default =>  [
					'email'    => $user->email(),
					'id'       => $user->id(),
					'language' => $user->language(),
					'role'     => $user->role()->id(),
					'username' => $user->username(),
				]
			},
			'$view' => function () use ($kirby, $options, $view) {
				$defaults = [
					'breadcrumb' => [],
					'code'       => 200,
					'path'       => Str::after($kirby->path(), '/'),
					'props'      => [],
					'query'      => App::instance()->request()->query()->toArray(),
					'referrer'   => Panel::referrer(),
					'search'     => $kirby->option('panel.search.type', 'pages'),
					'timestamp'  => (int)(microtime(true) * 1000),
				];

				$view = array_replace_recursive(
					$defaults,
					$options['area'] ?? [],
					$view
				);

				// make sure that views and dialogs are gone
				unset(
					$view['buttons'],
					$view['dialogs'],
					$view['drawers'],
					$view['dropdowns'],
					$view['requests'],
					$view['searches'],
					$view['views']
				);

				// resolve all callbacks in the view array
				return A::apply($view);
			}
		];
	}

	/**
	 * Renders the error view with provided message
	 */
	public static function error(string $message, int $code = 404, array $details = [])
	{
		$response = [
			...Json::error($message, $code, $details),
			'component' => 'k-error-view',
			'props'     => [
				'error'  => $message,
				'layout' => Panel::hasAccess(App::instance()->user()) ? 'inside' : 'outside'
			],
			'title' => 'Error'
		];

		ksort($response);

		return $response;
	}

	/**
	 * Creates global data for the Panel.
	 * This will be injected in the full Panel
	 * view via the script tag. Global data
	 * is only requested once on the first page load.
	 * It can be loaded partially later if needed,
	 * but is otherwise not included in Fiber calls.
	 */
	public static function globals(): array
	{
		$kirby = App::instance();

		return [
			'$config' => fn () => [
				'api'         => [
					'methodOverride' => $kirby->option('api.methodOverride', true)
				],
				'debug'       => $kirby->option('debug', false),
				'kirbytext'   => $kirby->option('panel.kirbytext', true),
				'theme'       => $kirby->option('panel.theme', 'system'),
				'translation' => $kirby->option('panel.language', 'en'),
				'upload'      => Upload::chunkSize(),
			],
			'$system' => function () use ($kirby) {
				$locales = [];

				foreach ($kirby->translations() as $translation) {
					$locales[$translation->code()] = $translation->locale();
				}

				return [
					'ascii'   => Str::$ascii,
					'csrf'    => $kirby->auth()->csrfFromSession(),
					'isLocal' => $kirby->system()->isLocal(),
					'locales' => $locales,
					'slugs'   => Str::$language,
					'title'   => $kirby->site()->title()->or('Kirby Panel')->toString()
				];
			},
			'$translation' => function () use ($kirby) {
				$translation = match ($user = $kirby->user()) {
					null    => $kirby->translation($kirby->panelLanguage()),
					default => $kirby->translation($user->language())
				};

				return [
					'code'      => $translation->code(),
					'data'      => $translation->dataWithFallback(),
					'direction' => $translation->direction(),
					'name'      => $translation->name(),
					'weekday'   => Date::firstWeekday($translation->locale())
				];
			},
			'$urls' => fn () => [
				'api'  => $kirby->url('api'),
				'site' => $kirby->url('index')
			]
		];
	}

	/**
	 * Renders the main panel view either as
	 * JSON response or full HTML document based
	 * on the request header or query params
	 */
	public static function response($data, array $options = []): Response
	{
		// handle redirects
		if ($data instanceof Redirect) {
			// if the redirect is a refresh, return a refresh response
			if ($data->refresh() !== false) {
				return Response::refresh($data->location(), $data->code(), $data->refresh());
			}

			return Response::redirect($data->location(), $data->code());
		}

		// handle Kirby exceptions
		if ($data instanceof Exception) {
			$data = static::error($data->getMessage(), $data->getHttpCode(), $data->getDetails());

		// handle regular exceptions
		} elseif ($data instanceof Throwable) {
			$data = static::error($data->getMessage(), 500);

		// only expect arrays from here on
		} elseif (is_array($data) === false) {
			$data = static::error('Invalid Panel response', 500);
		}

		// get all data for the request
		$fiber = static::data($data, $options);

		// if requested, send $fiber data as JSON
		if (Panel::isFiberRequest() === true) {
			// filter data, if only or globals headers or
			// query parameters are set
			$fiber = static::apply($fiber);

			return Panel::json($fiber, $fiber['$view']['code'] ?? 200);
		}

		// load globals for the full document response
		$globals = static::globals();

		// resolve and merge globals and shared data
		$fiber = array_merge_recursive(A::apply($globals), A::apply($fiber));

		// render the full HTML document
		return Document::response($fiber);
	}

	public static function searches(array $areas, array $permissions): array
	{
		$searches = [];

		foreach ($areas as $areaId => $area) {
			// by default, all areas are accessible unless
			// the permissions are explicitly set to false
			if (($permissions['access'][$areaId] ?? true) !== false) {
				foreach ($area['searches'] ?? [] as $id => $params) {
					$searches[$id] = [
						'icon'  => $params['icon'] ?? 'search',
						'label' => $params['label'] ?? Str::label($id),
						'id'    => $id
					];
				}
			}
		}
		return $searches;
	}
}
