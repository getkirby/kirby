<?php

namespace Kirby\Panel;

use Kirby\Api\Upload;
use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Menu;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\Str;

/**
 * Bundles all the Fiber data to be sent to the Panel
 * in as much of a lazy manner than possible
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
class Fiber
{
	protected App $kirby;
	protected bool $multilang;
	protected Language|null $language;
	protected array $permissions;
	protected User|null $user;

	public function __construct(
		protected array $view = [],
		protected Area|null $area = null,
		protected array $areas = [],
	) {
		$this->kirby       = App::instance();
		$this->multilang   = $this->kirby->panel()->multilang();
		$this->language    = $this->kirby->language();
		$this->user        = $this->kirby->user();
		$this->permissions = $this->user?->role()->permissions()->toArray() ?? [];
	}

	public function config(): array
	{
		return [
			'api'         => [
				'methodOverwrite' => $this->kirby->option('api.methodOverwrite', true)
			],
			'debug'       => $this->kirby->option('debug', false),
			'kirbytext'   => $this->kirby->option('panel.kirbytext', true),
			'translation' => $this->kirby->option('panel.language', 'en'),
			'upload'      => Upload::chunkSize(),
		];
	}

	/**
	 * Creates the shared data array for the individual views
	 * The full shared data is always sent on every JSON and
	 * full document request unless the `X-Fiber-Only` header or
	 * the `_only` query parameter is set.
	 */
	public function data(): array
	{
		// shared data for all requests
		return [
			'direction'   => $this->direction(...),
			'dialog'      => null,
			'drawer'      => null,
			'language'    => $this->language(...),
			'languages'   => $this->languages(...),
			'menu'        => $this->menu(...),
			'permissions' => $this->permissions(...),
			'license'     => $this->license(...),
			'multilang'   => $this->multilang(...),
			'searches'    => $this->searches(...),
			'url'         => $this->url(...),
			'user'        => $this->user(...),
			'view'        => $this->view(...)
		];
	}

	public function direction(): string|null
	{
		if ($this->multilang === true && $this->language && $this->user) {
			$default = $this->kirby->defaultLanguage();

			if (
				$this->language->direction() !== $default->direction() &&
				$this->language->code() !== $this->user->language()
			) {
				return $this->language->direction();
			}
		}

		return null;
	}

	/**
	 * Filters the data array based on headers or  query parameters.
	 *
	 * This way, JSON requests can tailor the returned data to
	 * only include certain data fields.
	 *
	 * This can be activated with the `X-Fiber-Only` header or
	 * the `_only` query parameter in a request.
	 *
	 * Globals are normally only loaded with the full document request.
	 * In addition, they can be requested via the `X-Fiber-Globals` header
	 * or the `_globals` query parameter.
	 */
	public function filter(array $data): array
	{
		$result = [];

		// requested data ids
		$request  = $this->kirby->request();
		$filter   = $request->header('X-Fiber-Only');
		$filter ??= $request->get('_only');

		if (empty($filter) === false) {
			// split include string into an array of fields
			$keys = Str::split($filter, ',');

			// take care of potentially requested globals
			$globals     = $this->globals();
			$keysEntries = A::map($keys, fn ($key) => Str::split($key, '.')[0]);

			// check if the keys from `_only` include any global id as entry
			if (array_intersect($keysEntries, array_keys($globals)) !== []) {
				$data = array_merge_recursive($globals, $data);
			}

			// make sure the data is already resolved to make
			// nested data fetching work
			$data = A::apply($data);

			// build a new array with all requested data
			foreach ($keys as $key) {
				$result[$key] = A::get($data, $key);
			}

			// Nest dotted keys in array but ignore $translation
			return A::nest($result, ['translation']);
		}

		$filterGlobals   = $request->header('X-Fiber-Globals');
		$filterGlobals ??= $request->get('_globals');

		if (empty($filterGlobals) === false) {
			// split globals string into an array of fields
			$keys = Str::split($filterGlobals, ',');

			// add requested globals
			$globals = $this->globals();

			foreach ($keys as $key) {
				if (isset($globals[$key]) === true) {
					$data[$key] = $globals[$key];
				}
			}

			return A::apply($data);
		}

		return A::apply($data);
	}

	/**
	 * Creates global data for the Panel.
	 * This will be injected in the full Panel
	 * view via the script tag. Global data
	 * is only requested once on the first page load.
	 * It can be loaded partially later if needed,
	 * but is otherwise not included in Fiber calls.
	 */
	public function globals(): array
	{
		return [
			'config'      => $this->config(...),
			'system'      => $this->system(...),
			'translation' => $this->translation(...),
			'urls'        => $this->urls(...)
		];
	}

	public function language(): array|null
	{
		if ($this->multilang === false) {
			return null;
		}

		return $this->language?->toArray();
	}

	public function languages(): array
	{
		if ($this->multilang === false) {
			return [];
		}

		return $this->kirby->languages()->values(
			fn ($language) => $language->toArray()
		);
	}

	public function license(): string
	{
		return $this->kirby->system()->license()->status()->value();
	}

	public function menu(): array
	{
		$menu = new Menu(
			areas:       $this->areas,
			permissions: $this->permissions,
			current:     $this->area?->id()
		);

		return $menu->render();
	}

	public function multilang(): bool
	{
		return $this->multilang;
	}

	/**
	 * Returns the permissions array for the current user
	 */
	public function permissions(): array
	{
		return $this->permissions;
	}

	public function searches(): array
	{
		$searches = [];

		foreach ($this->areas as $area) {
			// by default, all areas are accessible unless
			// the permissions are explicitly set to false
			if ($area->isAccessible($this->permissions) !== false) {
				foreach ($area->searches() as $id => $params) {
					$searches[$id] = [
						'icon'  => $params['icon'] ?? 'search',
						'label' => $params['label'] ?? Str::ucfirst($id),
						'id'    => $id
					];
				}
			}
		}
		return $searches;
	}

	public function system(): array
	{
		$locales = [];

		foreach ($this->kirby->translations() as $translation) {
			$locales[$translation->code()] = $translation->locale();
		}

		return [
			'ascii'   => Str::$ascii,
			'csrf'    => $this->kirby->auth()->csrfFromSession(),
			'isLocal' => $this->kirby->system()->isLocal(),
			'locales' => $locales,
			'slugs'   => Str::$language,
			'title'   => $this->kirby->site()->title()->or('Kirby Panel')->toString()
		];
	}

	public function toArray(bool $globals = true): array
	{
		// get all data for the request
		$data = $this->data();

		// if requested, send only non-global data
		if ($globals === false) {
			// filter data, if only globals headers or
			// query parameters are requested
			return $this->filter($data);
		}

		// load globals for the full document response
		$globals = $this->globals();

		// resolve and merge globals and shared data
		return array_merge_recursive(
			A::apply($globals),
			A::apply($data)
		);
	}

	public function translation(): array
	{
		$language = match ($user = $this->kirby->user()) {
			null    => $this->kirby->panelLanguage(),
			default => $user->language()
		};

		$translation = $this->kirby->translation($language);

		return [
			'code'      => $translation->code(),
			'data'      => $translation->dataWithFallback(),
			'direction' => $translation->direction(),
			'name'      => $translation->name(),
			'weekday'   => Date::firstWeekday($translation->locale())
		];
	}

	public function url(): string
	{
		return $this->kirby->request()->url()->toString();
	}

	public function urls(): array
	{
		return [
			'api'  => $this->kirby->url('api'),
			'site' => $this->kirby->url('index')
		];
	}

	public function user(): array|null
	{
		if ($this->user) {
			return [
				'email'    => $this->user->email(),
				'id'       => $this->user->id(),
				'language' => $this->user->language(),
				'role'     => $this->user->role()->id(),
				'username' => $this->user->username(),
			];
		}

		return null;
	}

	public function view(): array
	{
		$defaults = [
			'breadcrumb' => [],
			'code'       => 200,
			'path'       => Str::after($this->kirby->path(), '/'),
			'props'      => [],
			'query'      => $this->kirby->request()->query()->toArray(),
			'referrer'   => $this->kirby->panel()->referrer(),
			'search'     => $this->kirby->option('panel.search.type', 'pages'),
			'timestamp'  => (int)(microtime(true) * 1000),
		];

		$view = array_replace_recursive(
			$defaults,
			$this->area?->toView() ?? [],
			$this->view
		);

		// resolve all callbacks in the view array
		return A::apply($view);
	}
}
