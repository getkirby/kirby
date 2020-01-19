<?php

namespace Kirby\Toolkit;

/**
 * XML parser and creator Class
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Xml
{
    /**
     * Conversion table for html entities
     *
     * @var array
     */
    public static $entities = [
        '&nbsp;' => '&#160;', '&iexcl;' => '&#161;', '&cent;' => '&#162;', '&pound;' => '&#163;', '&curren;' => '&#164;', '&yen;' => '&#165;', '&brvbar;' => '&#166;', '&sect;' => '&#167;',
        '&uml;' => '&#168;', '&copy;' => '&#169;', '&ordf;' => '&#170;', '&laquo;' => '&#171;', '&not;' => '&#172;', '&shy;' => '&#173;', '&reg;' => '&#174;', '&macr;' => '&#175;',
        '&deg;' => '&#176;', '&plusmn;' => '&#177;', '&sup2;' => '&#178;', '&sup3;' => '&#179;', '&acute;' => '&#180;', '&micro;' => '&#181;', '&para;' => '&#182;', '&middot;' => '&#183;',
        '&cedil;' => '&#184;', '&sup1;' => '&#185;', '&ordm;' => '&#186;', '&raquo;' => '&#187;', '&frac14;' => '&#188;', '&frac12;' => '&#189;', '&frac34;' => '&#190;', '&iquest;' => '&#191;',
        '&Agrave;' => '&#192;', '&Aacute;' => '&#193;', '&Acirc;' => '&#194;', '&Atilde;' => '&#195;', '&Auml;' => '&#196;', '&Aring;' => '&#197;', '&AElig;' => '&#198;', '&Ccedil;' => '&#199;',
        '&Egrave;' => '&#200;', '&Eacute;' => '&#201;', '&Ecirc;' => '&#202;', '&Euml;' => '&#203;', '&Igrave;' => '&#204;', '&Iacute;' => '&#205;', '&Icirc;' => '&#206;', '&Iuml;' => '&#207;',
        '&ETH;' => '&#208;', '&Ntilde;' => '&#209;', '&Ograve;' => '&#210;', '&Oacute;' => '&#211;', '&Ocirc;' => '&#212;', '&Otilde;' => '&#213;', '&Ouml;' => '&#214;', '&times;' => '&#215;',
        '&Oslash;' => '&#216;', '&Ugrave;' => '&#217;', '&Uacute;' => '&#218;', '&Ucirc;' => '&#219;', '&Uuml;' => '&#220;', '&Yacute;' => '&#221;', '&THORN;' => '&#222;', '&szlig;' => '&#223;',
        '&agrave;' => '&#224;', '&aacute;' => '&#225;', '&acirc;' => '&#226;', '&atilde;' => '&#227;', '&auml;' => '&#228;', '&aring;' => '&#229;', '&aelig;' => '&#230;', '&ccedil;' => '&#231;',
        '&egrave;' => '&#232;', '&eacute;' => '&#233;', '&ecirc;' => '&#234;', '&euml;' => '&#235;', '&igrave;' => '&#236;', '&iacute;' => '&#237;', '&icirc;' => '&#238;', '&iuml;' => '&#239;',
        '&eth;' => '&#240;', '&ntilde;' => '&#241;', '&ograve;' => '&#242;', '&oacute;' => '&#243;', '&ocirc;' => '&#244;', '&otilde;' => '&#245;', '&ouml;' => '&#246;', '&divide;' => '&#247;',
        '&oslash;' => '&#248;', '&ugrave;' => '&#249;', '&uacute;' => '&#250;', '&ucirc;' => '&#251;', '&uuml;' => '&#252;', '&yacute;' => '&#253;', '&thorn;' => '&#254;', '&yuml;' => '&#255;',
        '&fnof;' => '&#402;', '&Alpha;' => '&#913;', '&Beta;' => '&#914;', '&Gamma;' => '&#915;', '&Delta;' => '&#916;', '&Epsilon;' => '&#917;', '&Zeta;' => '&#918;', '&Eta;' => '&#919;',
        '&Theta;' => '&#920;', '&Iota;' => '&#921;', '&Kappa;' => '&#922;', '&Lambda;' => '&#923;', '&Mu;' => '&#924;', '&Nu;' => '&#925;', '&Xi;' => '&#926;', '&Omicron;' => '&#927;',
        '&Pi;' => '&#928;', '&Rho;' => '&#929;', '&Sigma;' => '&#931;', '&Tau;' => '&#932;', '&Upsilon;' => '&#933;', '&Phi;' => '&#934;', '&Chi;' => '&#935;', '&Psi;' => '&#936;',
        '&Omega;' => '&#937;', '&alpha;' => '&#945;', '&beta;' => '&#946;', '&gamma;' => '&#947;', '&delta;' => '&#948;', '&epsilon;' => '&#949;', '&zeta;' => '&#950;', '&eta;' => '&#951;',
        '&theta;' => '&#952;', '&iota;' => '&#953;', '&kappa;' => '&#954;', '&lambda;' => '&#955;', '&mu;' => '&#956;', '&nu;' => '&#957;', '&xi;' => '&#958;', '&omicron;' => '&#959;',
        '&pi;' => '&#960;', '&rho;' => '&#961;', '&sigmaf;' => '&#962;', '&sigma;' => '&#963;', '&tau;' => '&#964;', '&upsilon;' => '&#965;', '&phi;' => '&#966;', '&chi;' => '&#967;',
        '&psi;' => '&#968;', '&omega;' => '&#969;', '&thetasym;' => '&#977;', '&upsih;' => '&#978;', '&piv;' => '&#982;', '&bull;' => '&#8226;', '&hellip;' => '&#8230;', '&prime;' => '&#8242;',
        '&Prime;' => '&#8243;', '&oline;' => '&#8254;', '&frasl;' => '&#8260;', '&weierp;' => '&#8472;', '&image;' => '&#8465;', '&real;' => '&#8476;', '&trade;' => '&#8482;', '&alefsym;' => '&#8501;',
        '&larr;' => '&#8592;', '&uarr;' => '&#8593;', '&rarr;' => '&#8594;', '&darr;' => '&#8595;', '&harr;' => '&#8596;', '&crarr;' => '&#8629;', '&lArr;' => '&#8656;', '&uArr;' => '&#8657;',
        '&rArr;' => '&#8658;', '&dArr;' => '&#8659;', '&hArr;' => '&#8660;', '&forall;' => '&#8704;', '&part;' => '&#8706;', '&exist;' => '&#8707;', '&empty;' => '&#8709;', '&nabla;' => '&#8711;',
        '&isin;' => '&#8712;', '&notin;' => '&#8713;', '&ni;' => '&#8715;', '&prod;' => '&#8719;', '&sum;' => '&#8721;', '&minus;' => '&#8722;', '&lowast;' => '&#8727;', '&radic;' => '&#8730;',
        '&prop;' => '&#8733;', '&infin;' => '&#8734;', '&ang;' => '&#8736;', '&and;' => '&#8743;', '&or;' => '&#8744;', '&cap;' => '&#8745;', '&cup;' => '&#8746;', '&int;' => '&#8747;',
        '&there4;' => '&#8756;', '&sim;' => '&#8764;', '&cong;' => '&#8773;', '&asymp;' => '&#8776;', '&ne;' => '&#8800;', '&equiv;' => '&#8801;', '&le;' => '&#8804;', '&ge;' => '&#8805;',
        '&sub;' => '&#8834;', '&sup;' => '&#8835;', '&nsub;' => '&#8836;', '&sube;' => '&#8838;', '&supe;' => '&#8839;', '&oplus;' => '&#8853;', '&otimes;' => '&#8855;', '&perp;' => '&#8869;',
        '&sdot;' => '&#8901;', '&lceil;' => '&#8968;', '&rceil;' => '&#8969;', '&lfloor;' => '&#8970;', '&rfloor;' => '&#8971;', '&lang;' => '&#9001;', '&rang;' => '&#9002;', '&loz;' => '&#9674;',
        '&spades;' => '&#9824;', '&clubs;' => '&#9827;', '&hearts;' => '&#9829;', '&diams;' => '&#9830;', '&quot;' => '&#34;', '&amp;' => '&#38;', '&lt;' => '&#60;', '&gt;' => '&#62;', '&OElig;' => '&#338;',
        '&oelig;' => '&#339;', '&Scaron;' => '&#352;', '&scaron;' => '&#353;', '&Yuml;' => '&#376;', '&circ;' => '&#710;', '&tilde;' => '&#732;', '&ensp;' => '&#8194;', '&emsp;' => '&#8195;',
        '&thinsp;' => '&#8201;', '&zwnj;' => '&#8204;', '&zwj;' => '&#8205;', '&lrm;' => '&#8206;', '&rlm;' => '&#8207;', '&ndash;' => '&#8211;', '&mdash;' => '&#8212;', '&lsquo;' => '&#8216;',
        '&rsquo;' => '&#8217;', '&sbquo;' => '&#8218;', '&ldquo;' => '&#8220;', '&rdquo;' => '&#8221;', '&bdquo;' => '&#8222;', '&dagger;' => '&#8224;', '&Dagger;' => '&#8225;', '&permil;' => '&#8240;',
        '&lsaquo;' => '&#8249;', '&rsaquo;' => '&#8250;', '&euro;' => '&#8364;'
    ];

    /**
     * Generates a single attribute or a list of attributes
     *
     * @param string|array $name String: A single attribute with that name will be generated.
     *                           Key-value array: A list of attributes will be generated. Don't pass a second argument in that case.
     * @param mixed $value If used with a `$name` string, pass the value of the attribute here.
     *                     If used with a `$name` array, this can be set to `false` to disable attribute sorting.
     * @return string|null The generated XML attributes string
     */
    public static function attr($name, $value = null): ?string
    {
        if (is_array($name) === true) {
            if ($value !== false) {
                ksort($name);
            }

            $attributes = [];
            foreach ($name as $key => $val) {
                $a = static::attr($key, $val);

                if ($a) {
                    $attributes[] = $a;
                }
            }

            return implode(' ', $attributes);
        }

        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if ($value === ' ') {
            return strtolower($name) . '=""';
        }

        if (is_bool($value) === true) {
            return $value === true ? strtolower($name) . '="' . strtolower($name) . '"' : null;
        }

        if (is_array($value) === true) {
            if (isset($value['value'], $value['escape'])) {
                $value = $value['escape'] === true ? static::encode($value['value']) : $value['value'];
            } else {
                $value = implode(' ', array_filter($value, function ($value) {
                    return !empty($value) || is_numeric($value);
                }));
            }
        } else {
            $value = static::encode($value);
        }

        return strtolower($name) . '="' . $value . '"';
    }

    /**
     * Creates an XML string from an array
     *
     * @param string $props The source array
     * @param string $name The name of the root element
     * @param bool $head Include the xml declaration head or not
     * @param int $level The indendation level
     * @return string The XML string
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
     * Removes all XML tags and encoded chars from a string
     *
     * ```
     * echo Xml::decode('some &uuml;ber <em>crazy</em> stuff');
     * // output: some über crazy stuff
     * ```
     *
     * @param string $string
     * @return string
     */
    public static function decode(string $string): string
    {
        $string = strip_tags($string);
        return html_entity_decode($string, ENT_COMPAT, 'utf-8');
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
     * @param string $string
     * @param bool $html True: convert to html first
     * @return string
     */
    public static function encode(string $string = null, bool $html = true): string
    {
        if ($html === true) {
            $string = Html::encode($string, false);
        }

        $entities = static::entities();
        $searches = array_keys($entities);
        $values   = array_values($entities);

        return str_replace($searches, $values, $string);
    }

    /**
     * Returns the html to xml entities translation table
     *
     * @return array
     */
    public static function entities(): array
    {
        return static::$entities;
    }

    /**
     * Parses a XML string and returns an array
     *
     * @param string $xml
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
     * @param mixed $indent
     * @return string
     */
    public static function tag(string $name, $content = null, array $attr = null, $indent = null): string
    {
        $attr  = Html::attr($attr);
        $start = '<' . $name . ($attr ? ' ' . $attr : null) . '>';
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

        return '<![CDATA[' . static::encode($value) . ']]>';
    }
}
