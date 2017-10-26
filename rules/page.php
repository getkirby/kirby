<?php

use Kirby\Cms\Page;

return [
    'page.change.slug' => function (Page $page, string $slug) {

        if ($page->exists() === false) {
            throw new Exception('The page does not exist');
        }

        if ($duplicate = $page->siblings()->not($page)->find($slug)) {
            throw new Exception(sprintf('The URL appendix "%s" exists', $slug));
        }

    },
    'page.change.status' => function (Page $page, string $status, int $position = null) {

        if ($page->exists() === false) {
            throw new Exception('The page does not exist');
        }

        if (in_array($status, ['listed', 'unlisted']) === false) {
            throw new Exception(sprintf('Invalid status "%s"', $status));
        }

    },
    'page.create' => function (Page $parent = null, string $slug, string $template, array $content = []) {

        $siblings = $parent === null ? $this->site()->children() : $parent->children();

        if ($duplicate = $siblings->find($slug)) {
            throw new Exception(sprintf('The URL appendix "%s" exists', $slug));
        }

    },
    'page.delete' => function (Page $page) {

        if ($page->exists() === false) {
            throw new Exception('The page does not exist');
        }

        if ($page->isHomePage()) {
            throw new Exception('The home page cannot be deleted');
        }

        if ($page->isErrorPage()) {
            throw new Exception('The error page cannot be deleted');
        }

    }
];
