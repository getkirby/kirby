<?php

namespace Kirby\Panel;

use Kirby\Api\Upload;
use Kirby\Cms\App;
use Kirby\Cms\Url as CmsUrl;
use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Panel\Router;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * The Panel class is only responsible to create
 * a working panel view with all the right URLs
 * and other panel options. The view template is
 * located in `kirby/views/panel.php`
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Panel
{
	public function __construct(
		protected App $kirby
	) {
	}

	/**
	 * Normalize a panel area
	 */
	public static function area(string $id, array $area): array
	{
		$area['id']                = $id;
		$area['label']           ??= $id;
		$area['breadcrumb']      ??= [];
		$area['breadcrumbLabel'] ??= $area['label'];
		$area['title']             = $area['label'];
		$area['menu']            ??= false;
		$area['link']            ??= $id;
		$area['search']          ??= null;

		return $area;
	}

	/**
	 * Collect all registered areas
	 */
	public static function areas(): array
	{
		$kirby  = App::instance();
		$system = $kirby->system();
		$user   = $kirby->user();
		$areas  = $kirby->load()->areas();

		// the system is not ready
		if (
			$system->isOk() === false ||
			$system->isInstalled() === false
		) {
			return [
				'installation' => static::area(
					'installation',
					$areas['installation']
				),
			];
		}

		// not yet authenticated
		if (!$user) {
			return [
				'logout' => static::area('logout', $areas['logout']),
				// login area last because it defines a fallback route
				'login'  => static::area('login', $areas['login']),
			];
		}

		unset($areas['installation'], $areas['login']);

		// Disable the language area for single-language installations
		// This does not check for installed languages. Otherwise you'd
		// not be able to add the first language through the view
		if (!$kirby->option('languages')) {
			unset($areas['languages']);
		}

		$result = [];

		foreach ($areas as $id => $area) {
			$result[$id] = static::area($id, $area);
		}

		return $result;
	}

	/**
	 * Collect all registered buttons from areas
	 * @since 5.0.0
	 */
	public static function buttons(): array
	{
		return array_merge(...array_values(
			A::map(
				Panel::areas(),
				fn ($area) => $area['buttons'] ?? []
			)
		));
	}

	/**
	 * Check for access permissions
	 * @deprecated 5.0.0 Use `Panel\Access:has(throws: true)` instead
	 * @codeCoverageIgnore
	 */
	public static function firewall(
		User|null $user = null,
		string|null $areaId = null
	): bool {
		return Access::has($user, $areaId, throws: true);
	}

	/**
	 * Garbage collection which runs with a probability
	 * of 10% on each Panel request
	 *
	 * @since 5.0.0
	 * @codeCoverageIgnore
	 */
	protected static function garbage(): void
	{
		// run garbage collection with a chance of 10%;
		if (mt_rand(1, 10000) <= 0.1 * 10000) {
			// clean up leftover upload chunks
			Upload::cleanTmpDir();
		}
	}

	/**
	 * Redirect to a Panel url
	 *
	 * @throws \Kirby\Panel\Redirect
	 * @codeCoverageIgnore
	 */
	public static function go(string|null $url = null, int $code = 302): void
	{
		throw new Redirect(static::url($url), $code);
	}

	/**
	 * Check if the given user has access to the panel
	 * or to a given area
	 *  @deprecated 5.0.0 Use `Panel\Access:has(throws: false)` instead
	 * @codeCoverageIgnore
	 */
	public static function hasAccess(
		User|null $user = null,
		string|null $area = null
	): bool {
		return Access::has($user, $area);
	}

	/**
	 * Checks for a Fiber request
	 * via get parameters or headers
	 */
	public static function isFiberRequest(): bool
	{
		$request = App::instance()->request();

		if ($request->method() === 'GET') {
			return
				(bool)($request->get('_json') ??
				$request->header('X-Fiber'));
		}

		return false;
	}

	/**
	 * Returns a JSON response
	 * for Fiber calls
	 */
	public static function json(array $data, int $code = 200): Response
	{
		$request = App::instance()->request();

		return Response::json($data, $code, $request->get('_pretty'), [
			'X-Fiber'       => 'true',
			'Cache-Control' => 'no-store, private'
		]);
	}

	/**
	 * Checks for a multi-language installation
	 */
	public static function multilang(): bool
	{
		// multilang setup check
		$kirby = App::instance();
		return $kirby->option('languages') || $kirby->multilang();
	}

	/**
	 * Returns the referrer path if present
	 */
	public function referrer(): string
	{
		$request  = $this->kirby->request();
		$referrer = $request->header('X-Fiber-Referrer')
				 ?? $request->get('_referrer')
				 ?? '';

		return '/' . trim($referrer, '/');
	}

	/**
	 * Router for the Panel views
	 */
	public static function router(string|null $path = null): Response|null
	{
		if (App::instance()->option('panel') === false) {
			return null;
		}

		// run garbage collection
		static::garbage();

		// set the translation for Panel UI before
		// gathering areas and routes, so that the
		// `t()` helper can already be used
		static::setTranslation();

		// set the language in multi-lang installations
		static::setLanguage();

		$router = new Router(areas: static::areas());
		return $router->call($path);
	}

	/**
	 * Set the current language in multi-lang
	 * installations based on the session or the
	 * query language query parameter
	 */
	public static function setLanguage(): string|null
	{
		$kirby = App::instance();

		// language switcher
		if (static::multilang()) {
			$fallback = 'en';

			if ($defaultLanguage = $kirby->defaultLanguage()) {
				$fallback = $defaultLanguage->code();
			}

			$session         = $kirby->session();
			$sessionLanguage = $session->get('panel.language', $fallback);
			$language        = $kirby->request()->get('language') ?? $sessionLanguage;

			// keep the language for the next visit
			if ($language !== $sessionLanguage) {
				$session->set('panel.language', $language);
			}

			// activate the current language in Kirby
			$kirby->setCurrentLanguage($language);

			return $language;
		}

		return null;
	}

	/**
	 * Set the currently active Panel translation
	 * based on the current user or config
	 */
	public static function setTranslation(): string
	{
		$kirby = App::instance();

		// use the user language for the default translation or
		// fall back to the language from the config
		$translation = $kirby->user()?->language() ??
						$kirby->panelLanguage();

		$kirby->setCurrentTranslation($translation);

		return $translation;
	}

	/**
	 * Creates an absolute Panel URL
	 * independent of the Panel slug config
	 */
	public static function url(string|null $url = null, array $options = []): string
	{
		// only touch relative paths
		if (Url::isAbsolute($url) === false) {
			$kirby = App::instance();
			$slug  = $kirby->option('panel.slug', 'panel');
			$path  = trim($url, '/');

			$baseUri  = new Uri($kirby->url());
			$basePath = trim($baseUri->path()->toString(), '/');

			// removes base path if relative path contains it
			if (empty($basePath) === false && Str::startsWith($path, $basePath) === true) {
				$path = Str::after($path, $basePath);
			}
			// add the panel slug prefix if it it's not
			// included in the path yet
			elseif (Str::startsWith($path, $slug . '/') === false) {
				$path = $slug . '/' . $path;
			}

			// create an absolute URL
			$url = CmsUrl::to($path, $options);
		}

		return $url;
	}
}
