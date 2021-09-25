<?php

namespace Kirby\Toolkit;

use Closure;
use DOMAttr;
use DOMDocument;
use DOMDocumentType;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;
use DOMXPath;
use Kirby\Exception\InvalidArgumentException;

/**
 * Helper class for DOM handling using the DOMDocument class
 * @since 3.6.0
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Dom
{
    /**
     * Cache for the HTML body
     *
     * @var \DOMElement|null
     */
    protected $body;

    /**
     * Document object
     *
     * @var \DOMDocument
     */
    protected $doc;

    /**
     * Document type (`'HTML'` or `'XML'`)
     *
     * @var string
     */
    protected $type;

    /**
     * Class constructor
     *
     * @param string $code XML or HTML code
     * @param string $type Document type (`'HTML'` or `'XML'`)
     */
    public function __construct(string $code, string $type = 'HTML')
    {
        $this->doc = new DOMDocument();
        $this->doc->strictErrorChecking = false;

        $loaderSetting = null;
        if (\PHP_VERSION_ID < 80000) {
            // prevent loading external entities to protect against XXE attacks;
            // only needed for PHP versions before 8.0 (the function was deprecated
            // as the disabled state is the new default in PHP 8.0+)
            $loaderSetting = libxml_disable_entity_loader(true);
        }

        // switch to "user error handling"
        $intErrorsSetting = libxml_use_internal_errors(true);

        $this->type = $type;
        if ($type === 'html') {
            // the loadHTML() method expects ISO-8859-1 by default;
            // convert every native UTF-8 character to an entity
            $load = $this->doc->loadHTML(mb_convert_encoding($code, 'HTML-ENTITIES', 'UTF-8'));
        } else {
            $load = $this->doc->loadXML($code);
        }

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
            $message = 'The markup could not be parsed';

            if ($error !== false) {
                $message .= ': ' . $error->message;
            }

            throw new InvalidArgumentException([
                'fallback' => $message,
                'details'  => compact('error')
            ]);
        }
    }

    /**
     * Returns the HTML body if one exists
     *
     * @return \DOMElement|null
     */
    public function body()
    {
        return $this->body = $this->body ?? $this->query('/html/body')[0] ?? null;
    }

    /**
     * Returns the document object
     *
     * @return \DOMDocument
     */
    public function document()
    {
        return $this->doc;
    }

    /**
     * Returns the XML or HTML markup contained in the node
     *
     * @param \DOMNode $node
     * @return string
     */
    public function innerMarkup(DOMNode $node): string
    {
        if ($node === null) {
            return '';
        }

        $markup   = '';
        $children = $node->childNodes;
        $method   = 'save' . $this->type;

        foreach ($children as $child) {
            $markup .= $node->ownerDocument->$method($child);
        }

        return $markup;
    }

    /**
     * Removes a node from the document
     *
     * @param \DOMNode $node
     * @return void
     */
    public static function remove(DOMNode $node): void
    {
        $node->parentNode->removeChild($node);
    }

    /**
     * Executes an XPath query in the document
     *
     * @param string $query
     * @param \DOMNode|null $node Optional context node for relative queries
     * @return \DOMNodeList|false
     */
    public function query(string $query, ?DOMNode $node = null)
    {
        return (new DOMXPath($this->doc))->query($query, $node);
    }

    /**
     * Sanitizes the DOM according to the provided configuration
     *
     * @param array $options Array with the following options:
     *                       - `allowedAttrPrefixes`: Global list of allowed attribute prefixes
     *                       like `data-` and `aria-`
     *                       - `allowedAttrs`: Global list of allowed attrs or `true` to allow
     *                       any attribute
     *                       - `allowedDataUris`: List of all MIME types that may be used in
     *                       data attributes (only checked in `urlAttrs`)
     *                       - `allowedDomains`: Allowed hostnames for HTTP(S) URLs in `urlAttrs`
     *                       - `allowedNamespaces`: Associative array of all allowed namespace URIs;
     *                       the array keys are reference names that can be referred to from the
     *                       `allowedAttrPrefixes`, `allowedAttrs`, `allowedTags` and `urlAttrs` lists;
     *                       the namespace names as used in the document are *not* validated;
     *                       setting the whole option to `true` will allow any namespace
     *                       - `allowedPIs`: Names of allowed XML processing instructions
     *                       - `allowedTags`: Associative array of all allowed tag names with the
     *                       value of either an array with the list of all allowed attributes for
     *                       this tag, `true` to allow any attribute from the `allowedAttrs` list
     *                       or `false` to allow the tag without any attributes;
     *                       not listed tags will be unwrapped (removed, but children are kept);
     *                       setting the whole option to `true` will allow any tag
     *                       - `attrCallback`: Closure that will receive each `DOMAttr` and may
     *                       modify it; the callback must return an array with exception
     *                       objects for each modification
     *                       - `disallowedTags`: Array of explicitly disallowed tags, which will
     *                       be removed completely including their children; the names must be
     *                       lower-case as the matching is done case-insensitively
     *                       - `doctypeCallback`: Closure that will receive the `DOMDocumentType`
     *                       and may throw exceptions on validation errors
     *                       - `elementCallback`: Closure that will receive each `DOMElement` and
     *                       may modify it; the callback must return an array with exception
     *                       objects for each modification
     *                       - `urlAttrs`: List of attributes that may contain URLs
     * @return array List of validation errors during sanitization
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the doctype is not valid
     */
    public function sanitize(array $options): array
    {
        $options = array_merge([
            'allowedAttrPrefixes' => [],
            'allowedAttrs'        => true,
            'allowedDataUris'     => [],
            'allowedDomains'      => [],
            'allowedNamespaces'   => true,
            'allowedPIs'          => [],
            'allowedTags'         => true,
            'attrCallback'        => null,
            'disallowedTags'      => [],
            'doctypeCallback'     => null,
            'elementCallback'     => null,
            'urlAttrs'            => ['href', 'src', 'xlink:href'],
        ], $options);

        $errors = [];

        // validate the doctype;
        // convert the `DOMNodeList` to an array first, otherwise removing
        // nodes would shift the list and make subsequent operations fail
        foreach (iterator_to_array($this->doc->childNodes) as $child) {
            if (is_a($child, 'DOMDocumentType') === true) {
                $this->sanitizeDoctype($child, $options, $errors);
            }
        }

        // validate all processing instructions like <?xml-stylesheet
        $pis = $this->query('//processing-instruction()');
        foreach (iterator_to_array($pis) as $pi) {
            $this->sanitizePI($pi, $options, $errors);
        }

        // validate all elements in the document tree
        $elements = $this->doc->getElementsByTagName('*');
        foreach (iterator_to_array($elements) as $element) {
            $this->sanitizeElement($element, $options, $errors);
        }

        return $errors;
    }

    /**
     * Returns the document markup as string
     *
     * @param bool $xmlDecl If set to `false`, the XML declaration
     *                      is omitted from the output
     * @return string
     */
    public function toString(bool $xmlDecl = true): string
    {
        if ($this->type === 'XML' && $xmlDecl === false) {
            // only return child nodes, which omits the XML declaration
            $result = '';
            foreach ($this->doc->childNodes as $node) {
                $result .= $this->doc->saveXML($node) . "\n";
            }

            return $result;
        }

        $method = 'save' . $this->type;
        return $this->doc->$method();
    }

    /**
     * Removes a node from the document but keeps its children
     * by moving them one level up
     *
     * @param \DOMNode $node
     * @return void
     */
    public static function unwrap(DOMNode $node): void
    {
        foreach ($node->childNodes as $childNode) {
            $node->parentNode->insertBefore(clone $childNode, $node);
        }

        static::remove($node);
    }

    /**
     * Extracts all URLs wrapped in a url() wrapper. E.g. for style attributes.
     *
     * @param string $value
     * @return array
     */
    protected function extractUrls(string $value): array
    {
        // remove invisible ASCII characters from the value
        $value = trim(preg_replace('/[^ -~]/u', '', $value));

        $count = preg_match_all(
            '!url\(\s*[\'"]?(.*?)[\'"]?\s*\)!i',
            $value,
            $matches,
            PREG_PATTERN_ORDER
        );

        if (is_int($count) === true && $count > 0) {
            return $matches[1];
        }

        return [];
    }

    /**
     * Checks for allowed attributes according to the allowlist
     *
     * @param \DOMAttr $attr
     * @param array $options
     * @return true|string If not allowed, an error message is returned
     */
    protected function isAllowedAttr(DOMAttr $attr, array $options)
    {
        $allowedTags = $options['allowedTags'];

        // check if the attribute is in the list of global allowed attributes
        $isAllowedGlobalAttr = $this->isAllowedGlobalAttr($attr, $options);

        // no specific tag attribute list
        if (is_array($allowedTags) === false) {
            return $isAllowedGlobalAttr;
        }

        // configuration per tag name
        $tagName            = $attr->ownerElement->tagName;
        $allowedAttrsForTag = $allowedTags[$tagName] ?? true;

        // the element allows all global attributes
        if ($allowedAttrsForTag === true) {
            return $isAllowedGlobalAttr;
        }

        // specific attributes are allowed in addition to the global ones
        if (is_array($allowedAttrsForTag) === true) {
            // if allowed globally, we don't need further checks
            if ($isAllowedGlobalAttr === true) {
                return true;
            }

            // otherwise the tag configuration decides
            if ($this->listContainsName($allowedAttrsForTag, $attr, $options) !== false) {
                return true;
            }

            return 'Not allowed by the "' . $tagName . '" element';
        }

        return 'The "' . $tagName . '" element does not allow attributes';
    }

    /**
     * Checks for allowed attributes according to the global allowlist
     *
     * @param \DOMAttr $attr
     * @param array $options
     * @return true|string If not allowed, an error message is returned
     */
    protected function isAllowedGlobalAttr(DOMAttr $attr, array $options)
    {
        $allowedAttrs = $options['allowedAttrs'];

        if ($allowedAttrs === true) {
            // all attributes are allowed
            return true;
        }

        if (
            $this->listContainsName(
                $options['allowedAttrPrefixes'],
                $attr,
                $options,
                function ($expected, $real): bool {
                    return Str::startsWith($real, $expected);
                }
            ) !== false
        ) {
            return true;
        }

        if (is_array($allowedAttrs) && $this->listContainsName($allowedAttrs, $attr, $options) !== true) {
            return 'The "' . $attr->name . '" attribute is not included in the global allowlist';
        }

        return 'All attributes are blocked by default in the global allowlist';
    }

    /**
     * Checks if the URL is acceptable for URL attributes
     *
     * @param string $url
     * @param array $options
     * @return true|string If not allowed, an error message is returned
     */
    protected function isAllowedUrl(string $url, array $options)
    {
        $url = Str::lower($url);

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

            if (in_array($hostname, $options['allowedDomains']) === true) {
                return true;
            }

            return 'The hostname "' . $hostname . '" is not allowed';
        }

        // allow listed data URIs
        if (Str::startsWith($url, 'data:') === true) {
            foreach ($options['allowedDataUris'] as $dataAttr) {
                if (Str::startsWith($url, $dataAttr) === true) {
                    return true;
                }
            }

            return 'Invalid data URI';
        }

        return 'Unknown URL type';
    }

    /**
     * Checks if a list contains the name of a node considering
     * the allowed namespaces
     *
     * @param array $list
     * @param \DOMNode $node
     * @param array $options See `Dom::sanitize()`
     * @param \Closure|null Comparison callback that returns whether the expected and real name match
     * @return string|false Matched name in the list or `false`
     */
    protected function listContainsName(array $list, DOMNode $node, array $options, ?Closure $compare = null)
    {
        $allowedNamespaces = $options['allowedNamespaces'];
        $localName         = $node->localName;

        if ($compare === null) {
            $compare = function ($expected, $real): bool {
                return $expected === $real;
            };
        }

        if ($allowedNamespaces === true) {
            // take the list as it is and only consider
            // exact matches of the local name (which will
            // contain a namespace if that namespace name
            // is not defined in the document)
            if (in_array($localName, $list) === true) {
                return $localName;
            }

            return false;
        }

        // we need to consider the namespaces,
        // so look at each item individually
        foreach ($list as $item) {
            // try to find the expected origin namespace URI
            $namespaceUri = null;
            $itemLocal    = $item;
            if (Str::contains($item, ':') === true) {
                list($namespaceName, $itemLocal) = explode(':', $item);
                $namespaceUri = $allowedNamespaces[$namespaceName] ?? null;
            } else {
                // list items without namespace are from the default namespace
                $namespaceUri = $allowedNamespaces[''] ?? null;
            }

            // try if we can find an exact namespaced match
            if ($namespaceUri === $node->namespaceURI && $compare($itemLocal, $localName) === true) {
                return $item;
            }

            // also try to match the fully-qualified name
            // if the document doesn't define the namespace
            if ($node->namespaceURI === null && $compare($item, $node->nodeName) === true) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Sanitizes an attribute
     *
     * @param \DOMAttr $attr
     * @param array $options See `Dom::sanitize()`
     * @param array $errors Array to store additional errors in by reference
     * @return void
     */
    protected function sanitizeAttr(DOMAttr $attr, array $options, array &$errors): void
    {
        $element = $attr->ownerElement;
        $name    = $attr->name;
        $value   = $attr->value;

        $allowed = $this->isAllowedAttr($attr, $options);
        if ($allowed !== true) {
            $errors[] = new InvalidArgumentException(
                'The "' . $name . '" attribute (line ' .
                $attr->getLineNo() . ') is not allowed: ' .
                $allowed
            );
            $element->removeAttribute($name);
        } elseif ($this->listContainsName($options['urlAttrs'], $attr, $options) !== false) {
            $allowed = $this->isAllowedUrl($value, $options);
            if ($allowed !== true) {
                $errors[] = new InvalidArgumentException(
                    'The URL is not allowed in attribute: ' .
                    $name . ' (line ' . $attr->getLineNo() . '): ' .
                    $allowed
                );
                $element->removeAttribute($name);
            }

            // TODO: escape XSS attacks in query parameters
        } else {
            // check for unwanted URLs in other attributes
            foreach ($this->extractUrls($value) as $url) {
                $allowed = $this->isAllowedUrl($url, $options);
                if ($allowed !== true) {
                    $errors[] = new InvalidArgumentException(
                        'The URL is not allowed in attribute: ' .
                        $name . ' (line ' . $attr->getLineNo() . '): ' .
                        $allowed
                    );
                    $element->removeAttribute($name);
                }
            }

            // TODO: why do we need this?
            $attr->value = Escape::attr($attr->value);
        }
    }

    /**
     * Sanitizes the doctype
     *
     * @param \DOMDocumentType $doctype
     * @param array $options See `Dom::sanitize()`
     * @param array $errors Array to store additional errors in by reference
     * @return void
     */
    protected function sanitizeDoctype(DOMDocumentType $doctype, array $options, array &$errors): void
    {
        try {
            $this->validateDoctype($doctype, $options);
        } catch (InvalidArgumentException $e) {
            $errors[] = $e;
            $this->remove($doctype);
        }
    }

    /**
     * Sanitizes a single DOM element and its attribute
     *
     * @param \DOMElement $element
     * @param array $options See `Dom::sanitize()`
     * @param array $errors Array to store additional errors in by reference
     * @return void
     */
    protected function sanitizeElement(DOMElement $element, array $options, array &$errors): void
    {
        $name = $element->tagName;

        // check if the tag is blocklisted; remove the element completely
        if (
            $this->listContainsName(
                $options['disallowedTags'],
                $element,
                $options,
                function ($expected, $real): bool {
                    return Str::lower($expected) === Str::lower($real);
                }
            ) !== false
        ) {
            $errors[] = new InvalidArgumentException(
                'The "' . $name . '" element (line ' .
                $element->getLineNo() . ') is not allowed'
            );
            $this->remove($element);

            return;
        }

        // check if the tag is not allowlisted; keep children
        if ($options['allowedTags'] !== true) {
            $listedName = $this->listContainsName($options['allowedTags'], $element, $options);
            $isAllowed  = ($listedName === false) ? false : $options['allowedTags'][$listedName];

            if ($isAllowed === false) {
                $errors[] = new InvalidArgumentException(
                    'The "' . $name . '" element (line ' .
                    $element->getLineNo() . ') is not allowed, ' .
                    'but its children can be kept'
                );
                $this->unwrap($element);

                return;
            }
        }

        // check defined namespaces (`xmlns` attributes)
        if (is_array($options['allowedNamespaces']) === true) {
            $simpleXmlElement = simplexml_import_dom($element);
            foreach ($simpleXmlElement->getDocNamespaces(false, false) as $namespace => $value) {
                if (array_search($value, $options['allowedNamespaces']) === false) {
                    $element->removeAttributeNS($value, $namespace);
                    $errors[] = new InvalidArgumentException(
                        'The namespace "' . $value . '" is not allowed' .
                        ' (around line ' . $element->getLineNo() . ')'
                    );
                }
            }
        }

        // check attributes
        if ($element->hasAttributes()) {
            // convert the `DOMNodeList` to an array first, otherwise removing
            // attributes would shift the list and make subsequent operations fail
            foreach (iterator_to_array($element->attributes) as $attr) {
                $this->sanitizeAttr($attr, $options, $errors);

                // custom check
                if ($options['attrCallback']) {
                    $errors = array_merge($errors, $options['attrCallback']($attr) ?? []);
                }
            }
        }

        // custom check
        if ($options['elementCallback']) {
            $errors = array_merge($errors, $options['elementCallback']($element) ?? []);
        }
    }

    /**
     * Sanitizes a single XML processing instruction
     *
     * @param \DOMProcessingInstruction $pi
     * @param array $options See `Dom::sanitize()`
     * @param array $errors Array to store additional errors in by reference
     * @return void
     */
    protected function sanitizePI(DOMProcessingInstruction $pi, array $options, array &$errors): void
    {
        $name = $pi->nodeName;

        // check for allow-listed processing instructions
        if (in_array($name, $options['allowedPIs']) === false) {
            $errors[] = new InvalidArgumentException(
                'The "' . $name . '" processing instruction (line ' .
                $pi->getLineNo() . ') is not allowed'
            );
            $this->remove($pi);
        }
    }

    /**
     * Validates the document type
     *
     * @param \DOMDocumentType $doctype
     * @param array $options See `Dom::sanitize()`
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the doctype is not valid
     */
    protected function validateDoctype(DOMDocumentType $doctype, array $options): void
    {
        if (empty($doctype->publicId) === false || empty($doctype->systemId) === false) {
            throw new InvalidArgumentException('The doctype must not reference external files');
        }

        if (empty($doctype->internalSubset) === false) {
            throw new InvalidArgumentException('The doctype must not define a subset');
        }

        if ($options['doctypeCallback']) {
            $options['doctypeCallback']($doctype);
        }
    }
}
