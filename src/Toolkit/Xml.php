<?php

namespace Kirby\Toolkit;

/**
* XML parser and creator Class
*
* @package   Kirby Toolkit
* @author    Bastian Allgeier <bastian@getkirby.com>
* @link      http://getkirby.com
* @copyright Bastian Allgeier
* @license   http://www.opensource.org/licenses/mit-license.php MIT License
*/
class Xml
{

    /**
     * Encodes the value as cdata if necessary
     *
     * @param mixed $value
     * @return mixed
     */
    public static function value($value)
    {
        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (is_numeric($value) === true) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (Str::contains($value, '<![CDATA[') === true) {
            return $value;
        }

        $encoded = htmlentities($value);

        if ($encoded === $value) {
            return $value;
        }

        return '<![CDATA[' . $value . ']]>';
    }

    /**
     * Creates an XML string from an array
     *
     * @param  string  $props   The source array
     * @param  string  $name    The name of the root element
     * @param  boolean $head    Include the xml declaration head or not
     * @param  int     $level   The indendation level
     * @return string  The XML string
     */
    public static function create($props, string $name = 'root', bool $head = true, $level = 0): string
    {
        $attributes = $props['@attributes'] ?? null;
        $value      = $props['@value'] ?? null;
        $children   = $props;
        $indent     = str_repeat('  ', $level);
        $nextLevel  = $level + 1;

        if (is_array($children) === true) {

            unset($children['@attributes'], $children['@value']);

            $childTags = [];

            foreach ($children as $childName => $childItems) {

                if (is_array($childItems) === true) {

                    // another tag with attributes
                    if (A::isAssociative($childItems) === true) {
                        $childTags[] = static::create($childItems, $childName, false, $level);

                    // just children
                    } else {
                        foreach ($childItems as $childItem) {
                            $childTags[] = static::create($childItem, $childName, false, $nextLevel);
                        }
                    }

                } else {
                    $childTags[] = static::tag($childName, $childItems, null, $indent);
                }
            }

            if (empty($childTags) === false) {
                $value = $childTags;
            }

        }

        $result  = $head === true ? '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL : null;
        $result .= static::tag($name, $value, $attributes, $indent);

        return $result;
    }

    /**
     * Removes all xml entities from a string
     * and convert them to html entities first
     * and remove all html entities afterwards.
     *
     * <code>
     *
     * echo xml::decode('some <em>&#252;ber</em> crazy stuff');
     * // output: some &uuml;ber crazy stuff
     *
     * </code>
     *
     * @param  string  $string
     * @return string
     */
    public static function decode(string $string = null): string
    {
        return Html::decode(strtr($string, static::entities()));
    }

    /**
     * Converts a string to a xml-safe string
     * Converts it to html-safe first and then it
     * will replace html entities to xml entities
     *
     * <code>
     *
     * echo xml::encode('some über crazy stuff');
     * // output: some &#252;ber crazy stuff
     *
     * </code>
     *
     * @param  string  $string
     * @param  boolean $html True: convert to html first
     * @return string
     */
    public static function encode(string $string = null, bool $html = true): string
    {
        // convert raw text to html safe text
        if ($html === true) {
            $string = Html::encode($string, false);
        }

        // convert html entities to xml entities
        return strtr($string, Html::entities());
    }

    /**
     * Returns a translation table of xml entities to html entities
     *
     * @return array
     */
    public static function entities(): array
    {
        return array_flip(Html::entities());
    }

    /**
     * Parses a XML string and returns an array
     *
     * @param  string  $xml
     * @return array|false
     */
    public static function parse(string $xml = null)
    {
        $xml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $xml);
        $xml = @simplexml_load_string($xml, null, LIBXML_NOENT | LIBXML_NOCDATA);

        $xml = @json_encode($xml);
        $xml = @json_decode($xml, true);
        return is_array($xml) === true ? $xml : false;
    }

    /**
     * Builds an XML tag
     *
     * @param string $name
     * @param mixed $content
     * @param array $attr
     * @return string
     */
    public static function tag(string $name, $content = null, array $attr = null, $indent = null): string
    {
        $attr  = Html::attr($attr);
        $start = '<' .  $name . ($attr ? ' ' . $attr : null) . '>';
        $end   = '</' . $name . '>';

        if (is_array($content) === true) {
            $xml = $indent . $start . PHP_EOL;
            foreach ($content as $line) {
                $xml .= $indent . $indent . $line . PHP_EOL;
            }
            $xml .= $indent . $end;
        } else {
            $xml = $indent . $start . static::value($content) . $end;
        }

        return $xml;
    }

}
