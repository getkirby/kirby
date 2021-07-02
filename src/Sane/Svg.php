<?php

namespace Kirby\Sane;

use DOMDocumentType;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Sane handler for SVG files
 * @since 3.5.4
 *
 * @package   Kirby Sane
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Svg extends Xml
{
    /**
     * Allow and block lists are inspired by DOMPurify
     *
     * @link https://github.com/cure53/DOMPurify
     * @copyright 2015 Mario Heiderich
     * @license https://www.apache.org/licenses/LICENSE-2.0
     */
    public static $allowedAttributes = [
        'accent-height',
        'accumulate',
        'additive',
        'alignment-baseline',
        'ascent',
        'attributeName',
        'attributeType',
        'azimuth',
        'baseFrequency',
        'baseline-shift',
        'begin',
        'bias',
        'by',
        'class',
        'clip',
        'clipPathUnits',
        'clip-path',
        'clip-rule',
        'color',
        'color-interpolation',
        'color-interpolation-filters',
        'color-profile',
        'color-rendering',
        'cx',
        'cy',
        'd',
        'dx',
        'dy',
        'diffuseConstant',
        'direction',
        'display',
        'divisor',
        'dur',
        'edgeMode',
        'elevation',
        'end',
        'fill',
        'fill-opacity',
        'fill-rule',
        'filter',
        'filterUnits',
        'flood-color',
        'flood-opacity',
        'font-family',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'fx',
        'fy',
        'g1',
        'g2',
        'glyph-name',
        'glyphRef',
        'gradientUnits',
        'gradientTransform',
        'height',
        'href',
        'id',
        'image-rendering',
        'in',
        'in2',
        'k',
        'k1',
        'k2',
        'k3',
        'k4',
        'kerning',
        'keyPoints',
        'keySplines',
        'keyTimes',
        'lang',
        'lengthAdjust',
        'letter-spacing',
        'kernelMatrix',
        'kernelUnitLength',
        'lighting-color',
        'local',
        'marker-end',
        'marker-mid',
        'marker-start',
        'markerHeight',
        'markerUnits',
        'markerWidth',
        'maskContentUnits',
        'maskUnits',
        'max',
        'mask',
        'media',
        'method',
        'mode',
        'min',
        'name',
        'numOctaves',
        'offset',
        'operator',
        'opacity',
        'order',
        'orient',
        'orientation',
        'origin',
        'overflow',
        'paint-order',
        'path',
        'pathLength',
        'patternContentUnits',
        'patternTransform',
        'patternUnits',
        'points',
        'preserveAlpha',
        'preserveAspectRatio',
        'primitiveUnits',
        'r',
        'rx',
        'ry',
        'radius',
        'refX',
        'refY',
        'repeatCount',
        'repeatDur',
        'restart',
        'result',
        'rotate',
        'scale',
        'seed',
        'shape-rendering',
        'specularConstant',
        'specularExponent',
        'spreadMethod',
        'startOffset',
        'stdDeviation',
        'stitchTiles',
        'stop-color',
        'stop-opacity',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke',
        'stroke-width',
        'style',
        'surfaceScale',
        'systemLanguage',
        'tabindex',
        'targetX',
        'targetY',
        'transform',
        'text-anchor',
        'text-decoration',
        'text-rendering',
        'textLength',
        'type',
        'u1',
        'u2',
        'unicode',
        'values',
        'viewBox',
        'visibility',
        'version',
        'vert-adv-y',
        'vert-origin-x',
        'vert-origin-y',
        'width',
        'word-spacing',
        'wrap',
        'writing-mode',
        'xChannelSelector',
        'yChannelSelector',
        'x',
        'x1',
        'x2',
        'xlink:href',
        'y',
        'y1',
        'y2',
        'z',
        'zoomAndPan',
    ];

    public static $allowedElements = [
        'svg',
        'a',
        'altGlyph',
        'altGlyphDef',
        'altGlyphItem',
        'animateColor',
        'animateMotion',
        'animateTransform',
        'circle',
        'clipPath',
        'defs',
        'desc',
        'ellipse',
        'filter',
        'font',
        'g',
        'glyph',
        'glyphRef',
        'hkern',
        'image',
        'line',
        'linearGradient',
        'marker',
        'mask',
        'metadata',
        'mpath',
        'path',
        'pattern',
        'polygon',
        'polyline',
        'radialGradient',
        'rect',
        'stop',
        'style',
        'switch',
        'symbol',
        'text',
        'textPath',
        'title',
        'tref',
        'tspan',
        'use',
        'view',
        'vkern',
    ];

    public static $allowedFilters = [
        'feBlend',
        'feColorMatrix',
        'feComponentTransfer',
        'feComposite',
        'feConvolveMatrix',
        'feDiffuseLighting',
        'feDisplacementMap',
        'feDistantLight',
        'feFlood',
        'feFuncA',
        'feFuncB',
        'feFuncG',
        'feFuncR',
        'feGaussianBlur',
        'feMerge',
        'feMergeNode',
        'feMorphology',
        'feOffset',
        'fePointLight',
        'feSpecularLighting',
        'feSpotLight',
        'feTile',
        'feTurbulence',
    ];

    public static $allowedNamespaces = [
        'xmlns'       => 'http://www.w3.org/2000/svg',
        'xmlns:svg'   => 'http://www.w3.org/2000/svg',
        'xmlns:xlink' => 'http://www.w3.org/1999/xlink'
    ];

    /**
     * IMPORTANT: Use lower-case names here because
     * of the case-insensitive matching
     */
    public static $disallowedElements = [
        'animate',
        'color-profile',
        'cursor',
        'discard',
        'fedropshadow',
        'feimage',
        'font-face',
        'font-face-format',
        'font-face-name',
        'font-face-src',
        'font-face-uri',
        'foreignobject',
        'hatch',
        'hatchpath',
        'mesh',
        'meshgradient',
        'meshpatch',
        'meshrow',
        'missing-glyph',
        'script',
        'set',
        'solidcolor',
        'unknown',
    ];

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
        $svg = static::parse($string);

        $rootName = $svg->documentElement->nodeName;
        if ($rootName !== 'svg') {
            throw new InvalidArgumentException('The file is not a SVG (got <' . $rootName . '>)');
        }

        parent::validateDom($svg);
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

            // allow all aria and data attributes
            $beginning = mb_substr($attrName, 0, 5);
            if ($beginning === 'aria-' || $beginning === 'data-') {
                continue;
            }

            if (in_array($attrName, static::$allowedAttributes) !== true) {
                throw new InvalidArgumentException(
                    'The "' . $attrName . '" attribute (line ' .
                    $attr->getLineNo() . ') is not allowed in SVGs'
                );
            }

            // block nested <use> elements ("Billion Laughs" DoS attack)
            if (
                $elementName === 'use' &&
                Str::contains($attrName, 'href') !== false &&
                Str::startsWith($attrValue, '#') === true
            ) {
                // find the target (used element)
                $id = str_replace('"', '', mb_substr($attrValue, 1));
                $target = $xPath->query('//*[@id="' . $id . '"]')->item(0);

                // the target must not contain any other <use> elements
                if (
                    is_a($target, 'DOMElement') === true &&
                    $target->getElementsByTagName('use')->count() > 0
                ) {
                    throw new InvalidArgumentException(
                        'Nested "use" elements are not allowed in SVGs (used in line ' .
                        $element->getLineNo() . ')'
                    );
                }
            }
        }

        // validate `xmlns` attributes as well, which can only
        // be properly extracted using SimpleXML
        if (is_a($element, 'DOMElement') === true) {
            $simpleXmlElement = simplexml_import_dom($element);
            foreach ($simpleXmlElement->getDocNamespaces(false, false) as $namespace => $value) {
                $namespace = 'xmlns' . ($namespace ? ':' . $namespace : '');

                // check if the namespace is allowlisted
                if (
                    isset(static::$allowedNamespaces[$namespace]) !== true ||
                    static::$allowedNamespaces[$namespace] !== $value
                ) {
                    throw new InvalidArgumentException(
                        'The namespace "' . $namespace . '" (around line ' .
                        $element->getLineNo() . ') is not allowed or has an invalid value'
                    );
                }
            }
        }

        parent::validateAttrs($xPath, $element);
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
        if (mb_strtolower($doctype->name) !== 'svg') {
            throw new InvalidArgumentException('Invalid doctype');
        }

        parent::validateDoctype($doctype);
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
        $allowedElements = array_merge(static::$allowedElements, static::$allowedFilters);

        foreach ($elements as $element) {
            $elementName = $element->nodeName;
            $elementNameLower = mb_strtolower($elementName);

            // check for block-listed elements
            if (in_array($elementNameLower, static::$disallowedElements) === true) {
                throw new InvalidArgumentException(
                    'The "' . $elementName . '" element (line ' .
                    $element->getLineNo() . ') is not allowed in SVGs'
                );
            }

            // check for allow-listed elements
            if (in_array($elementName, $allowedElements) === false) {
                throw new InvalidArgumentException(
                    'The "' . $elementName . '" element (line ' .
                    $element->getLineNo() . ') is not allowed in SVGs'
                );
            }

            // check for URLs inside <style> elements
            if ($elementName === 'style') {
                foreach (static::extractUrls($element->textContent) as $url) {
                    if (static::isAllowedUrl($url) !== true) {
                        throw new InvalidArgumentException(
                            'The URL is not allowed in the <style> element' .
                            ' (around line ' . $element->getLineNo() . ')'
                        );
                    }
                }
            }
        }

        parent::validateElements($xPath, $elements);
    }
}
