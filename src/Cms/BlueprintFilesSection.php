<?php

namespace Kirby\Cms;

use Exception;

class BlueprintFilesSection extends BlueprintPagesSection
{

    const ACCEPT = Files::class;

    protected function defaultQuery(): string
    {
        return 'page.files';
    }

    protected function itemTitle($item)
    {
        return $item->filename();
    }

    protected function itemInfo($item)
    {
        return (string)$item->mime();
    }

    protected function itemImage($item)
    {
        return $item;
    }

}
