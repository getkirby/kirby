<?php

use Kirby\Cms\PageDraft;

/**
 * Page Draft
 */

$page = require __DIR__ . '/Page.php';

$page['type'] = PageDraft::class;

return $page;
