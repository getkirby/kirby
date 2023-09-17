<?php
/**
 * Class Base64
 *
 * @created      23.06.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\Authenticator\Common;

use InvalidArgumentException;
use ParagonIE\ConstantTime\Base64 as ConstantTimeBase64;
use function function_exists;
use function preg_match;

/**
 * Class to provide base64 encoding/decoding of strings using constant time functions
 */
class Base64{

	/**
	 * The Base64 character set as defined by RFC3548
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc3548#section-3
	 * @see https://datatracker.ietf.org/doc/html/rfc4648#section-4
	 *
	 * @var string
	 */
	public const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

	/**
	 * Encode a string to Base64
	 */
	public static function encode(string $str):string{

		if(function_exists('sodium_bin2base64')){
			return sodium_bin2base64($str, \SODIUM_BASE64_VARIANT_ORIGINAL);
		}

		return ConstantTimeBase64::encode($str);
	}

	/**
	 * Decode a string from Base64
	 */
	public static function decode(string $base64):string{
		self::checkCharacterSet($base64);

		if(function_exists('sodium_base642bin')){
			return sodium_base642bin($base64, \SODIUM_BASE64_VARIANT_ORIGINAL);
		}

		return ConstantTimeBase64::decode($base64);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function checkCharacterSet(string $base64):void{

		if(!preg_match('#^[a-z\d/=+]+$#i', $base64)){
			throw new InvalidArgumentException('Base64 must match RFC4648 character set');
		}

	}

}
