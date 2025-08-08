<?php

namespace Kirby\Cms;

use Kirby\Http\Uri;
use Kirby\Toolkit\Pagination as BasePagination;

/**
 * The `$pagination` object divides
 * a collection of pages, files etc.
 * into discrete pages consisting of
 * the number of defined items. The
 * pagination object can then be used
 * to navigate between these pages,
 * create a navigation etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Pagination extends BasePagination
{
	/**
	 * Pagination method (param, query, none)
	 */
	protected string $method;

	/**
	 * The base URL
	 */
	protected Uri $url;

	/**
	 * Variable name for query strings
	 */
	protected string $variable;

	/**
	 * Creates the pagination object. As a new
	 * property you can now pass the base Url.
	 * That Url must be the Url of the first
	 * page of the collection without additional
	 * pagination information/query parameters in it.
	 *
	 * ```php
	 * $pagination = new Pagination([
	 *     'page'     => 1,
	 *     'limit'    => 10,
	 *     'total'    => 120,
	 *     'method'   => 'query',
	 *     'variable' => 'p',
	 *     'url'      => new Uri('https://getkirby.com/blog')
	 * ]);
	 * ```
	 */
	public function __construct(array $params = [])
	{
		$kirby   = App::instance();
		$config  = $kirby->option('pagination', []);
		$request = $kirby->request();

		$params['limit']    ??= $config['limit']    ?? 20;
		$params['method']   ??= $config['method']   ?? 'param';
		$params['variable'] ??= $config['variable'] ?? 'page';

		if (empty($params['url']) === true) {
			$params['url'] = new Uri($kirby->url('current'), [
				'params' => $request->params(),
				'query'  => $request->query()->toArray(),
			]);
		}

		$params['page'] ??= match ($params['method']) {
			'query' => $params['url']->query()->get($params['variable']),
			'param' => $params['url']->params()->get($params['variable']),
			default => null
		};

		parent::__construct($params);

		$this->method   = $params['method'];
		$this->url      = $params['url'];
		$this->variable = $params['variable'];
	}

	/**
	 * Returns the Url for the first page
	 */
	public function firstPageUrl(): string|null
	{
		return $this->pageUrl(1);
	}

	/**
	 * Returns the Url for the last page
	 */
	public function lastPageUrl(): string|null
	{
		return $this->pageUrl($this->lastPage());
	}

	/**
	 * Returns the Url for the next page.
	 * Returns null if there's no next page.
	 */
	public function nextPageUrl(): string|null
	{
		if ($page = $this->nextPage()) {
			return $this->pageUrl($page);
		}

		return null;
	}

	/**
	 * Returns the URL of the current page.
	 * If the `$page` variable is set, the URL
	 * for that page will be returned.
	 */
	public function pageUrl(int|null $page = null): string|null
	{
		if ($page === null) {
			return $this->pageUrl($this->page());
		}

		$url      = clone $this->url;
		$variable = $this->variable;

		if (
			$this->hasPage($page) === false ||
			in_array($this->method, ['query', 'param'], true) === false
		) {
			return null;
		}

		if ($page === 1) {
			$page = null;
		}

		match ($this->method) {
			'query' => $url->query()->$variable  = $page,
			'param' => $url->params()->$variable = $page
		};

		return $url->toString();
	}

	/**
	 * Returns the Url for the previous page.
	 * Returns null if there's no previous page.
	 */
	public function prevPageUrl(): string|null
	{
		if ($page = $this->prevPage()) {
			return $this->pageUrl($page);
		}

		return null;
	}
}
