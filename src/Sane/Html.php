<?php

namespace Kirby\Sane;

/**
 * Sane handler for HTML files
 * @since 3.5.8
 *
 * @package   Kirby Sane
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Html extends DomHandler
{
	/**
	 * Global list of allowed attribute prefixes
	 */
	public static array $allowedAttrPrefixes = [
		'aria-',
		'data-',
	];

	/**
	 * Global list of allowed attributes
	 */
	public static array $allowedAttrs = [
		'class',
		'id',
	];

	/**
	 * Associative array of all allowed tag names with the value
	 * of either an array with the list of all allowed attributes
	 * for this tag, `true` to allow any attribute from the
	 * `allowedAttrs` list or `false` to allow the tag without
	 * any attributes
	 */
	public static array $allowedTags = [
		'a'          => ['href', 'rel', 'title', 'target'],
		'abbr'       => ['title'],
		'b'          => true,
		'body'       => true,
		'blockquote' => true,
		'br'         => true,
		'code'       => true,
		'dl'         => true,
		'dd'         => true,
		'del'        => true,
		'div'        => true,
		'dt'         => true,
		'em'         => true,
		'footer'     => true,
		'h1'         => true,
		'h2'         => true,
		'h3'         => true,
		'h4'         => true,
		'h5'         => true,
		'h6'         => true,
		'hr'         => true,
		'html'       => true,
		'i'          => true,
		'ins'        => true,
		'li'         => true,
		'small'      => true,
		'span'       => true,
		'strong'     => true,
		'sub'        => true,
		'sup'        => true,
		'ol'         => true,
		'p'          => true,
		'pre'        => true,
		's'          => true,
		'u'          => true,
		'ul'         => true,
	];

	/**
	 * Array of explicitly disallowed tags
	 *
	 * IMPORTANT: Use lower-case names here because
	 * of the case-insensitive matching
	 */
	public static array $disallowedTags = [
		'iframe',
		'meta',
		'object',
		'script',
		'style',
	];

	/**
	 * List of attributes that may contain URLs
	 */
	public static array $urlAttrs = [
		'href',
		'src',
		'xlink:href',
	];

	/**
	 * The document type (`'HTML'` or `'XML'`)
	 */
	protected static string $type = 'HTML';

	/**
	 * Returns the sanitization options for the handler
	 *
	 * @param bool $isExternal Whether the string is from an external file
	 *                         that may be accessed directly
	 */
	protected static function options(bool $isExternal): array
	{
		return array_merge(parent::options($isExternal), [
			'allowedAttrPrefixes' => static::$allowedAttrPrefixes,
			'allowedAttrs'        => static::$allowedAttrs,
			'allowedNamespaces'   => [],
			'allowedPIs'          => [],
			'allowedTags'         => static::$allowedTags,
			'disallowedTags'      => static::$disallowedTags,
			'urlAttrs'            => static::$urlAttrs,
		]);
	}
}
