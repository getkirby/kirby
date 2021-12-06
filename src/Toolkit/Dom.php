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
use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;

/**
 * Helper class for DOM handling using the DOMDocument class
 * @since 3.5.8
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
     * The original input code as
     * passed to the constructor
     *
     * @var string
     */
    protected $code;

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
        $this->code = $code;
        $this->doc  = new DOMDocument();

        $loaderSetting = null;
        if (\PHP_VERSION_ID < 80000) {
            // prevent loading external entities to protect against XXE attacks;
            // only needed for PHP versions before 8.0 (the function was deprecated
            // as the disabled state is the new default in PHP 8.0+)
            $loaderSetting = libxml_disable_entity_loader(true);
        }

        // switch to "user error handling"
        $intErrorsSetting = libxml_use_internal_errors(true);

        $this->type = strtoupper($type);
        if ($this->type === 'HTML') {
            // ensure proper parsing for HTML snippets
            if (preg_match('/<(html|body)[> ]/i', $code) !== 1) {
                $code = '<body>' . $code . '</body>';
            }

            // the loadHTML() method expects ISO-8859-1 by default;
            // force parsing as UTF-8 by injecting an XML declaration
            $xmlDeclaration = 'encoding="UTF-8" id="' . Str::random(10) . '"';
            $load = $this->doc->loadHTML('<?xml ' . $xmlDeclaration . '>' . $code);

            // remove the injected XML declaration again
            $pis = $this->query('//processing-instruction()');
            foreach (iterator_to_array($pis, false) as $pi) {
                if ($pi->data === $xmlDeclaration) {
                    static::remove($pi);
                }
            }

            // remove the default doctype
            if (Str::contains($code, '<!DOCTYPE ', true) === false) {
                static::remove($this->doc->doctype);
            }
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
        return $this->body ??= $this->query('/html/body')[0] ?? null;
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
     * Extracts all URLs wrapped in a url() wrapper. E.g. for style attributes.
     * @internal
     *
     * @param string $value
     * @return array
     */
    public static function extractUrls(string $value): array
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
     * @internal
     *
     * @param \DOMAttr $attr
     * @param array $options
     * @return true|string If not allowed, an error message is returned
     */
    public static function isAllowedAttr(DOMAttr $attr, array $options)
    {
        $allowedTags = $options['allowedTags'];

        // check if the attribute is in the list of global allowed attributes
        $isAllowedGlobalAttr = static::isAllowedGlobalAttr($attr, $options);

        // no specific tag attribute list
        if (is_array($allowedTags) === false) {
            return $isAllowedGlobalAttr;
        }

        // configuration per tag name
        $tagName            = $attr->ownerElement->nodeName;
        $listedTagName      = static::listContainsName(array_keys($options['allowedTags']), $attr->ownerElement, $options);
        $allowedAttrsForTag = $listedTagName ? ($allowedTags[$listedTagName] ?? true) : true;

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
            if (static::listContainsName($allowedAttrsForTag, $attr, $options) !== false) {
                return true;
            }

            return 'Not allowed by the "' . $tagName . '" element';
        }

        return 'The "' . $tagName . '" element does not allow attributes';
    }

    /**
     * Checks for allowed attributes according to the global allowlist
     * @internal
     *
     * @param \DOMAttr $attr
     * @param array $options
     * @return true|string If not allowed, an error message is returned
     */
    public static function isAllowedGlobalAttr(DOMAttr $attr, array $options)
    {
        $allowedAttrs = $options['allowedAttrs'];

        if ($allowedAttrs === true) {
            // all attributes are allowed
            return true;
        }

        if (
            static::listContainsName(
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

        if (
            is_array($allowedAttrs) === true &&
            static::listContainsName($allowedAttrs, $attr, $options) !== false
        ) {
            return true;
        }

        return 'Not included in the global allowlist';
    }

    /**
     * Checks if the URL is acceptable for URL attributes
     * @internal
     *
     * @param string $url
     * @param array $options
     * @return true|string If not allowed, an error message is returned
     */
    public static function isAllowedUrl(string $url, array $options)
    {
        $url = Str::lower($url);

        // allow empty URL values
        if (empty($url) === true) {
            return true;
        }

        // allow URLs that point to fragments inside the file
        if (mb_substr($url, 0, 1) === '#') {
            return true;
        }

        // disallow protocol-relative URLs
        if (mb_substr($url, 0, 2) === '//') {
            return 'Protocol-relative URLs are not allowed';
        }

        // allow site-internal URLs that didn't match the
        // protocol-relative check above
        if (mb_substr($url, 0, 1) === '/') {
            // if a CMS instance is active, only allow the URL
            // if it doesn't point outside of the index URL
            if ($kirby = App::instance(null, true)) {
                $indexUrl = $kirby->url('index', true)->path()->toString(true);

                if (Str::startsWith($url, $indexUrl) !== true) {
                    return 'The URL points outside of the site index URL';
                }

                // disallow directory traversal outside of the index URL
                // TODO: the ../ sequences could be cleaned from the URL
                //       before the check by normalizing the URL; then the
                //       check above can also validate URLs with ../ sequences
                if (
                    Str::contains($url, '../') !== false ||
                    Str::contains($url, '..\\') !== false
                ) {
                    return 'The ../ sequence is not allowed in relative URLs';
                }
            }

            // no active CMS instance, always allow site-internal URLs
            return true;
        }

        // allow relative URLs (= URLs without a scheme);
        // this is either a URL without colon or one where the
        // part before the colon is definitely no valid scheme;
        // see https://url.spec.whatwg.org/#url-writing
        if (
            Str::contains($url, ':') === false ||
            Str::contains(Str::before($url, ':'), '/') === true
        ) {
            // disallow directory traversal as we cannot know
            // in which URL context the URL will be printed
            if (
                Str::contains($url, '../') !== false ||
                Str::contains($url, '..\\') !== false
            ) {
                return 'The ../ sequence is not allowed in relative URLs';
            }

            return true;
        }

        // allow specific HTTP(S) URLs
        if (
            Str::startsWith($url, 'http://') === true ||
            Str::startsWith($url, 'https://') === true
        ) {
            if ($options['allowedDomains'] === true) {
                return true;
            }

            $hostname = parse_url($url, PHP_URL_HOST);

            if (in_array($hostname, $options['allowedDomains']) === true) {
                return true;
            }

            return 'The hostname "' . $hostname . '" is not allowed';
        }

        // allow listed data URIs
        if (Str::startsWith($url, 'data:') === true) {
            if ($options['allowedDataUris'] === true) {
                return true;
            }

            foreach ($options['allowedDataUris'] as $dataAttr) {
                if (Str::startsWith($url, $dataAttr) === true) {
                    return true;
                }
            }

            return 'Invalid data URI';
        }

        // allow valid email addresses
        if (Str::startsWith($url, 'mailto:') === true) {
            $address = Str::after($url, 'mailto:');

            if (empty($address) === true || V::email($address) === true) {
                return true;
            }

            return 'Invalid email address';
        }

        // allow valid telephone numbers
        if (Str::startsWith($url, 'tel:') === true) {
            $address = Str::after($url, 'tel:');

            if (
                empty($address) === true ||
                preg_match('!^[+]?[0-9]+$!', $address) === 1
            ) {
                return true;
            }

            return 'Invalid telephone number';
        }

        return 'Unknown URL type';
    }

    /**
     * Check if the XML extension is installed on the server.
     * Otherwise DOMDocument won't be available and the Dom cannot
     * work at all.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    public static function isSupported(): bool
    {
        return class_exists('DOMDocument') === true;
    }

    /**
     * Returns the XML or HTML markup contained in the node
     *
     * @param \DOMNode $node
     * @return string
     */
    public function innerMarkup(DOMNode $node): string
    {
        $markup = '';
        $method = 'save' . $this->type;

        foreach ($node->childNodes as $child) {
            $markup .= $node->ownerDocument->$method($child);
        }

        return $markup;
    }

    /**
     * Checks if a list contains the name of a node considering
     * the allowed namespaces
     * @internal
     *
     * @param array $list
     * @param \DOMNode $node
     * @param array $options See `Dom::sanitize()`
     * @param \Closure|null Comparison callback that returns whether the expected and real name match
     * @return string|false Matched name in the list or `false`
     */
    public static function listContainsName(array $list, DOMNode $node, array $options, ?Closure $compare = null)
    {
        $allowedNamespaces = $options['allowedNamespaces'];
        $localName         = $node->localName;

        if ($compare === null) {
            $compare = function ($expected, $real): bool {
                return $expected === $real;
            };
        }

        // if the configuration does not define namespace URIs or if the
        // currently checked node is from the special `xml:` namespace
        // that has a fixed namespace according to the XML spec...
        if ($allowedNamespaces === true || $node->namespaceURI === 'http://www.w3.org/XML/1998/namespace') {
            // ...take the list as it is and only consider
            // exact matches of the local name (which will
            // contain a namespace if that namespace name
            // is not defined in the document)

            // the list contains the `xml:` prefix, so add it to the name as well
            if ($node->namespaceURI === 'http://www.w3.org/XML/1998/namespace') {
                $localName = 'xml:' . $localName;
            }

            foreach ($list as $item) {
                if ($compare($item, $localName) === true) {
                    return $item;
                }
            }

            return false;
        }

        // we need to consider the namespaces
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
     *                       data URIs (only checked in `urlAttrs` and inside `url()` wrappers)
     *                       or `true` for any
     *                       - `allowedDomains`: Allowed hostnames for HTTP(S) URLs in `urlAttrs`
     *                       and inside `url()` wrappers or `true` for any
     *                       - `allowedNamespaces`: Associative array of all allowed namespace URIs;
     *                       the array keys are reference names that can be referred to from the
     *                       `allowedAttrPrefixes`, `allowedAttrs`, `allowedTags`, `disallowedTags`
     *                       and `urlAttrs` lists; the namespace names as used in the document are *not*
     *                       validated; setting the whole option to `true` will allow any namespace
     *                       - `allowedPIs`: Names of allowed XML processing instructions or
     *                       `true` for any
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
     *                       be removed completely including their children (matched case-insensitively)
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
            'allowedDataUris'     => true,
            'allowedDomains'      => true,
            'allowedNamespaces'   => true,
            'allowedPIs'          => true,
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
        foreach (iterator_to_array($this->doc->childNodes, false) as $child) {
            if (is_a($child, 'DOMDocumentType') === true) {
                $this->sanitizeDoctype($child, $options, $errors);
            }
        }

        // validate all processing instructions like <?xml-stylesheet
        $pis = $this->query('//processing-instruction()');
        foreach (iterator_to_array($pis, false) as $pi) {
            $this->sanitizePI($pi, $options, $errors);
        }

        // validate all elements in the document tree
        $elements = $this->doc->getElementsByTagName('*');
        foreach (iterator_to_array($elements, false) as $element) {
            $this->sanitizeElement($element, $options, $errors);
        }

        return $errors;
    }

    /**
     * Returns the document markup as string
     *
     * @param bool $normalize If set to `true`, the document
     *                        is exported with an XML declaration/
     *                        full HTML markup even if the input
     *                        didn't have them
     * @return string
     */
    public function toString(bool $normalize = false): string
    {
        if ($this->type === 'HTML') {
            $string = $this->exportHtml($normalize);
        } else {
            $string = $this->exportXml($normalize);
        }

        // add trailing newline if the input contained one
        if (rtrim($this->code, "\r\n") !== $this->code) {
            $string .= "\n";
        }

        return $string;
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
            // discard text nodes as they can be unexpected
            // directly in the parent element
            if (is_a($childNode, 'DOMText') === true) {
                continue;
            }

            $node->parentNode->insertBefore(clone $childNode, $node);
        }

        static::remove($node);
    }

    /**
     * Returns the document markup as HTML string
     *
     * @param bool $normalize If set to `true`, the document
     *                        is exported with full HTML markup
     *                        even if the input didn't have it
     * @return string
     */
    protected function exportHtml(bool $normalize = false): string
    {
        // enforce export as UTF-8 by injecting a <meta> tag
        // at the beginning of the document
        $metaTag = $this->doc->createElement('meta');
        $metaTag->setAttribute('http-equiv', 'Content-Type');
        $metaTag->setAttribute('content', 'text/html; charset=utf-8');
        $metaTag->setAttribute('id', $metaId = Str::random(10));
        $this->doc->insertBefore($metaTag, $this->doc->documentElement);

        if (
            preg_match('/<html[> ]/i', $this->code) === 1 ||
            $this->doc->doctype !== null ||
            $normalize === true
        ) {
            // full document
            $html = $this->doc->saveHTML();
        } elseif (preg_match('/<body[> ]/i', $this->code) === 1) {
            // there was a <body>, but no <html>; export just the <body>
            $html = $this->doc->saveHTML($this->body());
        } else {
            // just an HTML snippet
            $html = $this->innerMarkup($this->body());
        }

        // remove the <meta> tag from the document and from the output
        static::remove($metaTag);
        $html = str_replace($this->doc->saveHTML($metaTag), '', $html);

        return trim($html);
    }

    /**
     * Returns the document markup as XML string
     *
     * @param bool $normalize If set to `true`, the document
     *                        is exported with an XML declaration
     *                        even if the input didn't have it
     * @return string
     */
    protected function exportXml(bool $normalize = false): string
    {
        if (Str::contains($this->code, '<?xml ', true) === false && $normalize === false) {
            // the input didn't contain an XML declaration;
            // only return child nodes, which omits it
            $result = [];
            foreach ($this->doc->childNodes as $node) {
                $result[] = $this->doc->saveXML($node);
            }

            return implode("\n", $result);
        }

        // ensure that the document is encoded as UTF-8
        // unless a different encoding was specified in
        // the input or before exporting
        if ($this->doc->encoding === null) {
            $this->doc->encoding = 'UTF-8';
        }

        return trim($this->doc->saveXML());
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
        $name    = $attr->nodeName;
        $value   = $attr->value;

        $allowed = static::isAllowedAttr($attr, $options);
        if ($allowed !== true) {
            $errors[] = new InvalidArgumentException(
                'The "' . $name . '" attribute (line ' .
                $attr->getLineNo() . ') is not allowed: ' .
                $allowed
            );
            $element->removeAttributeNode($attr);
        } elseif (static::listContainsName($options['urlAttrs'], $attr, $options) !== false) {
            $allowed = static::isAllowedUrl($value, $options);
            if ($allowed !== true) {
                $errors[] = new InvalidArgumentException(
                    'The URL is not allowed in attribute "' .
                    $name . '" (line ' . $attr->getLineNo() . '): ' .
                    $allowed
                );
                $element->removeAttributeNode($attr);
            }
        } else {
            // check for unwanted URLs in other attributes
            foreach (static::extractUrls($value) as $url) {
                $allowed = static::isAllowedUrl($url, $options);
                if ($allowed !== true) {
                    $errors[] = new InvalidArgumentException(
                        'The URL is not allowed in attribute "' .
                        $name . '" (line ' . $attr->getLineNo() . '): ' .
                        $allowed
                    );
                    $element->removeAttributeNode($attr);
                }
            }
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
            static::remove($doctype);
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
        $name = $element->nodeName;

        // check defined namespaces (`xmlns` attributes);
        // we need to check this first as the namespace can affect
        // whether the tag name is valid according to the configuration
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

        // check if the tag is blocklisted; remove the element completely
        if (
            static::listContainsName(
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
            static::remove($element);

            return;
        }

        // check if the tag is not allowlisted; keep children
        if ($options['allowedTags'] !== true) {
            $listedName = static::listContainsName(array_keys($options['allowedTags']), $element, $options);

            if ($listedName === false) {
                $errors[] = new InvalidArgumentException(
                    'The "' . $name . '" element (line ' .
                    $element->getLineNo() . ') is not allowed, ' .
                    'but its children can be kept'
                );
                static::unwrap($element);

                return;
            }
        }

        // check attributes
        if ($element->hasAttributes()) {
            // convert the `DOMNodeList` to an array first, otherwise removing
            // attributes would shift the list and make subsequent operations fail
            foreach (iterator_to_array($element->attributes, false) as $attr) {
                $this->sanitizeAttr($attr, $options, $errors);

                // custom check (if the attribute is still in the document)
                if ($attr->ownerElement !== null && $options['attrCallback']) {
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
        if (is_array($options['allowedPIs']) === true && in_array($name, $options['allowedPIs']) === false) {
            $errors[] = new InvalidArgumentException(
                'The "' . $name . '" processing instruction (line ' .
                $pi->getLineNo() . ') is not allowed'
            );
            static::remove($pi);
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
