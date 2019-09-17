<?php

namespace Kirby\Cms;

/**
 * Extended KirbyTag class to provide
 * common helpers for tag objects
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
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
     * @return \Kirby\Cms\File|null
     */
    public function file(string $path)
    {
        $parent = $this->parent();

        if (method_exists($parent, 'file') === true && $file = $parent->file($path)) {
            return $file;
        }

        if (is_a($parent, 'Kirby\Cms\File') === true && $file = $parent->page()->file($path)) {
            return $file;
        }

        return $this->kirby()->file($path, null, true);
    }

    /**
     * Returns the current Kirby instance
     *
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->data['kirby'] ?? App::instance();
    }

    /**
     * Returns the parent model
     *
     * @return \Kirby\Cms\Model|null
     */
    public function parent()
    {
        return $this->data['parent'];
    }
}
