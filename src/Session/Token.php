<?php

namespace Kirby\Session;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\SymmetricCrypto;
use Stringable;

/**
 * Represents a session token consisting of an expiry
 * timestamp, the session ID and an optional secret key
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Token implements Stringable
{
	public function __construct(
		public readonly int $expiry,
		public readonly string $id,
		public readonly string|null $key = null
	) {
	}

	/**
	 * Returns the full token string
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * Returns a symmetric crypto instance based on the token key
	 */
	public function crypto(): SymmetricCrypto|null
	{
		if (
			$this->key === null ||
			SymmetricCrypto::isAvailable() === false
		) {
			return null; // @codeCoverageIgnore
		}

		return new SymmetricCrypto(secretKey: hex2bin($this->key));
	}

	/**
	 * Generates a brand new token by reserving a session ID
	 * in the store and creating a random secret key
	 *
	 * @param $expiry Timestamp the token expires at
	 */
	public static function generate(
		Store $store,
		int $expiry
	): static {
		return new static(
			expiry: $expiry,
			id:     $store->createId($expiry),
			key:    bin2hex(random_bytes(32))
		);
	}

	/**
	 * Whether the token can only be used to read the session
	 * (i.e. it doesn't carry the secret key); only the case for
	 * moved sessions accessed through an old session token
	 */
	public function isReadonly(): bool
	{
		return $this->key === null;
	}

	/**
	 * Parses a token string into a Token object
	 *
	 * @param $token Session token
	 * @param $key Whether the token string includes the secret key
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function parse(
		string $token,
		bool $key = true
	): static {
		// split the token into its parts
		$parts = explode('.', $token);

		// only continue if the token has exactly
		// the right amount of parts
		$expectedParts = $key !== true ? 2 : 3;

		if (count($parts) !== $expectedParts) {
			throw new InvalidArgumentException(
				data: ['method' => 'Token::parse', 'argument' => '$token'],
				translate: false
			);
		}

		$instance = new static(
			expiry: (int)$parts[0],
			id: $parts[1],
			key: $key !== true ? null : $parts[2]
		);

		// verify that all parts were parsed correctly using reassembly
		if ($instance->toString() !== $token) {
			throw new InvalidArgumentException(
				data: ['method' => 'Token::parse', 'argument' => '$token'],
				translate: false
			);
		}

		return $instance;
	}

	/**
	 * Returns the token as a string in the format
	 * `<expiry>.<id>` or `<expiry>.<id>.<key>`
	 *
	 * @param $key Whether to include the secret key (if there is one)
	 */
	public function toString(bool $key = true): string
	{
		$token = $this->expiry . '.' . $this->id;

		if ($key === true && $this->key !== null) {
			$token .= '.' . $this->key;
		}

		return $token;
	}
}
