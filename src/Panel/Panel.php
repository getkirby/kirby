<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Url as CmsUrl;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;

/**
 * The Panel class is only responsible to create
 * a working panel view with all the right URLs
 * and other panel options. The view template is
 * located in `kirby/views/panel.php`
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.6.0
 */
class Panel
{
	protected Access $access;
	protected Areas $areas;
	protected Home $home;
	protected Router $router;

	public function __construct(
		protected App $kirby
	) {
	}

	/**
	 * Returns the Panel Access object
	 * @since 6.0.0
	 */
	public function access(): Access
	{
		return $this->access ??= new Access($this);
	}

	/**
	 * Collect all registered areas
	 */
	public function areas(): Areas
	{
		return $this->areas ??= Areas::for($this->kirby);
	}

	/**
	 * Redirect to a Panel url
	 *
	 * @throws \Kirby\Panel\Redirect
	 * @codeCoverageIgnore
	 */
	public static function go(string|null $url = null, int $code = 302, int|false $refresh = false): void
	{
		throw new Redirect(App::instance()->panel()->url($url), $code, $refresh);
	}

	/**
	 * Returns the Panel home instance
	 * @since 6.0.0
	 */
	public function home(): Home
	{
		return $this->home ??= new Home($this);
	}

	/**
	 * Checks for a Panel request via get parameters or headers
	 */
	public function isStateRequest(): bool
	{
		$request = $this->kirby->request();

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
	public function isPanelUrl(string $url): bool
	{
		return Str::startsWith($url, $this->kirby->url('panel'));
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
	 * Returns the Kirby instance
	 * @since 6.0.0
	 */
	public function kirby(): App
	{
		return $this->kirby;
	}

	/**
	 * Returns the Panel menu object
	 * @since 6.0.0
	 */
	public function menu(string|null $current = null): Menu
	{
		return new Menu(
			areas: $this->areas(),
			permissions: $this->kirby->user()?->role()->permissions()->toArray() ?? [],
			current: $current
		);
	}

	/**
	 * Checks for a multilanguage installation
	 */
	public function multilang(): bool
	{
		return $this->kirby->option('languages') || $this->kirby->multilang();
	}

	/**
	 * Returns the path after /panel/ which can then
	 * be used in the router or to find a matching view
	 * @since 6.0.0
	 */
	public function path(string $url): string|null
	{
		$after = Str::after($url, $this->kirby->url('panel'));
		return trim($after, '/');
	}

	/**
	 * Returns the referrer path if present
	 */
	public function referrer(): string
	{
		$request = $this->kirby->request();

		$referrer = $request->header('X-Panel-Referrer')
				 ?? $request->get('_referrer')
				 ?? '';

		return '/' . trim($referrer, '/');
	}

	/**
	 * Router for the Panel views
	 */
	public function router(): Router|null
	{
		if ($this->kirby->option('panel') === false) {
			return null;
		}

		return $this->router ??= new Router($this);
	}

	/**
	 * Creates an absolute Panel URL
	 * independent of the Panel slug config
	 */
	public function url(string|null $url = null, array $options = []): string
	{
		// only touch relative paths
		if (Url::isAbsolute($url) === false) {
			$slug  = $this->kirby->option('panel.slug', 'panel');
			$path  = trim($url ?? '', '/');

			$baseUri  = new Uri($this->kirby->url());
			$basePath = trim($baseUri->path()->toString(), '/');

			// removes base path if relative path contains it
			if (
				empty($basePath) === false &&
				Str::startsWith($path, $basePath) === true
			) {
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
