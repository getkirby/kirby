<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Html\Element;
use Kirby\Text\Tags\Tag;

/**
 * The File Tag can be used to add
 * download links to your text
 *
 * Example:
 * ```
 * (file: https://example.com/download.pdf text: Download)
 * ```
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class File extends Link
{

    /**
     * Modifies the link element to add the
     * download attribute.
     *
     * @return Element
     */
    public function element(): Element
    {
        return parent::element()->attr('download', true);
    }
}
