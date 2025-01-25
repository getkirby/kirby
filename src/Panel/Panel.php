<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Url;
use Kirby\Http\Response;
use Kirby\Http\Uri;
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
	protected Areas $areas;
	protected Home $home;

	public function __construct(
		protected App $kirby
	) {
	}

	/**
	 * All registered Panel areas
	 * @since 5.0.0
	 */
	public function areas(): Areas
	{
		return $this->areas ??= new Areas();
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

	public function home(): Home
	{
		return $this->home ??= new Home($this);
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
	 * Checks if the given URL is a Panel Url
	 */
	public static function isPanelUrl(string $url): bool
	{
		$panel = App::instance()->url('panel');
		return Str::startsWith($url, $panel);
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
	public function multilang(): bool
	{
		return $this->kirby->option('languages') || $this->kirby->multilang();
	}

	/**
	 * Returns the path after /panel/ which can then
	 * be used in the router or to find a matching view
	 */
	public static function path(string $url): string|null
	{
		$after = Str::after($url, App::instance()->url('panel'));
		return trim($after, '/');
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
	public function router(string|null $path = null): Response|null
	{
		if ($this->kirby->option('panel') === false) {
			return null;
		}

		// set the translation for Panel UI before
		// gathering areas and routes, so that the
		// `t()` helper can already be used
		$this->setTranslation();

		// set the language in multi-lang installations
		$this->setLanguage();

		return (new Router($this))->execute($path);
	}

	/**
	 * Set the current language in multi-lang
	 * installations based on the session or the
	 * query language query parameter
	 */
	public function setLanguage(): string|null
	{
		// language switcher
		if ($this->multilang() === true) {
			$fallback = 'en';

			if ($defaultLanguage = $this->kirby->defaultLanguage()) {
				$fallback = $defaultLanguage->code();
			}

			$session         = $this->kirby->session();
			$sessionLanguage = $session->get('panel.language', $fallback);
			$language        = $this->kirby->request()->get('language') ?? $sessionLanguage;

			// keep the language for the next visit
			if ($language !== $sessionLanguage) {
				$session->set('panel.language', $language);
			}

			// activate the current language in Kirby
			$this->kirby->setCurrentLanguage($language);

			return $language;
		}

		return null;
	}

	/**
	 * Set the currently active Panel translation
	 * based on the current user or config
	 */
	public function setTranslation(): string
	{
		// use the user language for the default translation or
		// fall back to the language from the config
		$translation =
			$this->kirby->user()?->language() ??
			$this->kirby->panelLanguage();

		$this->kirby->setCurrentTranslation($translation);

		return $translation;
	}

	/**
	 * Creates an absolute Panel URL
	 * independent of the Panel slug config
	 */
	public static function url(
		string|null $url = null,
		array $options = []
	): string {
		// only touch relative paths
		if (Url::isAbsolute($url) === false) {
			$kirby = App::instance();
			$slug  = $kirby->option('panel.slug', 'panel');
			$path  = trim($url, '/');

			$baseUri  = new Uri($kirby->url());
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
			$url = Url::to($path, $options);
		}

		return $url;
	}
}
