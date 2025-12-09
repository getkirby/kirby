<?php

namespace Kirby\Session;

use FilesystemIterator;
use Kirby\Exception\Exception;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Session
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class FileSessionStore extends SessionStore
{
	protected string $path;

	// state of the session files
	protected array $handles  = [];
	protected array $isLocked = [];

	/**
	 * Creates a new instance of the file session store
	 *
	 * @param string $path Path to the storage directory
	 */
	public function __construct(string $path)
	{
		// create the directory if it doesn't already exist
		Dir::make($path, true);

		// store the canonicalized path
		$this->path = realpath($path);

		// make sure it is usable for storage
		if (is_writable($this->path) === false) {
			throw new Exception(
				key: 'session.filestore.dirNotWritable',
				data: ['path' => $this->path],
				fallback: 'The session storage directory "' . $path . '" is not writable',
				translate: false,
				httpCode: 500
			);
		}
	}

	/**
	 * Creates a new session ID with the given expiry time
	 *
	 * Needs to make sure that the session does not already exist
	 * and needs to reserve it by locking it exclusively.
	 *
	 * @param int $expiryTime Timestamp
	 * @return string Randomly generated session ID (without timestamp)
	 */
	public function createId(int $expiryTime): string
	{
		clearstatcache();
		do {
			// use helper from the abstract SessionStore class
			$id   = static::generateId();
			$name = $this->name($expiryTime, $id);
			$path = $this->path($name);
		} while (file_exists($path));

		// reserve the file
		touch($path);
		$this->lock($expiryTime, $id);

		// ensure that no other thread already wrote to the same file,
		// otherwise try again (very unlikely scenario!)
		$contents = $this->get($expiryTime, $id);

		if ($contents !== '') {
			// @codeCoverageIgnoreStart
			$this->unlock($expiryTime, $id);
			return $this->createId($expiryTime);
			// @codeCoverageIgnoreEnd
		}

		return $id;
	}

	/**
	 * Checks if the given session exists
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @return bool true:  session exists,
	 *              false: session doesn't exist
	 */
	public function exists(int $expiryTime, string $id): bool
	{
		$name = $this->name($expiryTime, $id);
		$path = $this->path($name);

		clearstatcache();
		return is_file($path) === true;
	}

	/**
	 * Locks the given session exclusively
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 */
	public function lock(int $expiryTime, string $id): void
	{
		$name = $this->name($expiryTime, $id);

		// check if the file is already locked
		if (isset($this->isLocked[$name]) === true) {
			return;
		}

		// lock it exclusively
		$handle = $this->handle($name);
		$result = flock($handle, LOCK_EX);

		// @codeCoverageIgnoreStart
		if ($result !== true) {
			throw new Exception(
				key: 'session.filestore.unexpectedFilesystemError',
				fallback: 'Unexpected file system error',
				translate: false,
				httpCode: 500
			);
		}
		// @codeCoverageIgnoreEnd

		// make a note that the file is now locked
		$this->isLocked[$name] = true;
	}

	/**
	 * Removes all locks on the given session
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 */
	public function unlock(int $expiryTime, string $id): void
	{
		$name = $this->name($expiryTime, $id);

		// check if the file is already unlocked or doesn't exist
		if (isset($this->isLocked[$name]) === false) {
			return;
		}

		if ($this->exists($expiryTime, $id) === false) {
			unset($this->isLocked[$name]);
			return;
		}

		// remove the exclusive lock
		$handle = $this->handle($name);
		$result = flock($handle, LOCK_UN);

		// @codeCoverageIgnoreStart
		if ($result !== true) {
			throw new Exception(
				key: 'session.filestore.unexpectedFilesystemError',
				fallback: 'Unexpected file system error',
				translate: false,
				httpCode: 500
			);
		}
		// @codeCoverageIgnoreEnd

		// make a note that the file is no longer locked
		unset($this->isLocked[$name]);
	}

	/**
	 * Returns the stored session data of the given session
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 */
	public function get(int $expiryTime, string $id): string
	{
		$name   = $this->name($expiryTime, $id);
		$path   = $this->path($name);
		$handle = $this->handle($name);

		// set read lock to prevent other threads from corrupting
		// the data while we read it; only if we don't already have
		// a write lock, which is even better
		if (isset($this->isLocked[$name]) === false) {
			$result = flock($handle, LOCK_SH);

			if ($result !== true) {
				// @codeCoverageIgnoreStart
				throw new Exception(
					key: 'session.filestore.unexpectedFilesystemError',
					fallback: 'Unexpected file system error',
					translate: false,
					httpCode: 500
				);
				// @codeCoverageIgnoreEnd
			}
		}

		clearstatcache();
		$filesize = filesize($path);
		if ($filesize > 0) {
			// always read the whole file
			rewind($handle);
			$string = fread($handle, $filesize);
		} else {
			// we don't need to read empty files
			$string = '';
		}

		// remove the shared lock if we set one above
		if (isset($this->isLocked[$name]) === false) {
			$result = flock($handle, LOCK_UN);

			if ($result !== true) {
				// @codeCoverageIgnoreStart
				throw new Exception(
					key: 'session.filestore.unexpectedFilesystemError',
					fallback: 'Unexpected file system error',
					translate: false,
					httpCode: 500
				);
				// @codeCoverageIgnoreEnd
			}
		}

		return $string;
	}

	/**
	 * Stores data to the given session
	 *
	 * Needs to make sure that the session exists.
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 * @param string $data Session data to write
	 */
	public function set(int $expiryTime, string $id, string $data): void
	{
		$name   = $this->name($expiryTime, $id);
		$handle = $this->handle($name);

		// validate that we have an exclusive lock already
		if (isset($this->isLocked[$name]) === false) {
			throw new LogicException(
				key: 'session.filestore.notLocked',
				data: ['name' => $name],
				fallback: 'Cannot write to session "' . $name . '", because it is not locked',
				translate: false,
				httpCode: 500
			);
		}

		// delete all file contents first
		if (rewind($handle) !== true || ftruncate($handle, 0) !== true) {
			// @codeCoverageIgnoreStart
			throw new Exception(
				key: 'session.filestore.unexpectedFilesystemError',
				fallback: 'Unexpected file system error',
				translate: false,
				httpCode: 500
			);
			// @codeCoverageIgnoreEnd
		}

		// write the new contents
		$result = fwrite($handle, $data);

		if (is_int($result) === false || $result === 0) {
			// @codeCoverageIgnoreStart
			throw new Exception(
				key: 'session.filestore.unexpectedFilesystemError',
				fallback: 'Unexpected file system error',
				translate: false,
				httpCode: 500
			);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Deletes the given session
	 *
	 * Needs to throw an Exception on error.
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 */
	public function destroy(int $expiryTime, string $id): void
	{
		$name = $this->name($expiryTime, $id);
		$path = $this->path($name);

		// close the file, otherwise we can't delete it on Windows;
		// deletion is *not* thread-safe because of this, but
		// resurrection of the file is prevented in $this->set() because of
		// the check in $this->handle() every time any method is called
		$this->unlock($expiryTime, $id);
		$this->closeHandle($name);

		// we don't need to delete files that don't exist anymore
		if ($this->exists($expiryTime, $id) === false) {
			return;
		}

		// file still exists, delete it
		if (@F::unlink($path) !== true) {
			// @codeCoverageIgnoreStart
			throw new Exception(
				key: 'session.filestore.unexpectedFilesystemError',
				fallback: 'Unexpected file system error',
				translate: false,
				httpCode: 500
			);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Deletes all expired sessions
	 *
	 * Needs to throw an Exception on error.
	 */
	public function collectGarbage(): void
	{
		$iterator = new FilesystemIterator($this->path);

		$currentTime = time();
		foreach ($iterator as $file) {
			// make sure that the file is a session file
			// prevents deleting files like .gitignore or other unrelated files
			if (preg_match('/^[0-9]+\.[a-z0-9]+\.sess$/', $file->getFilename()) !== 1) {
				continue;
			}

			// extract the data from the filename
			$name       = $file->getBasename('.sess');
			$expiryTime = (int)Str::before($name, '.');
			$id         = Str::after($name, '.');

			if ($expiryTime < $currentTime) {
				// the session has expired, delete it
				$this->destroy($expiryTime, $id);
			}
		}
	}

	/**
	 * Cleans up the open locks and file handles
	 *
	 * @codeCoverageIgnore
	 */
	public function __destruct()
	{
		// unlock all locked files
		foreach (array_keys($this->isLocked) as $name) {
			$expiryTime = (int)Str::before($name, '.');
			$id         = Str::after($name, '.');

			$this->unlock($expiryTime, $id);
		}

		// close all file handles
		foreach (array_keys($this->handles) as $name) {
			$this->closeHandle($name);
		}
	}

	/**
	 * Returns the combined name based on expiry time and ID
	 *
	 * @param int $expiryTime Timestamp
	 * @param string $id Session ID
	 */
	protected function name(int $expiryTime, string $id): string
	{
		// protect against path traversal
		return $expiryTime . '.' . basename($id);
	}

	/**
	 * Returns the full path to the session file
	 *
	 * @param string $name Combined name
	 */
	protected function path(string $name): string
	{
		return $this->path . '/' . $name . '.sess';
	}

	/**
	 * Returns a PHP file handle for a session
	 *
	 * @param string $name Combined name
	 * @return resource File handle
	 */
	protected function handle(string $name)
	{
		// always verify that the file still exists, even if we
		// already have a handle; ensures thread-safeness for
		// recently deleted sessions, see $this->destroy()
		$path = $this->path($name);
		clearstatcache();

		if (is_file($path) === false) {
			throw new NotFoundException(
				key: 'session.filestore.notFound',
				data: ['name' => $name],
				fallback: 'Session file "' . $name . '" does not exist',
				translate: false,
				httpCode: 404
			);
		}

		// return from cache
		if (isset($this->handles[$name]) === true) {
			return $this->handles[$name];
		}

		// open a new handle
		$handle = @fopen($path, 'r+b');

		if (is_resource($handle) === false) {
			throw new Exception(
				key: 'session.filestore.notOpened',
				data: ['name' => $name],
				fallback: 'Session file "' . $name . '" could not be opened',
				translate: false,
				httpCode: 500
			);
		}

		return $this->handles[$name] = $handle;
	}

	/**
	 * Closes an open file handle
	 *
	 * @param string $name Combined name
	 */
	protected function closeHandle(string $name): void
	{
		if (isset($this->handles[$name]) === false) {
			return;
		}

		$handle = $this->handles[$name];
		unset($this->handles[$name]);
		$result = fclose($handle);

		if ($result !== true) {
			// @codeCoverageIgnoreStart
			throw new Exception(
				key: 'session.filestore.unexpectedFilesystemError',
				fallback: 'Unexpected file system error',
				translate: false,
				httpCode: 500
			);
			// @codeCoverageIgnoreEnd
		}
	}
}
