<?php

namespace Kirby\Panel;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
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
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Home
{
    /**
     * @return string
     */
    public static function alternative(): string
    {
        $user = kirby()->user();

        if (!$user) {
            return Panel::url('login');
        }

        $permissions = $user->role()->permissions();

        // no access to the panel? The only good alternative is the main url
        if ($permissions->for('access', 'panel') === false) {
            return site()->url();
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
     * @return string
     */
    public static function custom(): string
    {
        $user = kirby()->user();

        if (!$user) {
            return Panel::url('login');
        }

        return $user->panel()->home();
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function hasAccess(string $path): bool
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
            return router($path, 'GET', $routes, function ($route) {
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
                return Panel::hasAccess(kirby()->user(), $areaId);
            });
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param \Kirby\Http\Uri $uri
     * @return bool
     */
    public static function hasValidDomain(Uri $uri): bool
    {
        return $uri->domain() === (new Uri(site()->url()))->domain();
    }

    /**
     * @param string $url
     * @return bool
     */
    public static function isPanelUrl(string $url): bool
    {
        return Str::startsWith($url, kirby()->url('panel'));
    }

    /**
     * @param string $url
     * @return string|null
     */
    public static function panelPath(string $url): ?string
    {
        $after = Str::after($url, kirby()->url('panel'));
        return trim($after, '/');
    }

    /**
     * @return string|null
     */
    public static function remembered(): ?string
    {
        // check for a stored path after login
        $remembered = kirby()->session()->pull('panel.path');

        // convert the result to an absolute URL if available
        return $remembered ? Panel::url($remembered) : null;
    }

    /**
     * @return string
     */
    public static function url(): string
    {
        $url = static::remembered() ?? static::custom();

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

        // when the user is already signed in, a redirect to login, logout or
        // installation views would lead to an infinite redirect loop
        if (in_array($path, ['', 'login', 'logout', 'installation'], true) === true) {
            if (kirby()->user()) {
                throw new InvalidArgumentException('Invalid redirect URL');
            }
        }

        // Check if the user can access the URL
        if (static::hasAccess($path) === true) {
            return $url;
        }

        // Try to find an alternative
        return static::alternative();
    }
}
