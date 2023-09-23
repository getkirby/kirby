<?php

namespace Kirby\Toolkit;

use Base32\Base32;
use Kirby\Exception\InvalidArgumentException;
use SensitiveParameter;

/**
 * The TOTP class handles the generation and verification
 * of time-based one-time passwords according to RFC6238
 * with the SHA1 algorithm, 30 second intervals and 6 digits
 * @since 4.0.0
 *
 * @package   Kirby Toolkit
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Totp
{
	/**
	 * Binary secret
	 */
	protected string $secret;

	/**
	 * Class constructor
	 *
	 * @param string|null $secret Existing secret in Base32 format
	 *                            or `null` to generate a new one
	 * @param bool $force Whether to skip the secret length validation;
	 *                    WARNING: Only ever set this to `true` when
	 *                    generating codes for third-party services
	 */
	public function __construct(
		#[SensitiveParameter]
		string|null $secret = null,
		bool $force = false
	) {
		// if provided, decode the existing secret into binary
		if ($secret !== null) {
			$this->secret = Base32::decode($secret);
		}

		// otherwise generate a new one;
		// 20 bytes are the length of the SHA1 HMAC
		$this->secret ??= random_bytes(20);

		// safety check to avoid accidental insecure secrets
		if ($force === false && strlen($this->secret) !== 20) {
			throw new InvalidArgumentException('TOTP secrets should be 32 Base32 digits (= 20 bytes)');
		}
	}

	/**
	 * Generates the current TOTP code
	 *
	 * @param int $offset Optional counter offset to generate
	 *                    previous or upcoming codes
	 */
	public function generate(int $offset = 0): string
	{
		// generate a new code every 30 seconds
		$counter = floor(time() / 30) + $offset;

		// pack the number into a binary 64-bit unsigned int
		$binaryCounter = pack('J', $counter);

		// on 32-bit systems, we need to pack into a binary 32-bit
		// unsigned int and prepend 4 null bytes to get a 64-bit value
		// @codeCoverageIgnoreStart
		if (PHP_INT_SIZE < 8) {
			$binaryCounter = "\0\0\0\0" . pack('N', $counter);
		}
		// @codeCoverageIgnoreEnd

		// create a binary HMAC from the binary counter and the binary secret
		$binaryHmac = hash_hmac('sha1', $binaryCounter, $this->secret, true);

		// convert the HMAC into an array of byte values (from 0-255)
		$bytes = unpack('C*', $binaryHmac);

		// perform dynamic truncation to four bytes according to RFC6238 & RFC4226
		$byteOffset = (end($bytes) & 0xF);
		$code = (($bytes[$byteOffset + 1] & 0x7F) << 24) |
			($bytes[$byteOffset + 2] << 16) |
			($bytes[$byteOffset + 3] << 8) |
			$bytes[$byteOffset + 4];

		// truncate the resulting number to at max six digits
		$code %= 1000000;

		// format as a six-digit string, left-padded with zeros
		return sprintf('%06d', $code);
	}

	/**
	 * Returns the secret in human-readable Base32 format
	 */
	public function secret(): string
	{
		return Base32::encode($this->secret);
	}

	/**
	 * Returns a `otpauth://` URI for use in a setup QR code or link
	 *
	 * @param string $issuer Name of the site the code is valid for
	 * @param string $label Account name the code is valid for
	 */
	public function uri(string $issuer, string $label): string
	{
		$query = http_build_query([
			'secret' => $this->secret(),
			'issuer' => $issuer
		], '', '&', PHP_QUERY_RFC3986);

		return 'otpauth://totp/' . rawurlencode($issuer) .
			':' . rawurlencode($label) . '?' . $query;
	}

	/**
	 * Securely checks the provided TOTP code against the
	 * current, the direct previous and following codes
	 */
	public function verify(string $totp): bool
	{
		// strip out any non-numeric character (e.g. spaces)
		// from user input to increase UX
		$totp = preg_replace('/[^0-9]/', '', $totp);

		// also allow the previous and upcoming codes
		// to account for time sync issues
		foreach ([0, -1, 1] as $offset) {
			if (hash_equals($this->generate($offset), $totp) === true) {
				return true;
			}
		}

		return false;
	}
}
