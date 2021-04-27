<?php

namespace Kirby\Sane;

use DOMDocument;
use DOMDocumentType;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Sane handler for XML files
 *
 * @package   Kirby Sane
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Xml extends Handler
{
    public static $allowedDataAttrs = [
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

    public static $allowedDomains = [];

    public static $allowedPIs = [];

    /**
     * Validates file contents
     *
     * @param string $string
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     */
    public static function validate(string $string): void
    {
        $xml = static::parse($string);

        static::validateDom($xml);
    }

    /**
     * Extracts all URLs wrapped in a url() wrapper. E.g. for style attributes.
     *
     * @param string $value
     * @return array
     */
    protected static function extractUrls(string $value): array
    {
        $count = preg_match_all(
            '!url\(\s*[\'"]?(.*?)[\'"]?\s*\)!i',
            static::trim($value),
            $matches,
            PREG_PATTERN_ORDER
        );

        if (is_int($count) === true && $count > 0) {
            return $matches[1];
        }

        return [];
    }

    /**
     * Checks if the URL is acceptable for href attributes
     *
     * @param string $url
     * @return bool
     */
    protected static function isAllowedUrl(string $url): bool
    {
        $url = mb_strtolower($url);

        // allow empty URL values
        if (empty($url) === true) {
            return true;
        }

        // allow URLs that point to fragments inside the file
        // as well as site-internal URLs
        if (in_array(mb_substr($url, 0, 1), ['#', '/']) === true) {
            return true;
        }

        // allow specific HTTP(S) URLs
        if (
            Str::startsWith($url, 'http://') === true ||
            Str::startsWith($url, 'https://') === true
        ) {
            $hostname = parse_url($url, PHP_URL_HOST);

            if (in_array($hostname, static::$allowedDomains) === true) {
                return true;
            }
        }

        // allow listed data URIs
        foreach (static::$allowedDataAttrs as $dataAttr) {
            if (Str::startsWith($url, $dataAttr) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tries to parse an XML string
     *
     * @param string $string
     * @return \DOMDocument
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file couldn't be parsed
     */
    protected static function parse(string $string)
    {
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->strictErrorChecking = false;

        $loaderSetting = null;
        if (\PHP_VERSION_ID < 80000) {
            // prevent loading external entities to protect against XXE attacks;
            // only needed for PHP versions before 8.0 (the function was deprecated
            // as the disabled state is the new default in PHP 8.0+)
            $loaderSetting = libxml_disable_entity_loader(true);
        }

        // switch to "user error handling"
        $intErrorsSetting = libxml_use_internal_errors(true);

        $load = $xml->loadXML($string);

        if (\PHP_VERSION_ID < 80000) {
            // ensure that we don't alter global state by
            // resetting the original value
            libxml_disable_entity_loader($loaderSetting);
        }

        // get one error for use below and reset the global state
        $error = libxml_get_last_error();
        libxml_clear_errors();
        libxml_use_internal_errors($intErrorsSetting);

        if ($load !== true) {
            $message = 'The file could not be parsed';

            if ($error !== false) {
                $message .= ': ' . $error->message;
            }

            throw new InvalidArgumentException([
                'fallback' => $message,
                'details'  => compact('error')
            ]);
        }

        return $xml;
    }

    /**
     * Removes invisible ASCII characters from the value
     *
     * @param string $value
     * @return string
     */
    protected static function trim(string $value): string
    {
        return trim(preg_replace('/[^ -~]/u', '', $value));
    }

    /**
     * Validates the attributes of an element
     *
     * @param \DOMXPath $xPath
     * @param \DOMNode $element
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If any of the attributes is not valid
     */
    protected static function validateAttrs(DOMXPath $xPath, DOMNode $element): void
    {
        $elementName = $element->nodeName;

        foreach ($element->attributes ?? [] as $attr) {
            $attrName  = $attr->nodeName;
            $attrValue = $attr->nodeValue;

            if (Str::contains($attrName, 'href') !== false) {
                if (static::isAllowedUrl($attrValue) !== true) {
                    throw new InvalidArgumentException(
                        'The URL is not allowed in attribute: ' . $attrName .
                        ' (line ' . $attr->getLineNo() . ')'
                    );
                }
            } else {
                // check for unwanted URLs in other attributes
                foreach (static::extractUrls($attrValue) as $url) {
                    if (static::isAllowedUrl($url) !== true) {
                        throw new InvalidArgumentException(
                            'The URL is not allowed in attribute: ' . $attrName .
                            ' (line ' . $attr->getLineNo() . ')'
                        );
                    }
                }
            }
        }

        // if we are validating an XML file, block
        // all SVG and HTML namespaces
        if (static::class === self::class && is_a($element, 'DOMElement') === true) {
            $simpleXmlElement = simplexml_import_dom($element);
            foreach ($simpleXmlElement->getDocNamespaces(false, false) as $namespace => $value) {
                if (
                    Str::contains($value, 'html', true) === true ||
                    Str::contains($value, 'svg', true) === true
                ) {
                    throw new InvalidArgumentException(
                        'The namespace is not allowed in XML files' .
                        ' (around line ' . $element->getLineNo() . ')'
                    );
                }
            }
        }
    }

    /**
     * Validates the doctype if present
     *
     * @param \DOMDocumentType $doctype
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the doctype is not valid
     */
    protected static function validateDoctype(DOMDocumentType $doctype): void
    {
        // if we are validating an XML file, block
        // all SVG and HTML doctypes
        if (
            static::class === self::class &&
            (
                Str::contains($doctype->name, 'html', true) === true ||
                Str::contains($doctype->name, 'svg', true) === true
            )
        ) {
            throw new InvalidArgumentException('The doctype is not allowed in XML files');
        }

        if (empty($doctype->publicId) === false || empty($doctype->systemId) === false) {
            throw new InvalidArgumentException('The doctype must not reference external files');
        }

        if (empty($doctype->internalSubset) === false) {
            throw new InvalidArgumentException('The doctype must not define a subset');
        }
    }

    /**
     * Validates a DOMDocument tree
     *
     * @param \DOMDocument $string
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the document didn't pass validation
     */
    protected static function validateDom(DOMDocument $xml): void
    {
        foreach ($xml->childNodes as $child) {
            if (is_a($child, 'DOMDocumentType') === true) {
                static::validateDoctype($child);
            }
        }

        // validate all processing instructions like <?xml-stylesheet
        $xPath = new DOMXPath($xml);
        $pis = $xPath->query('//processing-instruction()');
        static::validateProcessingInstructions($pis);

        // validate all elements in the document tree
        $elements = $xml->getElementsByTagName('*');
        static::validateElements($xPath, $elements);
    }

    /**
     * Validates all given DOM elements and their attributes
     *
     * @param \DOMXPath $xPath
     * @param \DOMNodeList $elements
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If any of the elements is not valid
     */
    protected static function validateElements(DOMXPath $xPath, DOMNodeList $elements): void
    {
        foreach ($elements as $element) {
            // check for allow-listed attributes
            static::validateAttrs($xPath, $element);
        }
    }

    /**
     * Validates the values of all given processing instructions
     *
     * @param \DOMNodeList $elements
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If any of the processing instructions is not valid
     */
    protected static function validateProcessingInstructions(DOMNodeList $elements): void
    {
        foreach ($elements as $element) {
            $elementName = $element->nodeName;

            // check for allow-listed processing instructions
            if (in_array($elementName, static::$allowedPIs) === false) {
                throw new InvalidArgumentException(
                    'The "' . $elementName . '" processing instruction (line ' .
                    $element->getLineNo() . ') is not allowed'
                );
            }
        }
    }
}
