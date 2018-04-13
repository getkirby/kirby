<?php

namespace Kirby\Http\Acceptance;

use Kirby\Http\Acceptance;

/**
 * HTTP MimeType Acceptance negotiation
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class MimeType extends Acceptance
{

    /**
     * Creates a new MimeType acceptance object
     * If no mime type is given, the accepted content
     * type will be taken from the `$_SERVER` global
     *
     * @param string|null $input
     */
    public function __construct(string $input = null)
    {
        if ($input === null) {
            $input = $_SERVER['HTTP_ACCEPT'] ?? '';
        }
        parent::__construct($input);
    }

    /**
     * Special fuzzy match for mime types with wildcards.
     * I.e. `image/*`
     *
     * @param  array  $item
     * @param  string $pattern
     * @return boolean
     */
    protected function match(array $item, string $pattern): bool
    {
        return fnmatch($item['value'], $pattern, FNM_PATHNAME) === true;
    }
}
