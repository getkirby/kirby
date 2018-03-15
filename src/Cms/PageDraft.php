<?php

namespace Kirby\Cms;

use Kirby\Util\Str;

class PageDraft extends Page
{

    public function diruri(): string
    {
        if ($parent = $this->parent()) {
            return $parent->diruri() . '/_drafts/' . $this->dirname();
        }

        return '_drafts/' . $this->dirname();
    }

    public static function seek($parent, string $path)
    {
        $path = str_replace('_drafts/', '', $path);

        if (Str::contains($path, '/') === false) {
            return $parent->drafts()->find($path);
        }

        $parts = explode('/', $path);

        foreach ($parts as $slug) {
            if ($page = $parent->find($slug)) {
                $parent = $page;
                continue;
            }

            if ($draft = $parent->drafts()->find($slug)) {
                $parent = $draft;
                continue;
            }

            return null;
        }

        return $parent;
    }

}
