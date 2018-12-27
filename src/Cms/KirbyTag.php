<?php

namespace Kirby\Cms;

/**
 * Extended KirbyTag class to provide
 * common helpers for tag objects
 */
class KirbyTag extends \Kirby\Text\KirbyTag
{

    /**
     * Finds a file for the given path.
     * The method first searches the file
     * in the current parent, if it's a page.
     * Afterwards it uses Kirby's global file finder.
     *
     * @param string $path
     * @return File|null
     */
    public function file(string $path): ?File
    {
        $parent = $this->parent();

        if (method_exists($parent, 'file') === true && $file = $parent->file($path)) {
            return $file;
        }

        if (is_a($parent, File::class) === true && $file = $parent->page()->file($path)) {
            return $file;
        }

        return $this->kirby()->file($path, null, true);
    }

    /**
     * Returns the current Kirby instance
     *
     * @return App
     */
    public function kirby(): App
    {
        return $this->data['kirby'] ?? App::instance();
    }

    /**
     * Returns the parent model
     *
     * @return Page|Site|File|User
     */
    public function parent()
    {
        return $this->data['parent'];
    }
}
