<?php

namespace Kirby\Session;

/**
 * @package   Kirby Session
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class SessionStore
{
	/**
	 * Creates a new session ID with the given expiry time
	 *
	 * Needs to make sure that the session does not already exist
	 * and needs to reserve it by locking it exclusively.
	 *
	 * @param int $expiryTime Timestamp
	 * @return string Randomly generated session ID (without timestamp)
	 */
	abstract public function createId(int $expiryTime): string;

	/**
	 * Checks if the given session exists
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @return bool true:  session exists,
	 *              false: session doesn't exist
	 */
	abstract public function exists(int $expiryTime, string $id): bool;

	/**
	 * Locks the given session exclusively
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @return void
	 */
	abstract public function lock(int $expiryTime, string $id);

	/**
	 * Removes all locks on the given session
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @return void
	 */
	abstract public function unlock(int $expiryTime, string $id);

	/**
	 * Returns the stored session data of the given session
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @return string
	 */
	abstract public function get(int $expiryTime, string $id): string;

	/**
	 * Stores data to the given session
	 *
	 * Needs to make sure that the session exists.
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @param string $data Session data to write
	 * @return void
	 */
	abstract public function set(int $expiryTime, string $id, string $data);

	/**
	 * Deletes the given session
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @return void
	 */
	abstract public function destroy(int $expiryTime, string $id);

	/**
	 * Deletes all expired sessions
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @return void
	 */
	abstract public function collectGarbage();

	/**
	 * Securely generates a random session ID
	 *
	 * @return string Random hex string with 20 bytes
	 */
	protected static function generateId(): string
	{
		return bin2hex(random_bytes(10));
	}
}
