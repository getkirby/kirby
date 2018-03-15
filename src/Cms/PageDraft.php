<?php

namespace Kirby\Cms;

class PageDraft extends Page
{

    public function diruri(): string
    {
        if ($parent = $this->parent()) {
            return $parent->diruri() . '/_drafts/' . $this->dirname();
        }

        return '_drafts/' . $this->dirname();
    }

}
