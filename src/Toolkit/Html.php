<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * Html builder for the most common elements
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Html
{

    /**
     * An internal store for a html entities translation table
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
     * Can be used to switch to trailing slashes if required
     *
     * ```php
     * html::$void = ' />'
     * ```
     *
     * @var string $void
     */
    public static $void = '>';

    /**
     * Generic HTML tag generator
     *
     * @param string $tag
     * @param array $arguments
     * @return string
     */
    public static function __callStatic(string $tag, array $arguments = []): string
    {
        if (static::isVoid($tag) === true) {
            return Html::tag($tag, null, ...$arguments);
        }

        return Html::tag($tag, ...$arguments);
    }

    /**
     * Generates an a tag
     *
     * @param string $href The url for the a tag
     * @param mixed $text The optional text. If null, the url will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string the generated html
     */
    public static function a(string $href = null, $text = null, array $attr = []): string
    {
        $attr = array_merge(['href' => $href], $attr);

        if (empty($text) === true) {
            $text = $href;
        }

        // add rel=noopener to target blank links to improve security
        $attr['rel'] = static::rel($attr['rel'] ?? null, $attr['target'] ?? null);

        return static::tag('a', $text, $attr);
    }

    /**
     * Generates a single attribute or a list of attributes
     *
     * @param string $name mixed string: a single attribute with that name will be generated. array: a list of attributes will be generated. Don't pass a second argument in that case.
     * @param string $value if used for a single attribute, pass the content for the attribute here
     * @return string the generated html
     */
    public static function attr($name, $value = null): string
    {
        if (is_array($name) === true) {
            $attributes = [];

            asort($name);

            foreach ($name as $key => $val) {
                $a = static::attr($key, $val);

                if ($a) {
                    $attributes[] = $a;
                }
            }

            return implode(' ', $attributes);
        }

        if ($value === null || $value === '' || $value === []) {
            return false;
        }

        if ($value === ' ') {
            return strtolower($name) . '=""';
        }

        if (is_bool($value) === true) {
            return $value === true ? strtolower($name) : '';
        }

        if (is_array($value) === true) {
            if (isset($value['value']) && isset($value['escape'])) {
                $value = $value['escape'] === true ? htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8') : $value['value'];
            } else {
                $value = implode(' ', $value);
            }
        } else {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return strtolower($name) . '="' . $value . '"';
    }

    /**
     * Converts lines in a string into html breaks
     *
     * @param string $string
     * @return string
     */
    public static function breaks(string $string = null): string
    {
        return nl2br($string);
    }

    /**
     * Removes all html tags and encoded chars from a string
     *
     * <code>
     *
     * echo html::decode('some <em>crazy</em> stuff');
     * // output: some uber crazy stuff
     *
     * </code>
     *
     * @param  string  $string
     * @return string  The html string
     */
    public static function decode(string $string = null): string
    {
        $string = strip_tags($string);
        return html_entity_decode($string, ENT_COMPAT, 'utf-8');
    }

    /**
     * Generates an "a mailto" tag
     *
     * @param string $email The url for the a tag
     * @param mixed $text The optional text. If null, the url will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string the generated html
     */
    public static function email(string $email, string $text = null, array $attr = []): string
    {
        if (empty($text) === true) {
            // show only the eMail address without additional parameters (if the 'text' argument is empty)
            $text = [Str::encode(Str::split($email, '?')[0])];
        }

        $email = Str::encode($email);
        $attr  = array_merge([
            'href' => [
                'value'  => 'mailto:' . $email,
                'escape' => false
            ]
        ], $attr);

        // add rel=noopener to target blank links to improve security
        $attr['rel'] = static::rel($attr['rel'] ?? null, $attr['target'] ?? null);

        return static::tag('a', $text, $attr);
    }

    /**
     * Converts a string to a html-safe string
     *
     * @param  string  $string
     * @param  boolean $keepTags True: lets stuff inside html tags untouched.
     * @return string  The html string
     */
    public static function encode(string $string = null, bool $keepTags = true): string
    {
        if ($keepTags === true) {
            return stripslashes(implode('', preg_replace_callback('/^([^<].+[^>])$/', function ($match) {
                return htmlentities($match[1], ENT_COMPAT, 'utf-8');
            }, preg_split('/(<.+?>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE))));
        }

        return htmlentities($string, ENT_COMPAT, 'utf-8');
    }

    /**
     * Returns the full array with all HTML entities
     *
     * @return array
     */
    public static function entities(): array
    {
        return static::$entities;
    }

    /**
     * Creates a figure tag with optional caption
     *
     * @param string|array $content
     * @param string|array $caption
     * @param array $attr
     * @return string
     */
    public static function figure($content, $caption = null, array $attr = []): string
    {
        if ($caption) {
            $figcaption = static::tag('figcaption', $caption);

            if (is_string($content) === true) {
                $content = [static::encode($content, false)];
            }

            $content[] = $figcaption;
        }

        return static::tag('figure', $content, $attr);
    }

    /**
     * Embeds a gist
     *
     * @param string $url
     * @param string $file
     * @param array $attr
     * @return string
     */
    public static function gist(string $url, string $file = null, array $attr = []): string
    {
        if ($file === null) {
            $src = $url . '.js';
        } else {
            $src = $url . '.js?file=' . $file;
        }

        return static::tag('script', null, array_merge($attr, [
            'src' => $src
        ]));
    }

    /**
     * Creates an iframe
     *
     * @param string $src
     * @param array $attr
     * @return string
     */
    public static function iframe(string $src, array $attr = []): string
    {
        return static::tag('iframe', null, array_merge(['src' => $src], $attr));
    }

    /**
     * Generates an img tag
     *
     * @param string $src The url of the image
     * @param array $attr Additional attributes for the image tag
     * @return string the generated html
     */
    public static function img(string $src, array $attr = []): string
    {
        $attr = array_merge([
            'src' => $src,
            'alt' => ' '
        ], $attr);

        return static::tag('img', null, $attr);
    }

    /**
     * Checks if a tag is self-closing
     *
     * @param string $tag
     * @return bool
     */
    public static function isVoid(string $tag): bool
    {
        $void = [
            'area',
            'base',
            'br',
            'col',
            'command',
            'embed',
            'hr',
            'img',
            'input',
            'keygen',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr',
        ];

        return in_array(strtolower($tag), $void);
    }

    /**
     * Add noopeener noreferrer to rels when target is _blank
     *
     * @param string $rel
     * @param string $target
     * @return string|null
     */
    public static function rel(string $rel = null, string $target = null)
    {
        if ($target === '_blank') {
            return trim($rel . ' noopener noreferrer');
        }

        return $rel;
    }

    /**
     * Generates an Html tag with optional content and attributes
     *
     * @param string $name The name of the tag, i.e. "a"
     * @param mixed $content The content if availble. Pass null to generate a self-closing tag, Pass an empty string to generate empty content
     * @param array $attr An associative array with additional attributes for the tag
     * @return string The generated Html
     */
    public static function tag(string $name, $content = null, array $attr = []): string
    {
        $html = '<' . $name;
        $attr = static::attr($attr);

        if (empty($attr) === false) {
            $html .= ' ' . $attr;
        }

        if (static::isVoid($name) === true) {
            $html .= static::$void;
        } else {
            if (is_array($content) === true) {
                $content = implode($content);
            } else {
                $content = static::encode($content, false);
            }

            $html .= '>' . $content . '</' . $name . '>';
        }

        return $html;
    }

    /**
     * Creates a video embed via iframe for Youtube or Vimeo
     * videos. The embed Urls are automatically detected from
     * the given Url.
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string
     */
    public static function video(string $url, array $options = [], array $attr = []): string
    {
        // YouTube video
        if (preg_match('!youtu!i', $url) === 1) {
            return static::youtube($url, $options['youtube'] ?? [], $attr);
        }

        // Vimeo video
        if (preg_match('!vimeo!i', $url) === 1) {
            return static::vimeo($url, $options['vimeo'] ?? [], $attr);
        }

        throw new Exception('Unexpected video type');
    }

    /**
     * Embeds a Vimeo video by URL in an iframe
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string
     */
    public static function vimeo(string $url, array $options = [], array $attr = []): string
    {
        if (preg_match('!vimeo.com\/([0-9]+)!i', $url, $array) === 1) {
            $id = $array[1];
        } else {
            throw new Exception('Invalid Vimeo source');
        }

        // build the options query
        if (!empty($options)) {
            $query = '?' . http_build_query($options);
        } else {
            $query = '';
        }

        $url = 'https://player.vimeo.com/video/' . $id . $query;

        return static::iframe($url, array_merge(['allowfullscreen' => true], $attr));
    }

    /**
     * Embeds a Youtube video by URL in an iframe
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string
     */
    public static function youtube(string $url, array $options = [], array $attr = []): string
    {
        // youtube embed domain
        $domain = 'youtube.com';
        $id     = null;

        $schemes = [
            // http://www.youtube.com/embed/d9NF2edxy-M
            ['pattern' => 'youtube.com\/embed\/([a-zA-Z0-9_-]+)'],
            // https://www.youtube-nocookie.com/embed/d9NF2edxy-M
            [
                'pattern' => 'youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)',
                'domain'  => 'www.youtube-nocookie.com'
            ],
            // https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M
            [
                'pattern' => 'youtube-nocookie.com\/watch\?v=([a-zA-Z0-9_-]+)',
                'domain'  => 'www.youtube-nocookie.com'
            ],
            // http://www.youtube.com/watch?v=d9NF2edxy-M
            ['pattern' => 'v=([a-zA-Z0-9_-]+)'],
            // http://youtu.be/d9NF2edxy-M
            ['pattern' => 'youtu.be\/([a-zA-Z0-9_-]+)']
        ];

        foreach ($schemes as $schema) {
            if (preg_match('!' . $schema['pattern'] . '!i', $url, $array) === 1) {
                $domain = $schema['domain'] ?? $domain;
                $id     = $array[1];
                break;
            }
        }

        // no match
        if ($id === null) {
            throw new Exception('Invalid Youtube source');
        }

        // build the options query
        if (!empty($options)) {
            $query = '?' . http_build_query($options);
        } else {
            $query = '';
        }

        $url = 'https://' . $domain . '/embed/' . $id . $query;

        return static::iframe($url, array_merge(['allowfullscreen' => true], $attr));
    }
}
