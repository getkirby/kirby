<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

/**
 * An extended version of the Page object
 * that represents a draft.
 */
class PageDraft extends Page
{
    public function diruri(): string
    {
        if ($parent = $this->parent()) {
            return $parent->diruri() . '/_drafts/' . $this->dirname();
        }

        return '_drafts/' . $this->dirname();
    }

    public function isVerified(string $token = null)
    {
        if ($token === null) {
            return false;
        }

        return sha1($this->id() . $this->template()) === $token;
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

    public function token(): string
    {
        return sha1($this->id() . $this->template());
    }

    public function previewUrl(): string
    {
        return $this->url() . '?token=' . $this->token();
    }
}
