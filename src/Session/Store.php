<?php

namespace Kirby\Session;

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Store
{
	/**
	 * Deletes all expired sessions
	 *
	 * Needs to throw an Exception on error.
	 */
	abstract public function collectGarbage(): void;

	/**
	 * Creates a new session ID with the given expiry time
	 *
	 * Needs to make sure that the session does not already exist
	 * and needs to reserve it by locking it exclusively.
	 */
	abstract public function createId(int $expiryTime): string;

	/**
	 * Deletes the given session.
	 * Needs to throw an Exception on error.
	 */
	abstract public function destroy(int $expiryTime, string $id): void;

	/**
	 * Checks if the given session exists
	 */
	abstract public function exists(int $expiryTime, string $id): bool;

	/**
	 * Securely generates a random session ID (hex string with 20 bytes)
	 */
	protected static function generateId(): string
	{
		return bin2hex(random_bytes(10));
	}

	/**
	 * Returns the stored session data of the given session.
	 * Needs to throw an Exception on error.
	 */
	abstract public function get(int $expiryTime, string $id): string;

	/**
	 * Locks the given session exclusively
	 * Needs to throw an Exception on error.
	 */
	abstract public function lock(int $expiryTime, string $id): void;

	/**
	 * Stores data to the given session.
	 * Needs to make sure that the session exists.
	 * Needs to throw an Exception on error.
	 */
	abstract public function set(int $expiryTime, string $id, string $data): void;

	/**
	 * Removes all locks on the given session.
	 * Needs to throw an Exception on error.
	 */
	abstract public function unlock(int $expiryTime, string $id): void;
}
