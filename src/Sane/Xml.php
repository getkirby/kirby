<?php

namespace Kirby\Sane;

use DOMDocumentType;
use DOMElement;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Sane handler for XML files
 * @since 3.5.4
 *
 * @package   Kirby Sane
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Xml extends DomHandler
{
	/**
	 * Custom callback for additional element sanitization
	 * @internal
	 *
	 * @return array Array with exception objects for each modification
	 */
	public static function sanitizeElement(
		DOMElement $element,
		array $options
	): array {
		$errors = [];

		// if we are validating an XML file, block all SVG and HTML namespaces
		if (static::class === self::class) {
			$xml        = simplexml_import_dom($element);
			$namespaces = $xml->getDocNamespaces(false, false);

			foreach ($namespaces as $namespace => $value) {
				if (
					Str::contains($value, 'html', true) === true ||
					Str::contains($value, 'svg', true) === true
				) {
					$element->removeAttributeNS($value, $namespace);
					$errors[] = new InvalidArgumentException(
						'The namespace "' . $value . '" is not allowed' .
						' (around line ' . $element->getLineNo() . ')'
					);
				}
			}
		}

		return $errors;
	}

	/**
	 * Custom callback for additional doctype validation
	 * @internal
	 */
	public static function validateDoctype(
		DOMDocumentType $doctype,
		array $options
	): void {
		// if we are validating an XML file, block all SVG and HTML doctypes
		if (
			static::class === self::class &&
			(
				Str::contains($doctype->name, 'html', true) === true ||
				Str::contains($doctype->name, 'svg', true) === true
			)
		) {
			throw new InvalidArgumentException(
				message: 'The doctype is not allowed in XML files'
			);
		}
	}
}
