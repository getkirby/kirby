<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\Url;
use Kirby\Cms\Page;
use Kirby\Util\Str;

class Link extends \Kirby\Text\Tags\Link
{

    use Dependencies;

    protected $relatedPage;

    protected function text(): string
    {
        if ($text = $this->attr('text')) {
            return $text;
        }

        if ($relatedPage = $this->relatedPage()) {
            return $relatedPage->title();
        }

        return $this->link();
    }

    protected function link(): string
    {
        $link = parent::link();

        if ($relatedPage = $this->relatedPage()) {
            return $relatedPage->url();
        }

        return Url::to($link);
    }

    protected function relatedPage()
    {
        if (is_a($this->relatedPage, Page::class) === true) {
            return $this->relatedPage;
        }

        if ($this->relatedPage === false) {
            return false;
        }

        $link = parent::link();

        // no need to check absolute urls
        if (Url::isAbsolute($link) === true || Str::startsWith($link, '#') === true) {
            return $this->relatedPage = false;
        }

        // trim all slashes for the page search
        $link = trim(parent::link(), '/');

        if ($page = $this->site()->find($link)) {
            return $this->relatedPage = $page;
        }

        return $this->relatedPage = false;
    }

}
