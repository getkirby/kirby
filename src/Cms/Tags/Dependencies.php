<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\App;
use Kirby\Cms\Model;
use Kirby\Cms\Page;
use Kirby\Cms\Site;

trait Dependencies
{

    /**
     * Returns the parent content field if it
     * has been passed to the data array of the parse
     * method
     *
     * @return ContentField|null
     */
    public function field()
    {
        return $this->data()['field'] ?? null;
    }

    /**
     * Tries to resolve page and site
     * files by the filename. This will
     * only work if the parser received
     * the parent content field object
     *
     * @param string $filename
     * @return File|null
     */
    public function file(string $filename)
    {
        // try to resolve page files
        if ($page = $this->page()) {
            return $page->file($filename);
        }

        $parent = $this->parent();

        // try to resolve site files
        if (is_a($parent, Site::class) === true) {
            return $parent->file($filename);
        }

        return null;
    }

    /**
     * Tries to find the kirby instance by the
     * parent model and falls back to the
     * app singleton.
     *
     * @return App
     */
    public function kirby(): App
    {
        if ($parent = $this->parent()) {
            if (is_a($parent, Model::class) === true) {
                return $parent->kirby();
            }
        }

        return App::instance();
    }

    /**
     * Tries to find the parent model
     * This can be a page, file, site
     * or user, or even null.
     * It depends on what has been passed
     * to the parse function as additional
     * data. The built-in kirbytext methods
     * all pass down the current content field
     *
     * @return Page|Site|File|User|null
     */
    public function parent()
    {
        if ($field = $this->field()) {
            return $field->parent();
        }

        return null;
    }

    /**
     * Tries to find the parent page
     *
     * @return Page|null
     */
    public function page()
    {
        $parent = $this->parent();

        if (is_a($parent, Page::class) === true) {
            return $parent;
        }

        if (is_a($parent, File::class) === true && is_a($parent->parent(), Page::class) === true) {
            return $parent->parent();
        }

        return null;
    }

    /**
     * Tries to find the site by the
     * parent model and falls back to the
     * app singleton.
     *
     * @return Site
     */
    public function site(): Site
    {
        if ($parent = $this->parent()) {
            if (is_a($parent, Model::class) === true) {
                return $parent->site();
            }
        }

        return App::instance()->site();
    }
}
