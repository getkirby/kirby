<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Url as CmsUrl;
use Kirby\Cms\User;
use Kirby\Exception\PermissionException;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Throwable;

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
	/**
	 * Collect all registered areas
	 */
	public static function areas(): Areas
	{
		return new Areas();
	}

	/**
	 * Check for access permissions
	 */
	public static function firewall(
		User|null $user = null,
		string|null $areaId = null
	): bool {
		// a user has to be logged in
		if ($user === null) {
			throw new PermissionException(
				key: 'access.panel'
			);
		}

		// get all access permissions for the user role
		$permissions = $user->role()->permissions()->toArray()['access'];

		// check for general panel access
		if (($permissions['panel'] ?? true) !== true) {
			throw new PermissionException(
				key: 'access.panel'
			);
		}

		// don't check if the area is not defined
		if (empty($areaId) === true) {
			return true;
		}

		// undefined area permissions means access
		if (isset($permissions[$areaId]) === false) {
			return true;
		}

		// no access
		if ($permissions[$areaId] !== true) {
			throw new PermissionException(
				key: 'access.view'
			);
		}

		return true;
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
	 */
	public static function hasAccess(
		User|null $user = null,
		string|null $area = null
	): bool {
		try {
			static::firewall($user, $area);
			return true;
		} catch (Throwable) {
			return false;
		}
	}

	/**
	 * Returns the Panel home instance
	 * @since 6.0.0
	 */
	public static function home(): Home
	{
		return new Home();
	}

	/**
	 * Checks for a Panel request
	 * via get parameters or headers
	 */
	public static function isStateRequest(): bool
	{
		$request = App::instance()->request();

		if ($request->method() === 'GET') {
			return
				(bool)($request->get('_json') ??
				$request->header('X-Panel'));
		}

		return false;
	}

	/**
	 * Checks if the given URL is a Panel Url
	 * @since 6.0.0
	 */
	public static function isPanelUrl(string $url): bool
	{
		$panel = App::instance()->url('panel');
		return Str::startsWith($url, $panel);
	}

	/**
	 * Returns a JSON response
	 * for State calls
	 */
	public static function json(array $data, int $code = 200): Response
	{
		$request = App::instance()->request();

		return Response::json($data, $code, $request->get('_pretty'), [
			'X-Panel'       => 'true',
			'Cache-Control' => 'no-store, private'
		]);
	}

	/**
	 * Checks for a multilanguage installation
	 */
	public static function multilang(): bool
	{
		// multilang setup check
		$kirby = App::instance();
		return $kirby->option('languages') || $kirby->multilang();
	}

	/**
	 * Returns the path after /panel/ which can then
	 * be used in the router or to find a matching view
	 * @since 6.0.0
	 */
	public static function path(string $url): string|null
	{
		$after = Str::after($url, App::instance()->url('panel'));
		return trim($after, '/');
	}

	/**
	 * Returns the referrer path if present
	 */
	public static function referrer(): string
	{
		$request = App::instance()->request();

		$referrer = $request->header('X-Panel-Referrer')
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

		// Set the translation for Panel UI before
		// gathering areas and routes, so that the
		// `t()` helper can already be used
		static::setTranslation();

		// Set the content language in multi-lang installations
		static::setLanguage();

		return (new Router())->execute($path);
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
			$path  = trim($url ?? '', '/');

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
