<?php

namespace Kirby\Toolkit;

use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use SensitiveParameter;

/**
 * User-friendly and safe abstraction for symmetric
 * authenticated encryption and decryption using the
 * PHP `sodium` extension
 * @since 3.9.8
 *
 * @package   Kirby Toolkit
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class SymmetricCrypto
{
	/**
	 * Cache for secret keys derived from the password
	 * indexed by the used salt and limits
	 */
	protected array $secretKeysByOptions = [];

	/**
	 * Initializes the keys used for crypto, both optional
	 *
	 * @param string|null $password Password to be derived into a `$secretKey`
	 * @param string|null $secretKey 256-bit key, alternatively a `$password` can be used
	 */
	public function __construct(
		#[SensitiveParameter]
		protected string|null $password = null,
		#[SensitiveParameter]
		protected string|null $secretKey = null,
	) {
		if ($password !== null && $secretKey !== null) {
			throw new InvalidArgumentException(
				message: 'Passing both a secret key and a password is not supported'
			);
		}

		if ($secretKey !== null && strlen($secretKey) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
			throw new InvalidArgumentException(
				message: 'Invalid secret key length, expected ' . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . ' bytes'
			);
		}
	}

	/**
	 * Hide values of secrets when printing the object
	 */
	public function __debugInfo(): array
	{
		return [
			'hasPassword'  => isset($this->password),
			'hasSecretKey' => isset($this->secretKey),
		];
	}

	/**
	 * Wipes the secrets from memory when they are no longer needed
	 */
	public function __destruct()
	{
		$this->memzero($this->password);
		$this->memzero($this->secretKey);

		foreach ($this->secretKeysByOptions as $key => &$value) {
			$this->memzero($value);
			unset($this->secretKeysByOptions[$key]);
		}
	}

	/**
	 * Decrypts JSON data encrypted by `SymmetricCrypto::encrypt()` using the secret key or password
	 *
	 * <code>
	 * // decryption with a password
	 * $crypto    = new SymmetricCrypto(password: 'super secure');
	 * $plaintext = $crypto->decrypt('a very confidential string');
	 *
	 * // decryption with a previously generated key
	 * $crypto    = new SymmetricCrypto(secretKey: $secretKey);
	 * $plaintext = $crypto->decrypt('{"mode":"secretbox"...}');
	 * </code>
	 */
	public function decrypt(string $json): string
	{
		$props = Json::decode($json);

		if (($props['mode'] ?? null) !== 'secretbox') {
			throw new InvalidArgumentException(
				message: 'Unsupported encryption mode "' . ($props['mode'] ?? '') . '"'
			);
		}

		if (
			isset($props['data']) !== true ||
			isset($props['nonce']) !== true ||
			isset($props['salt']) !== true ||
			isset($props['limits']) !== true
		) {
			throw new InvalidArgumentException(
				message: 'Input data does not contain all required props'
			);
		}

		$data   = base64_decode($props['data']);
		$nonce  = base64_decode($props['nonce']);
		$salt   = base64_decode($props['salt']);
		$limits = $props['limits'];

		$plaintext = sodium_crypto_secretbox_open($data, $nonce, $this->secretKey($salt, $limits));

		if (is_string($plaintext) !== true) {
			throw new LogicException(
				message: 'Encrypted string was tampered with'
			);
		}

		return $plaintext;
	}

	/**
	 * Encrypts a string using the secret key or password
	 *
	 * <code>
	 * // encryption with a password
	 * $crypto     = new SymmetricCrypto(password: 'super secure');
	 * $ciphertext = $crypto->encrypt('a very confidential string');
	 *
	 * // encryption with a random key
	 * $crypto     = new SymmetricCrypto();
	 * $ciphertext = $crypto->encrypt('a very confidential string');
	 * $secretKey  = $crypto->secretKey();
	 * </code>
	 */
	public function encrypt(
		#[SensitiveParameter]
		string $string
	): string {
		$nonce  = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$salt   = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
		$limits = [SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE];
		$key    = $this->secretKey($salt, $limits);

		$ciphertext = sodium_crypto_secretbox($string, $nonce, $key);

		// bundle all necessary information in a JSON object;
		// always include the salt and limits to hide whether a key or password was used
		return Json::encode([
			'mode'   => 'secretbox',
			'data'   => base64_encode($ciphertext),
			'nonce'  => base64_encode($nonce),
			'salt'   => base64_encode($salt),
			'limits' => $limits,
		]);
	}

	/**
	 * Checks if the required PHP `sodium` extension is available
	 */
	public static function isAvailable(): bool
	{
		return defined('SODIUM_LIBRARY_MAJOR_VERSION') === true && SODIUM_LIBRARY_MAJOR_VERSION >= 10;
	}

	/**
	 * Returns the binary secret key, optionally derived from the password
	 * or randomly generated
	 *
	 * @param string|null $salt Salt for password-based key derivation
	 * @param array|null $limits Processing limits for password-based key derivation
	 */
	public function secretKey(
		#[SensitiveParameter]
		string|null $salt = null,
		array|null $limits = null
	): string {
		if (isset($this->secretKey) === true) {
			return $this->secretKey;
		}

		// derive from password
		if (isset($this->password) === true) {
			if ($salt === null || $limits === null) {
				throw new InvalidArgumentException(
					message: 'Salt and limits are required when deriving a secret key from a password'
				);
			}

			// access from cache
			$options = $salt . ':' . implode(',', $limits);
			if (isset($this->secretKeysByOptions[$options]) === true) {
				return $this->secretKeysByOptions[$options];
			}

			return $this->secretKeysByOptions[$options] = sodium_crypto_pwhash(
				SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
				$this->password,
				$salt,
				$limits[0],
				$limits[1],
				SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
			);
		}

		// generate a random key
		return $this->secretKey = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
	}

	/**
	 * Wipes a variable from memory if it is a string
	 */
	protected function memzero(mixed &$value): void
	{
		if (is_string($value) === true) {
			sodium_memzero($value);
			$value = '';
		}
	}
}
