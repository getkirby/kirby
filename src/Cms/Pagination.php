<?php

namespace Kirby\Cms;

use Kirby\Pagination\Pagination as BasePagination;
use Kirby\Http\Url;

class Pagination extends BasePagination
{

    protected $url;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->url = $params['url'] ?? '';
    }

    public function pageUrl(int $page = null): string
    {
        if ($page === null) {
            return $this->pageUrl($this->page());
        }

        return $this->url . '?page=' . $page;
    }

    public function prevPageUrl(): string
    {
        if ($page = $this->prevPage()) {
            return $this->pageUrl($page);
        }

        return $this->url;
    }

    public function nextPageUrl(): string
    {
        if ($page = $this->nextPage()) {
            return $this->pageUrl($page);
        }

        return $this->url;
    }

    public function firstPageUrl(): string
    {
        return $this->pageUrl(1);
    }

    public function lastPageUrl(): string
    {
        return $this->pageUrl($this->lastPage());
    }

}
