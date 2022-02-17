<?php

namespace Kirby\Sane;

use DOMAttr;
use DOMDocumentType;
use DOMElement;
use Kirby\Toolkit\Dom;

/**
 * Base class for Sane handlers with DOM file types
 * @since 3.5.8
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class DomHandler extends Handler
{
    /**
     * List of all MIME types that may
     * be used in data URIs
     *
     * @var array
     */
    public static $allowedDataUris = [
        'data:image/png',
        'data:image/gif',
        'data:image/jpg',
        'data:image/jpe',
        'data:image/pjp',
        'data:img/png',
        'data:img/gif',
        'data:img/jpg',
        'data:img/jpe',
        'data:img/pjp',
    ];

    /**
     * Allowed hostnames for HTTP(S) URLs
     *
     * @var array
     */
    public static $allowedDomains = [];

    /**
     * Names of allowed XML processing instructions
     *
     * @var array
     */
    public static $allowedPIs = [];

    /**
     * The document type (`'HTML'` or `'XML'`)
     * (to be set in child classes)
     *
     * @var string
     */
    protected static $type = 'XML';

    /**
     * Sanitizes the given string
     *
     * @param string $string
     * @return string
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file couldn't be parsed
     */
    public static function sanitize(string $string): string
    {
        $dom = static::parse($string);
        $dom->sanitize(static::options());
        return $dom->toString();
    }

    /**
     * Validates file contents
     *
     * @param string $string
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file couldn't be parsed
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     */
    public static function validate(string $string): void
    {
        $dom = static::parse($string);
        $errors = $dom->sanitize(static::options());
        if (count($errors) > 0) {
            // there may be multiple errors, we can only throw one of them at a time
            throw $errors[0];
        }
    }

    /**
     * Custom callback for additional attribute sanitization
     * @internal
     *
     * @param \DOMAttr $attr
     * @return array Array with exception objects for each modification
     */
    public static function sanitizeAttr(DOMAttr $attr): array
    {
        // to be extended in child classes
        return [];
    }

    /**
     * Custom callback for additional element sanitization
     * @internal
     *
     * @param \DOMElement $element
     * @return array Array with exception objects for each modification
     */
    public static function sanitizeElement(DOMElement $element): array
    {
        // to be extended in child classes
        return [];
    }

    /**
     * Custom callback for additional doctype validation
     * @internal
     *
     * @param \DOMDocumentType $doctype
     * @return void
     */
    public static function validateDoctype(DOMDocumentType $doctype): void
    {
        // to be extended in child classes
    }

    /**
     * Returns the sanitization options for the handler
     * (to be extended in child classes)
     *
     * @return array
     */
    protected static function options(): array
    {
        return [
            'allowedDataUris' => static::$allowedDataUris,
            'allowedDomains'  => static::$allowedDomains,
            'allowedPIs'      => static::$allowedPIs,
            'attrCallback'    => [static::class, 'sanitizeAttr'],
            'doctypeCallback' => [static::class, 'validateDoctype'],
            'elementCallback' => [static::class, 'sanitizeElement'],
        ];
    }

    /**
     * Parses the given string into a `Toolkit\Dom` object
     *
     * @param string $string
     * @return \Kirby\Toolkit\Dom
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file couldn't be parsed
     */
    protected static function parse(string $string)
    {
        return new Dom($string, static::$type);
    }
}
