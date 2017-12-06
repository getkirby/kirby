<?php

namespace Kirby\Cms;

use Kirby\Pagination\Pagination as BasePagination;
use Kirby\Http\Url;

/**
 * The extended Pagination class handles
 * URLs in addition to the pagination features
 * from Kirby\Pagination\Pagination
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Pagination extends BasePagination
{

    /**
     * The base URL
     *
     * @var string
     */
    protected $url;

    /**
     * Creates the pagination object. As a new
     * property you can now pass the base Url.
     * That Url must be the Url of the first
     * page of the collection without additional
     * pagination information/query parameters in it.
     *
     * ```php
     * $pagination = new Pagination([
     *     'page'  => 1,
     *     'limit' => 10,
     *     'total' => 120,
     *     'url'   => 'https://getkirby.com/blog'
     * ]);
     * ```
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->url = $params['url'] ?? '';
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

        if ($page === 1) {
            return $this->url;
        }

        if ($this->hasPage($page) === true) {
            // TODO: add url builder here to avoid breaking the queries
            return $this->url . '?page=' . $page;
        }

        return null;
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
