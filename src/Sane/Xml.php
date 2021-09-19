<?php

namespace Kirby\Sane;

use DOMDocumentType;
use DOMNode;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Sane handler for XML files
 * @since 3.5.4
 *
 * @package   Kirby Sane
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Xml extends DomHandler
{
    /**
     * Custom callback for additional node sanitization
     * @internal
     *
     * @param \DOMNode $node
     * @return array Array with exception objects for each modification
     */
    public static function sanitizeNode(DOMNode $node): array
    {
        // if we are validating an XML file, block all SVG and HTML namespaces
        if (static::class === self::class && is_a($node, 'DOMElement') === true) {
            $simpleXmlElement = simplexml_import_dom($node);
            foreach ($simpleXmlElement->getDocNamespaces(false, false) as $namespace => $value) {
                if (
                    Str::contains($value, 'html', true) === true ||
                    Str::contains($value, 'svg', true) === true
                ) {
                    // TODO: Implement sanitization (remove the namespaces and the nodes/attributes)
                    throw new InvalidArgumentException(
                        'The namespace is not allowed in XML files' .
                        ' (around line ' . $node->getLineNo() . ')'
                    );
                }
            }
        }

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
        // if we are validating an XML file, block all SVG and HTML doctypes
        if (
            static::class === self::class &&
            (
                Str::contains($doctype->name, 'html', true) === true ||
                Str::contains($doctype->name, 'svg', true) === true
            )
        ) {
            throw new InvalidArgumentException('The doctype is not allowed in XML files');
        }
    }
}
