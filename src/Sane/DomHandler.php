<?php

namespace Kirby\Sane;

use DOMAttr;
use DOMDocumentType;
use DOMElement;
use Kirby\Toolkit\Dom;

/**
 * Base class for Sane handlers with DOM file types
 * @since 3.5.8
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DomHandler extends Handler
{
	/**
	 * List of all MIME types that may
	 * be used in data URIs
	 */
	public static array $allowedDataUris = [
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

	/**
	 * Allowed hostnames for HTTP(S) URLs
	 *
	 * @var array|true
	 */
	public static array|bool $allowedDomains = true;

	/**
	 * Whether URLs that begin with `/` should be allowed even if the
	 * site index URL is in a subfolder (useful when using the HTML
	 * `<base>` element where the sanitized code will be rendered)
	 */
	public static bool $allowHostRelativeUrls = true;

	/**
	 * Names of allowed XML processing instructions
	 */
	public static array $allowedPIs = [];

	/**
	 * The document type (`'HTML'` or `'XML'`)
	 * (to be set in child classes)
	 */
	protected static string $type = 'XML';

	/**
	 * Sanitizes the given string
	 *
	 * @param bool $isExternal Whether the string is from an external file
	 *                         that may be accessed directly
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file couldn't be parsed
	 */
	public static function sanitize(string $string, bool $isExternal = false): string
	{
		$dom = static::parse($string);
		$dom->sanitize(static::options($isExternal));
		return $dom->toString();
	}

	/**
	 * Validates file contents
	 *
	 * @param bool $isExternal Whether the string is from an external file
	 *                         that may be accessed directly
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file couldn't be parsed
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 */
	public static function validate(string $string, bool $isExternal = false): void
	{
		$dom    = static::parse($string);
		$errors = $dom->sanitize(static::options($isExternal));

		// there may be multiple errors, we can only throw one of them at a time
		if (count($errors) > 0) {
			throw $errors[0];
		}
	}

	/**
	 * Custom callback for additional attribute sanitization
	 * @internal
	 *
	 * @return array Array with exception objects for each modification
	 */
	public static function sanitizeAttr(DOMAttr $attr, array $options): array
	{
		// to be extended in child classes
		return [];
	}

	/**
	 * Custom callback for additional element sanitization
	 * @internal
	 *
	 * @return array Array with exception objects for each modification
	 */
	public static function sanitizeElement(DOMElement $element, array $options): array
	{
		// to be extended in child classes
		return [];
	}

	/**
	 * Custom callback for additional doctype validation
	 * @internal
	 */
	public static function validateDoctype(DOMDocumentType $doctype, array $options): void
	{
		// to be extended in child classes
	}

	/**
	 * Returns the sanitization options for the handler
	 * (to be extended in child classes)
	 *
	 * @param bool $isExternal Whether the string is from an external file
	 *                         that may be accessed directly
	 */
	protected static function options(bool $isExternal): array
	{
		$options = [
			'allowedDataUris'       => static::$allowedDataUris,
			'allowedDomains'        => static::$allowedDomains,
			'allowHostRelativeUrls' => static::$allowHostRelativeUrls,
			'allowedPIs'            => static::$allowedPIs,
			'attrCallback'          => [static::class, 'sanitizeAttr'],
			'doctypeCallback'       => [static::class, 'validateDoctype'],
			'elementCallback'       => [static::class, 'sanitizeElement'],
		];

		// never allow host-relative URLs in external files as we
		// cannot set a `<base>` element for them when accessed directly
		if ($isExternal === true) {
			$options['allowHostRelativeUrls'] = false;
		}

		return $options;
	}

	/**
	 * Parses the given string into a `Toolkit\Dom` object
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file couldn't be parsed
	 */
	protected static function parse(string $string): Dom
	{
		return new Dom($string, static::$type);
	}
}
