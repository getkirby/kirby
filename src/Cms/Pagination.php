<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Pagination as BasePagination;

/**
 * The extended Pagination class handles
 * URLs in addition to the pagination features
 * from Kirby\Toolkit\Pagination
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Pagination extends BasePagination
{

    /**
     * Pagination method (param or query)
     *
     * @var string
     */
    protected $method;

    /**
     * The base URL
     *
     * @var string
     */
    protected $url;

    /**
     * Variable name for query strings
     *
     * @var string
     */
    protected $variable;

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
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $kirby   = App::instance();
        $config  = $kirby->option('pagination', []);
        $request = $kirby->request();

        $params['limit']    = $params['limit']    ?? $config['limit']    ?? 20;
        $params['method']   = $params['method']   ?? $config['method']   ?? 'param';
        $params['variable'] = $params['variable'] ?? $config['variable'] ?? 'page';
        $params['url']      = $params['url']      ?? $request->url();

        if ($params['method'] === 'query') {
            $params['page'] = $params['page'] ?? $request->url()->query()->get($params['variable'], 1);
        } else {
            $params['page'] = $params['page'] ?? $request->url()->params()->get($params['variable'], 1);
        }

        parent::__construct($params);

        $this->method   = $params['method'];
        $this->url      = $params['url'];
        $this->variable = $params['variable'];
    }

    /**
     * Returns the Url for the first page
     *
     * @return string
     */
    public function firstPageUrl(): string
    {
        return $this->pageUrl(1);
    }

    /**
     * Returns the Url for the last page
     *
     * @return string
     */
    public function lastPageUrl(): string
    {
        return $this->pageUrl($this->lastPage());
    }

    /**
     * Returns the Url for the next page.
     * Returns null if there's no next page.
     *
     * @return string
     */
    public function nextPageUrl()
    {
        if ($page = $this->nextPage()) {
            return $this->pageUrl($page);
        }

        return null;
    }

    /**
     * Returns the Url of the current page.
     * If the $page variable is set, the Url
     * for that page will be returned.
     *
     * @return string|null
     */
    public function pageUrl(int $page = null)
    {
        if ($page === null) {
            return $this->pageUrl($this->page());
        }

        $url      = clone $this->url;
        $variable = $this->variable;

        if ($this->hasPage($page) === false) {
            return null;
        }

        $pageValue = $page === 1 ? null : $page;

        if ($this->method === 'query') {
            $url->query->$variable = $pageValue;
        } else {
            $url->params->$variable = $pageValue;
        }

        return $url->toString();
    }

    /**
     * Returns the Url for the previous page.
     * Returns null if there's no previous page.
     *
     * @return string
     */
    public function prevPageUrl()
    {
        if ($page = $this->prevPage()) {
            return $this->pageUrl($page);
        }

        return null;
    }
}
