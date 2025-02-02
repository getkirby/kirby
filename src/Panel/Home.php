<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Http\Uri;
use Kirby\Panel\Router as PanelRouter;
use Kirby\Panel\Ui\Menu;
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
	protected App $kirby;
	protected User|null $user;

	public function __construct(
		protected Panel $panel
	) {
		$this->kirby = App::instance();
		$this->user  = $this->kirby->user();
	}

	/**
	 * Returns an alternative URL if access
	 * to the first choice is blocked.
	 *
	 * It will go through the entire menu and
	 * take the first area which is not disabled
	 * or locked in other ways
	 */
	public function alternative(): string
	{
		$permissions = $this->user->role()->permissions();

		// no access to the panel? The only good alternative is the main url
		if ($permissions->for('access', 'panel') === false) {
			return $this->kirby->site()->url();
		}

		// needed to create a proper menu
		$areas = $this->kirby->panel()->areas()->toArray();
		$menu  = new Menu(areas: $areas, permissions: $permissions->toArray());
		$menu  = $menu->items();

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

			// skip buttons that don't open a link
			// (but e.g. a dialog)
			if (isset($menuItem['link']) === false) {
				continue;
			}

			// skip the logout button
			if ($menuItem['link'] === 'logout') {
				continue;
			}

			return Panel::url($menuItem['link']);
		}

		throw new NotFoundException(
			message: 'There’s no available Panel page to redirect to'
		);
	}

	/**
	 * Checks if the current user has access to the given
	 * Panel path. This is quite tricky, because we
	 * need to call a trimmed down router to check
	 * for available routes and their firewall status.
	 */
	public function hasAccess(string $path): bool
	{
		$routes = PanelRouter::routes(
			areas: $this->panel->areas()->toArray()
		);

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
			return Router::execute($path, 'GET', $routes, function ($route) {
				$attrs  = $route->attributes();
				$auth   = $attrs['auth'] ?? true;
				$areaId = $attrs['area'] ?? null;
				$type   = $attrs['type'] ?? 'view';

				// only allow redirects to views
				if ($type !== 'view') {
					return false;
				}

				// if auth is not required the redirect is allowed
				if ($auth === false) {
					return true;
				}

				// check the firewall
				return Access::has($this->user, $areaId);
			});
		} catch (Throwable) {
			return false;
		}
	}

	/**
	 * Checks if the given Uri has the same domain
	 * as the index URL of the Kirby installation.
	 * This is used to block external URLs to third-party
	 * domains as redirect options.
	 */
	public function hasValidDomain(Uri $uri): bool
	{
		$rootUrl = $this->kirby->site()->url();
		$rootUri = new Uri($rootUrl);
		return $uri->domain() === $rootUri->domain();
	}

	/**
	 * Returns the Url that has been stored in the session
	 * before the last logout. We take this Url if possible
	 * to redirect the user back to the last point where they
	 * left before they got logged out.
	 */
	public static function remembered(): string|null
	{
		// check for a stored path after login
		if ($remembered = App::instance()->session()->pull('panel.path')) {
			// convert the result to an absolute URL if available
			return Panel::url($remembered);
		}

		return null;
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
	 */
	public function url(): string
	{
		// if there's no authenticated user, all internal
		// redirects will be blocked and the user is redirected
		// to the login instead
		if ($this->user === null) {
			return Panel::url('login');
		}

		// get the last visited url from the session or the custom home
		$url = $this->remembered() ?? $this->user->panel()->home();

		// inspect the given URL
		$uri = new Uri($url);

		// compare domains to avoid external redirects
		if ($this->hasValidDomain($uri) !== true) {
			throw new InvalidArgumentException(
				message: 'External URLs are not allowed for Panel redirects'
			);
		}

		// remove all params to avoid
		// possible attack vectors
		$uri->params = '';
		$uri->query  = '';

		// get a clean version of the URL
		$url = $uri->toString();

		// Don't further inspect URLs outside of the Panel
		if (Panel::isPanelUrl($url) === false) {
			return $url;
		}

		// get the plain panel path
		$path = Panel::path($url);

		// a redirect to login, logout or installation
		// views would lead to an infinite redirect loop
		$loops = ['', 'login', 'logout', 'installation'];

		if (in_array($path, $loops, true) === true) {
			$path = 'site';
		}

		// Check if the user can access the URL
		if ($this->hasAccess($path) === true) {
			return Panel::url($path);
		}

		// Try to find an alternative
		return $this->alternative();
	}
}
