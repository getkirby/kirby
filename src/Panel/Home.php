<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Http\Uri;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The Home class creates the secure redirect
 * URL after logins. The URL can either come
 * from the session to remember the last view
 * before the automatic logout, or from a user
 * blueprint to redirect to custom views.
 *
 * The Home class also makes sure to check access
 * before a redirect happens and avoids redirects
 * to inaccessible views.
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Home
{
	/**
	 * Returns an alternative URL if access
	 * to the first choice is blocked.
	 *
	 * It will go through the entire menu and
	 * take the first area which is not disabled
	 * or locked in other ways
	 *
	 * @param \Kirby\Cms\User $user
	 * @return string
	 */
	public static function alternative(User $user): string
	{
		$permissions = $user->role()->permissions();

		// no access to the panel? The only good alternative is the main url
		if ($permissions->for('access', 'panel') === false) {
			return App::instance()->site()->url();
		}

		// needed to create a proper menu
		$areas = Panel::areas();
		$menu  = View::menu($areas, $permissions->toArray());

		// go through the menu and search for the first
		// available view we can go to
		foreach ($menu as $menuItem) {
			// skip separators
			if ($menuItem === '-') {
				continue;
			}

			// skip disabled items
			if (($menuItem['disabled'] ?? false) === true) {
				continue;
			}

			// skip the logout button
			if ($menuItem['id'] === 'logout') {
				continue;
			}


			return Panel::url($menuItem['link']);
		}

		throw new NotFoundException('There’s no available Panel page to redirect to');
	}

	/**
	 * Checks if the user has access to the given
	 * panel path. This is quite tricky, because we
	 * need to call a trimmed down router to check
	 * for available routes and their firewall status.
	 *
	 * @param \Kirby\Cms\User
	 * @param string $path
	 * @return bool
	 */
	public static function hasAccess(User $user, string $path): bool
	{
		$areas  = Panel::areas();
		$routes = Panel::routes($areas);

		// Remove fallback routes. Otherwise a route
		// would be found even if the view does
		// not exist at all.
		foreach ($routes as $index => $route) {
			if ($route['pattern'] === '(:all)') {
				unset($routes[$index]);
			}
		}

		// create a dummy router to check if we can access this route at all
		try {
			return Router::execute($path, 'GET', $routes, function ($route) use ($user) {
				$auth   = $route->attributes()['auth'] ?? true;
				$areaId = $route->attributes()['area'] ?? null;
				$type   = $route->attributes()['type'] ?? 'view';

				// only allow redirects to views
				if ($type !== 'view') {
					return false;
				}

				// if auth is not required the redirect is allowed
				if ($auth === false) {
					return true;
				}

				// check the firewall
				return Panel::hasAccess($user, $areaId);
			});
		} catch (Throwable $e) {
			return false;
		}
	}

	/**
	 * Checks if the given Uri has the same domain
	 * as the index URL of the Kirby installation.
	 * This is used to block external URLs to third-party
	 * domains as redirect options.
	 *
	 * @param \Kirby\Http\Uri $uri
	 * @return bool
	 */
	public static function hasValidDomain(Uri $uri): bool
	{
		$rootUrl = App::instance()->site()->url();
		return $uri->domain() === (new Uri($rootUrl))->domain();
	}

	/**
	 * Checks if the given URL is a Panel Url.
	 *
	 * @param string $url
	 * @return bool
	 */
	public static function isPanelUrl(string $url): bool
	{
		return Str::startsWith($url, App::instance()->url('panel'));
	}

	/**
	 * Returns the path after /panel/ which can then
	 * be used in the router or to find a matching view
	 *
	 * @param string $url
	 * @return string|null
	 */
	public static function panelPath(string $url): ?string
	{
		$after = Str::after($url, App::instance()->url('panel'));
		return trim($after, '/');
	}

	/**
	 * Returns the Url that has been stored in the session
	 * before the last logout. We take this Url if possible
	 * to redirect the user back to the last point where they
	 * left before they got logged out.
	 *
	 * @return string|null
	 */
	public static function remembered(): ?string
	{
		// check for a stored path after login
		$remembered = App::instance()->session()->pull('panel.path');

		// convert the result to an absolute URL if available
		return $remembered ? Panel::url($remembered) : null;
	}

	/**
	 * Tries to find the best possible Url to redirect
	 * the user to after the login.
	 *
	 * When the user got logged out, we try to send them back
	 * to the point where they left.
	 *
	 * If they have a custom redirect Url defined in their blueprint
	 * via the `home` option, we send them there if no Url is stored
	 * in the session.
	 *
	 * If none of the options above find any result, we try to send
	 * them to the site view.
	 *
	 * Before the redirect happens, the final Url is sanitized, the query
	 * and params are removed to avoid any attacks and the domain is compared
	 * to avoid redirects to external Urls.
	 *
	 * Afterwards, we also check for permissions before the redirect happens
	 * to avoid redirects to inaccessible Panel views. In such a case
	 * the next best accessible view is picked from the menu.
	 *
	 * @return string
	 */
	public static function url(): string
	{
		$user = App::instance()->user();

		// if there's no authenticated user, all internal
		// redirects will be blocked and the user is redirected
		// to the login instead
		if (!$user) {
			return Panel::url('login');
		}

		// get the last visited url from the session or the custom home
		$url = static::remembered() ?? $user->panel()->home();

		// inspect the given URL
		$uri = new Uri($url);

		// compare domains to avoid external redirects
		if (static::hasValidDomain($uri) !== true) {
			throw new InvalidArgumentException('External URLs are not allowed for Panel redirects');
		}

		// remove all params to avoid
		// possible attack vectors
		$uri->params = '';
		$uri->query  = '';

		// get a clean version of the URL
		$url = $uri->toString();

		// Don't further inspect URLs outside of the Panel
		if (static::isPanelUrl($url) === false) {
			return $url;
		}

		// get the plain panel path
		$path = static::panelPath($url);

		// a redirect to login, logout or installation
		// views would lead to an infinite redirect loop
		if (in_array($path, ['', 'login', 'logout', 'installation'], true) === true) {
			$path = 'site';
		}

		// Check if the user can access the URL
		if (static::hasAccess($user, $path) === true) {
			return Panel::url($path);
		}

		// Try to find an alternative
		return static::alternative($user);
	}
}
