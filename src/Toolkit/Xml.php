<?php

namespace Kirby\Toolkit;

use SimpleXMLElement;

/**
 * XML parser and creator class
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Xml
{
	/**
	 * HTML to XML conversion table for entities
	 *
	 * @var array<string, string>|null
	 */
	public static array|null $entities = [
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
	 * Closing string for void tags
	 */
	public static string $void = ' />';

	/**
	 * Generates a single attribute or a list of attributes
	 *
	 * @param $name String: A single attribute with that name will be generated.
	 *             Key-value array: A list of attributes will be generated. Don't pass a second argument in that case.
	 * @param $value If used with a `$name` string, pass the value of the attribute here.
	 *              If used with a `$name` array, this can be set to `false` to disable attribute sorting.
	 */
	public static function attr(
		string|array $name,
		$value = null
	): string|null {
		if (is_array($name) === true) {
			if ($value !== false) {
				ksort($name);
			}

			$attributes = [];

			foreach ($name as $key => $val) {
				if (is_int($key) === true) {
					$key = $val;
					$val = true;
				}

				if ($attribute = static::attr($key, $val)) {
					$attributes[] = $attribute;
				}
			}

			return implode(' ', $attributes);
		}

		if ($value === null || $value === false || $value === []) {
			return null;
		}

		if ($value === true) {
			return $name . '="' . $name . '"';
		}

		if (is_array($value) === false) {
			$value = static::encode($value);
		} elseif (isset($value['value'], $value['escape']) === true) {
			$value = match ($value['escape']) {
				true    => static::encode($value['value']),
				default => $value['value']
			};
		} else {
			$value = implode(' ', array_filter(
				$value,
				fn ($value) => !empty($value) || is_numeric($value)
			));
		}

		return $name . '="' . $value . '"';
	}

	/**
	 * Creates an XML string from an array
	 *
	 * Supports special array keys `@name` (element name),
	 * `@attributes` (XML attribute key-value array),
	 * `@namespaces` (array with XML namespaces) and
	 * `@value` (element content)
	 *
	 * @param $props The source array or tag content (used internally)
	 * @param $name Name of the root element
	 * @param $head Whether to include the XML declaration head
	 * @param $indent Indentation string
	 * @param $level Indentation level (used internally)
	 */
	public static function create(
		array|string $props,
		string $name = 'root',
		bool $head = true,
		string $indent = '  ',
		int $level = 0
	): string {
		if (is_array($props) === true) {
			if (A::isAssociative($props) === true) {
				// a tag with attributes or named children
				$name       = $props['@name'] ?? $name;
				$attributes = $props['@attributes'] ?? [];
				$value      = $props['@value'] ?? null;

				foreach ($props['@namespaces'] ?? [] as $prefix => $namespace) {
					$attributes['xmlns' . ($prefix ? ':' . $prefix : '')] = $namespace;
				}

				// continue with just the children
				unset(
					$props['@name'],
					$props['@attributes'],
					$props['@namespaces'],
					$props['@value']
				);

				if ($props !== []) {
					// there are children, use them instead of the value

					$value = [];
					foreach ($props as $key => $child) {
						// render the child, but don't include the
						// indentation of the first line
						$value[] = trim(static::create(
							$child,
							$key,
							false,
							$indent,
							$level + 1
						));
					}
				}

				$result = static::tag(
					$name,
					$value,
					$attributes,
					$indent,
					$level
				);
			} else {
				// just children

				$result = [];

				foreach ($props as $child) {
					$result[] = static::create(
						$child,
						$name,
						false,
						$indent,
						$level
					);
				}

				$result = implode(PHP_EOL, $result);
			}
		} else {
			$result = static::tag($name, $props, [], $indent, $level);
		}

		if ($head === true) {
			return '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $result;
		}

		return $result;
	}

	/**
	 * Removes all HTML/XML tags and encoded chars from a string
	 *
	 * ```php
	 * echo Xml::decode('some &uuml;ber <em>crazy</em> stuff');
	 * // output: some über crazy stuff
	 * ```
	 */
	public static function decode(string|null $string): string
	{
		$string = strip_tags($string ?? '');
		return html_entity_decode($string, ENT_COMPAT, 'utf-8');
	}

	/**
	 * Converts a string to an XML-safe string
	 *
	 * Converts it to HTML-safe first and then it
	 * will replace HTML entities with XML entities
	 *
	 * ```php
	 * echo Xml::encode('some über crazy stuff');
	 * // output: some &#252;ber crazy stuff
	 * ```
	 *
	 * @param $html Whether to convert to HTML-safe first
	 */
	public static function encode(
		string|null $string,
		bool $html = true
	): string {
		if ($string === null) {
			return '';
		}

		if ($html === true) {
			$string = Html::encode($string, false);
		}

		// cache the keys/values arrays;
		// invalidates if $entities changes
		static $cached = null;
		static $html   = [];
		static $xml    = [];

		$entities = self::entities();

		if ($cached !== $entities) {
			$html   = array_keys($entities);
			$xml    = array_values($entities);
			$cached = $entities;
		}

		return str_replace($html, $xml, $string);
	}

	/**
	 * Returns the HTML-to-XML entity translation table
	 *
	 * @return array<string, string>
	 */
	public static function entities(): array
	{
		return self::$entities ?? [];
	}

	/**
	 * Parses an XML string and returns an array
	 *
	 * @return array<string, mixed>|null Parsed array or `null` on error
	 */
	public static function parse(string $xml): array|null
	{
		$element = @simplexml_load_string($xml);

		if ($element === false) {
			return null;
		}

		return static::simplify($element);
	}

	/**
	 * Breaks a SimpleXMLElement down into a simpler tree
	 * structure of arrays and strings
	 *
	 * @param $collectName Whether the element name should be collected (for the root element)
	 * @return ($collectName is true ? array<string, mixed> : array<string, mixed>|string)
	 */
	public static function simplify(
		SimpleXMLElement $element,
		bool $collectName = true
	): array|string {
		// get all XML namespaces of the whole document to iterate over later;
		// we don't need the global namespace (empty string) in the list
		$usedNamespaces = $element->getNamespaces(true);

		unset($usedNamespaces['']);

		// now collect element metadata of the parent
		$array = [];

		if ($collectName === true) {
			$array['@name'] = $element->getName();
		}

		// collect attributes with each defined document namespace;
		// also check for attributes without any namespace
		$attributeArray = [];

		foreach ([0 => null, ...array_keys($usedNamespaces)] as $namespace) {
			$prefix     = $namespace ? $namespace . ':' : '';
			$attributes = $element->attributes($namespace, true);

			foreach ($attributes as $key => $value) {
				$attributeArray[$prefix . $key] = (string)$value;
			}
		}

		/** @psalm-suppress RedundantCondition */
		if ($attributeArray !== []) {
			$array['@attributes'] = $attributeArray;
		}

		// collect namespace definitions of this particular XML element
		if ($namespaces = $element->getDocNamespaces(false, false)) {
			$array['@namespaces'] = $namespaces;
		}

		// check for children with each defined document namespace;
		// also check for children without any namespace
		$hasChildren = false;

		foreach ([0 => null, ...array_keys($usedNamespaces)] as $namespace) {
			$prefix   = $namespace ? $namespace . ':' : '';
			$children = $element->children($namespace, true);

			if (count($children) > 0) {
				// there are children, recursively simplify each one
				$hasChildren = true;

				// make a grouped collection of elements per element name
				foreach ($children as $child) {
					$array[$prefix . $child->getName()][] = static::simplify($child, false);
				}
			}
		}

		if ($hasChildren === true) {
			// there were children of any namespace

			// reduce elements where there is only one item
			// of the respective type to a simple string;
			// don't do anything with special `@` metadata keys
			foreach ($array as $name => $item) {
				if (
					str_starts_with($name, '@') === false &&
					count($item) === 1
				) {
					$array[$name] = $item[0];
				}
			}

			return $array;
		}

		// we didn't find any XML children above, only use the string value
		$element = (string)$element;

		if ($array === []) {
			return $element;
		}

		$array['@value'] = $element;

		return $array;
	}

	/**
	 * Builds an XML tag
	 *
	 * @param $content Scalar value or array with multiple lines of content or `null` to
	 *                generate a self-closing tag; pass an empty string to generate empty content
	 * @param $attr An associative array with additional attributes for the tag
	 * @param $indent Indentation string or `null` for output on one line
	 * @param $level Indentation level
	 */
	public static function tag(
		string $name,
		array|string|null $content = '',
		array $attr = [],
		string|null $indent = null,
		int $level = 0
	): string {
		$attr       = static::attr($attr);
		$start      = '<' . $name . ($attr ? ' ' . $attr : '') . '>';
		$startShort = '<' . $name . ($attr ? ' ' . $attr : '') . static::$void;
		$end        = '</' . $name . '>';
		$baseIndent = match ($indent) {
			null    => '',
			default => str_repeat($indent, $level)
		};

		if (is_array($content) === false) {
			return match ($content) {
				null    => $baseIndent . $startShort,
				default => $baseIndent . $start . static::value($content) . $end
			};
		}

		if (is_string($indent) === false) {
			return $start . implode($content) . $end;
		}

		$xml = $baseIndent . $start . PHP_EOL;

		foreach ($content as $line) {
			$xml .= $baseIndent . $indent . $line . PHP_EOL;
		}

		$xml .= $baseIndent . $end;

		return $xml;
	}

	/**
	 * Properly encodes tag contents
	 */
	public static function value($value): string|null
	{
		if ($value === true) {
			return 'true';
		}

		if ($value === false) {
			return 'false';
		}

		if (is_numeric($value) === true) {
			return (string)$value;
		}

		if ($value === null || $value === '') {
			return null;
		}

		// accept raw CDATA only if the entire string is made up of
		// complete consecutive CDATA blocks without any content outside
		if (
			Str::startsWith($value, '<![CDATA[') === true &&
			Str::endsWith($value, ']]>') === true &&
			Str::matches($value, '/\]\]>(?!<!\[CDATA\[|$)/') === false
		) {
			return $value;
		}

		$encoded = htmlentities($value, ENT_NOQUOTES | ENT_XML1);

		// no CDATA block needed
		if ($encoded === $value) {
			return $value;
		}

		// wrap everything in a CDATA block
		// and ensure that it is not closed in the input string
		return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $value) . ']]>';
	}
}
