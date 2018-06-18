<?php

use Kirby\Cms\PageDraft;

/**
 * Page Draft
 */

$page = require __DIR__ . '/Page.php';

$page['type'] = PageDraft::class;

// resolve siblings from the parent page instead of listing all drafts
$page['fields']['siblings'] = function (PageDraft $page) {
    return $page->parentModel()->children()->not($page);
};


return $page;
