<?php

namespace Kirby\Sane;

use DOMAttr;
use DOMDocumentType;
use DOMElement;
use DOMXPath;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Dom;
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

    /**
     * Global list of allowed attribute prefixes
     *
     * @var array
     */
    public static $allowedAttrPrefixes = [
        'aria-',
        'data-',
    ];

    /**
     * Global list of allowed attributes
     *
     * @var array
     */
    public static $allowedAttrs = [
        // core attributes
        'id',
        'lang',
        'tabindex',
        'xml:id',
        'xml:lang',
        'xml:space',

        // styling attributes
        'class',
        'style',

        // conditional processing attributes
        'systemLanguage',

        // presentation attributes
        'alignment-baseline',
        'baseline-shift',
        'clip',
        'clip-path',
        'clip-rule',
        'color',
        'color-interpolation',
        'color-interpolation-filters',
        'color-profile',
        'color-rendering',
        'd',
        'direction',
        'display',
        'dominant-baseline',
        'enable-background',
        'fill',
        'fill-opacity',
        'fill-rule',
        'filter',
        'flood-color',
        'flood-opacity',
        'font-family',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'image-rendering',
        'kerning',
        'letter-spacing',
        'lighting-color',
        'marker-end',
        'marker-mid',
        'marker-start',
        'mask',
        'opacity',
        'overflow',
        'paint-order',
        'shape-rendering',
        'stop-color',
        'stop-opacity',
        'stroke',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke-width',
        'text-anchor',
        'text-decoration',
        'text-rendering',
        'transform',
        'visibility',
        'word-spacing',
        'writing-mode',

        // animation attribute target attributes
        'attributeName',
        'attributeType',

        // animation timing attributes
        'begin',
        'dur',
        'end',
        'max',
        'min',
        'repeatCount',
        'repeatDur',
        'restart',

        // animation value attributes
        'by',
        'from',
        'keySplines',
        'keyTimes',
        'to',
        'values',

        // animation addition attributes
        'accumulate',
        'additive',

        // filter primitive attributes
        'height',
        'result',
        'width',
        'x',
        'y',

        // transfer function attributes
        'amplitude',
        'exponent',
        'intercept',
        'offset',
        'slope',
        'tableValues',
        'type',

        // other attributes specific to one or multiple elements
        'azimuth',
        'baseFrequency',
        'bias',
        'clipPathUnits',
        'cx',
        'cy',
        'diffuseConstant',
        'divisor',
        'dx',
        'dy',
        'edgeMode',
        'elevation',
        'filterUnits',
        'fr',
        'fx',
        'fy',
        'g1',
        'g2',
        'glyph-name',
        'glyphRef',
        'gradientTransform',
        'gradientUnits',
        'href',
        'hreflang',
        'in',
        'in2',
        'k',
        'k1',
        'k2',
        'k3',
        'k4',
        'kernelMatrix',
        'kernelUnitLength',
        'keyPoints',
        'lengthAdjust',
        'limitingConeAngle',
        'markerHeight',
        'markerUnits',
        'markerWidth',
        'maskContentUnits',
        'maskUnits',
        'media',
        'method',
        'mode',
        'numOctaves',
        'operator',
        'order',
        'orient',
        'orientation',
        'path',
        'pathLength',
        'patternContentUnits',
        'patternTransform',
        'patternUnits',
        'points',
        'pointsAtX',
        'pointsAtY',
        'pointsAtZ',
        'preserveAlpha',
        'preserveAspectRatio',
        'primitiveUnits',
        'r',
        'radius',
        'refX',
        'refY',
        'rotate',
        'rx',
        'ry',
        'scale',
        'seed',
        'side',
        'spacing',
        'specularConstant',
        'specularExponent',
        'spreadMethod',
        'startOffset',
        'stdDeviation',
        'stitchTiles',
        'surfaceScale',
        'targetX',
        'targetY',
        'textLength',
        'u1',
        'u2',
        'unicode',
        'version',
        'vert-adv-y',
        'vert-origin-x',
        'vert-origin-y',
        'viewBox',
        'x1',
        'x2',
        'xChannelSelector',
        'xlink:href',
        'xlink:title',
        'y1',
        'y2',
        'yChannelSelector',
        'z',
        'zoomAndPan',
    ];

    /**
     * Associative array of all allowed namespace URIs
     *
     * @var array
     */
    public static $allowedNamespaces = [
        ''      => 'http://www.w3.org/2000/svg',
        'xlink' => 'http://www.w3.org/1999/xlink'
    ];

    /**
     * Associative array of all allowed tag names with the value
     * of either an array with the list of all allowed attributes
     * for this tag, `true` to allow any attribute from the
     * `allowedAttrs` list or `false` to allow the tag without
     * any attributes
     *
     * @var array
     */
    public static $allowedTags = [
        'a' => true,
        'altGlyph' => true,
        'altGlyphDef' => true,
        'altGlyphItem' => true,
        'animateColor' => true,
        'animateMotion' => true,
        'animateTransform' => true,
        'circle' => true,
        'clipPath' => true,
        'defs' => true,
        'desc' => true,
        'ellipse' => true,
        'feBlend' => true,
        'feColorMatrix' => true,
        'feComponentTransfer' => true,
        'feComposite' => true,
        'feConvolveMatrix' => true,
        'feDiffuseLighting' => true,
        'feDisplacementMap' => true,
        'feDistantLight' => true,
        'feFlood' => true,
        'feFuncA' => true,
        'feFuncB' => true,
        'feFuncG' => true,
        'feFuncR' => true,
        'feGaussianBlur' => true,
        'feMerge' => true,
        'feMergeNode' => true,
        'feMorphology' => true,
        'feOffset' => true,
        'fePointLight' => true,
        'feSpecularLighting' => true,
        'feSpotLight' => true,
        'feTile' => true,
        'feTurbulence' => true,
        'filter' => true,
        'font' => true,
        'g' => true,
        'glyph' => true,
        'glyphRef' => true,
        'hkern' => true,
        'image' => true,
        'line' => true,
        'linearGradient' => true,
        'marker' => true,
        'mask' => true,
        'metadata' => true,
        'mpath' => true,
        'path' => true,
        'pattern' => true,
        'polygon' => true,
        'polyline' => true,
        'radialGradient' => true,
        'rect' => true,
        'stop' => true,
        'style' => true,
        'svg' => true,
        'switch' => true,
        'symbol' => true,
        'text' => true,
        'textPath' => true,
        'title' => true,
        'tref' => true,
        'tspan' => true,
        'use' => true,
        'view' => true,
        'vkern' => true,
    ];

    /**
     * Array of explicitly disallowed tags
     *
     * IMPORTANT: Use lower-case names here because
     * of the case-insensitive matching
     *
     * @var array
     */
    public static $disallowedTags = [
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
     * Custom callback for additional attribute sanitization
     * @internal
     *
     * @param \DOMAttr $attr
     * @return array Array with exception objects for each modification
     */
    public static function sanitizeAttr(DOMAttr $attr): array
    {
        $element = $attr->ownerElement;
        $name    = $attr->name;
        $value   = $attr->value;
        $errors = [];

        // block nested <use> elements ("Billion Laughs" DoS attack)
        if (
            $element->localName === 'use' &&
            Str::contains($name, 'href') !== false &&
            Str::startsWith($value, '#') === true
        ) {
            // find the target (used element)
            $id = str_replace('"', '', mb_substr($value, 1));
            $target = (new DOMXPath($attr->ownerDocument))->query('//*[@id="' . $id . '"]')->item(0);

            // the target must not contain any other <use> elements
            if (
                is_a($target, 'DOMElement') === true &&
                $target->getElementsByTagName('use')->count() > 0
            ) {
                $errors[] = new InvalidArgumentException(
                    'Nested "use" elements are not allowed' .
                    ' (used in line ' . $element->getLineNo() . ')'
                );
                $element->removeAttributeNode($attr);
            }
        }

        return $errors;
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
        $errors = [];

        // check for URLs inside <style> elements
        if ($element->tagName === 'style') {
            foreach (Dom::extractUrls($element->textContent) as $url) {
                if (Dom::isAllowedUrl($url, static::options()) !== true) {
                    $errors[] = new InvalidArgumentException(
                        'The URL is not allowed in the "style" element' .
                        ' (around line ' . $element->getLineNo() . ')'
                    );
                    Dom::remove($element);
                }
            }
        }

        return $errors;
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
        if (mb_strtolower($doctype->name) !== 'svg') {
            throw new InvalidArgumentException('Invalid doctype');
        }
    }

    /**
     * Returns the sanitization options for the handler
     *
     * @return array
     */
    protected static function options(): array
    {
        return array_merge(parent::options(), [
            'allowedAttrPrefixes' => static::$allowedAttrPrefixes,
            'allowedAttrs'        => static::$allowedAttrs,
            'allowedNamespaces'   => static::$allowedNamespaces,
            'allowedTags'         => static::$allowedTags,
            'disallowedTags'      => static::$disallowedTags,
        ]);
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
        $svg = parent::parse($string);

        // basic validation before we continue sanitizing/validating
        $rootName = $svg->document()->documentElement->nodeName;
        if ($rootName !== 'svg') {
            throw new InvalidArgumentException('The file is not a SVG (got <' . $rootName . '>)');
        }

        return $svg;
    }
}
