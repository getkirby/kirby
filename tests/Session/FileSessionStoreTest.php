<?php

namespace Kirby\Session;

use Kirby\Exception\Exception;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * @coversDefaultClass \Kirby\Session\FileSessionStore
 */
class FileSessionStoreTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Session.FileSessionStore';

	protected SessionStore$store;
	protected ReflectionProperty $storeHandles;
	protected ReflectionProperty $storeIsLocked;

	public function setUp(): void
	{
		$this->store = new FileSessionStore(static::TMP);
		$this->assertDirectoryExists(static::TMP);

		// make internal data accessible
		$reflector = new ReflectionClass(FileSessionStore::class);
		$this->storeHandles = $reflector->getProperty('handles');
		$this->storeHandles->setAccessible(true);
		$this->storeIsLocked = $reflector->getProperty('isLocked');
		$this->storeIsLocked->setAccessible(true);

		// demo files
		F::write(static::TMP . '/.gitignore', "*\n!.gitignore");
		F::write(static::TMP . '/1234567890.abcdefghijabcdefghij.sess', '1234567890');
		F::write(static::TMP . '/1357913579.abcdefghijabcdefghij.sess', '1357913579');
		F::write(static::TMP . '/7777777777.abcdefghijabcdefghij.sess', '7777777777');
		F::write(static::TMP . '/8888888888.abcdefghijabcdefghij.sess', '');
		F::write(static::TMP . '/9999999999.abcdefghijabcdefghij.sess', '9999999999');
	}

	public function tearDown(): void
	{
		// let the store __destruct() itself
		unset($this->store);

		// make sure the directory and in files are writable before trying to delete
		chmod(static::TMP, 0o777);

		$files = array_diff(scandir(static::TMP) ?? [], ['.', '..']);
		foreach ($files as $file) {
			chmod(static::TMP . '/' . $file, 0o777);
		}

		Dir::remove(static::TMP);
		$this->assertDirectoryDoesNotExist(static::TMP);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructorNotWritable()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.session.filestore.dirNotWritable');

		Dir::make(static::TMP, true);
		chmod(static::TMP, 0o555);

		new FileSessionStore(static::TMP);
	}

	/**
	 * @covers ::createId
	 * @covers ::name
	 * @covers ::path
	 */
	public function testCreateId()
	{
		$id = $this->store->createId(1234567890);

		$this->assertStringMatchesFormat('%x', $id);
		$this->assertSame(20, strlen($id));
		$this->assertFileExists(static::TMP . '/1234567890.' . $id . '.sess');
		$this->assertHandleExists('1234567890.' . $id);
		$this->assertLocked('1234567890.' . $id);
	}

	/**
	 * @covers ::exists
	 * @covers ::name
	 * @covers ::path
	 */
	public function testExists()
	{
		$this->assertTrue($this->store->exists(1234567890, 'abcdefghijabcdefghij'));
		$this->assertTrue($this->store->exists(1357913579, 'abcdefghijabcdefghij'));
		$this->assertTrue($this->store->exists(7777777777, 'abcdefghijabcdefghij'));
		$this->assertTrue($this->store->exists(8888888888, 'abcdefghijabcdefghij'));
		$this->assertTrue($this->store->exists(9999999999, 'abcdefghijabcdefghij'));
		$this->assertFalse($this->store->exists(1234567890, 'someotherid'));

		$this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');
		$this->assertHandleNotExists('1357913579.abcdefghijabcdefghij');
		$this->assertHandleNotExists('7777777777.abcdefghijabcdefghij');
		$this->assertHandleNotExists('8888888888.abcdefghijabcdefghij');
		$this->assertHandleNotExists('9999999999.abcdefghijabcdefghij');
	}

	/**
	 * @covers ::lock
	 * @covers ::name
	 * @covers ::path
	 */
	public function testLock()
	{
		$this->store->lock(1234567890, 'abcdefghijabcdefghij');
		$this->assertLocked('1234567890.abcdefghijabcdefghij');
		$this->assertHandleExists('1234567890.abcdefghijabcdefghij');

		// lock an already locked file
		$this->store->lock(1234567890, 'abcdefghijabcdefghij');
		$this->assertLocked('1234567890.abcdefghijabcdefghij');
		$this->assertHandleExists('1234567890.abcdefghijabcdefghij');
	}

	/**
	 * @covers ::lock
	 * @covers ::name
	 * @covers ::path
	 */
	public function testLockNonExistingFile()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.filestore.notFound');

		$this->store->lock(1234567890, 'someotherid');
	}

	/**
	 * @covers ::unlock
	 * @covers ::name
	 * @covers ::path
	 */
	public function testUnlock()
	{
		// unlock an unlocked file
		$this->assertNotLocked('1234567890.abcdefghijabcdefghij');
		$this->store->unlock(1234567890, 'abcdefghijabcdefghij');
		$this->assertNotLocked('1234567890.abcdefghijabcdefghij');
		$this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');

		// lock and then unlock again
		$this->store->lock(1357913579, 'abcdefghijabcdefghij');
		$this->assertLocked('1357913579.abcdefghijabcdefghij');
		$this->store->unlock(1357913579, 'abcdefghijabcdefghij');
		$this->assertNotLocked('1357913579.abcdefghijabcdefghij');

		// non-existing file: *not* supposed to throw an Exception
		$this->store->unlock(1234567890, 'someotherid');

		// locked file that doesn't exist anymore
		$this->store->lock(1357913579, 'abcdefghijabcdefghij');
		$this->assertLocked('1357913579.abcdefghijabcdefghij');
		unlink(static::TMP . '/1357913579.abcdefghijabcdefghij.sess');
		$this->store->unlock(1357913579, 'abcdefghijabcdefghij');
		$this->assertNotLocked('1357913579.abcdefghijabcdefghij');
	}

	/**
	 * @covers ::get
	 * @covers ::name
	 * @covers ::path
	 */
	public function testGet()
	{
		$this->assertSame('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
		$this->assertHandleExists('1234567890.abcdefghijabcdefghij');

		$this->assertSame('', $this->store->get(8888888888, 'abcdefghijabcdefghij'));
	}

	/**
	 * @covers ::get
	 * @covers ::name
	 * @covers ::path
	 */
	public function testGetNonExistingFile()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.filestore.notFound');

		$this->store->get(1234567890, 'someotherid');
	}

	/**
	 * @covers ::get
	 * @covers ::name
	 * @covers ::path
	 * @covers ::handle
	 */
	public function testGetUnreadableFile()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.session.filestore.notOpened');

		// session files need to have read and write permissions even for reading
		chmod(static::TMP . '/1234567890.abcdefghijabcdefghij.sess', 0o444);
		$this->store->get(1234567890, 'abcdefghijabcdefghij');
	}

	/**
	 * @covers ::set
	 * @covers ::name
	 * @covers ::path
	 */
	public function testSet()
	{
		$this->assertSame('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));

		$this->store->lock(1234567890, 'abcdefghijabcdefghij');
		$this->store->set(1234567890, 'abcdefghijabcdefghij', 'some other data');

		$this->assertLocked('1234567890.abcdefghijabcdefghij');
		$this->assertHandleExists('1234567890.abcdefghijabcdefghij');
		$this->assertSame('some other data', F::read(static::TMP . '/1234567890.abcdefghijabcdefghij.sess'));
		$this->assertSame('some other data', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
	}

	/**
	 * @covers ::set
	 * @covers ::name
	 * @covers ::path
	 */
	public function testSetNonExistingFile()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.filestore.notFound');

		$this->store->set(1234567890, 'someotherid', 'some other data');
	}

	/**
	 * @covers ::set
	 * @covers ::name
	 * @covers ::path
	 */
	public function testSetWithoutLock()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.filestore.notLocked');

		$this->assertSame('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));

		$this->store->set(1234567890, 'abcdefghijabcdefghij', 'some other data');
	}

	/**
	 * @covers ::destroy
	 * @covers ::name
	 * @covers ::path
	 * @covers ::closeHandle
	 */
	public function testDestroy()
	{
		$this->assertFileExists(static::TMP . '/1234567890.abcdefghijabcdefghij.sess');
		$this->assertNotLocked('1234567890.abcdefghijabcdefghij');
		$this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');

		$this->store->destroy(1234567890, 'abcdefghijabcdefghij');

		$this->assertFileDoesNotExist(static::TMP . '/1234567890.abcdefghijabcdefghij.sess');
		$this->assertNotLocked('1234567890.abcdefghijabcdefghij');
		$this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');
	}

	/**
	 * @covers ::destroy
	 * @covers ::name
	 * @covers ::path
	 * @covers ::closeHandle
	 */
	public function testDestroyAlreadyDestroyed()
	{
		// make sure we get a handle
		$this->assertSame('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
		$this->assertHandleExists('1234567890.abcdefghijabcdefghij');

		// simulate that another thread deleted the file
		unlink(static::TMP . '/1234567890.abcdefghijabcdefghij.sess');

		// shouldn't throw an Exception
		$this->store->destroy(1234567890, 'abcdefghijabcdefghij');
	}

	/**
	 * @covers ::handle
	 * @covers ::name
	 * @covers ::path
	 */
	public function testAccessAfterDestroy()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.filestore.notFound');

		// make sure we get a handle
		$this->assertSame('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
		$this->store->lock(1234567890, 'abcdefghijabcdefghij');
		$this->assertHandleExists('1234567890.abcdefghijabcdefghij');

		// simulate that another thread deleted the file
		unlink(static::TMP . '/1234567890.abcdefghijabcdefghij.sess');

		// now it should throw even if there is already a handle
		$this->store->set(1234567890, 'abcdefghijabcdefghij', 'something else');
	}

	/**
	 * @covers ::collectGarbage
	 */
	public function testCollectGarbage()
	{
		$this->assertFileExists(static::TMP . '/.gitignore');
		$this->assertFileExists(static::TMP . '/1234567890.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/1357913579.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/7777777777.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/8888888888.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/9999999999.abcdefghijabcdefghij.sess');

		$this->store->collectGarbage();

		$this->assertFileExists(static::TMP . '/.gitignore');
		$this->assertFileDoesNotExist(static::TMP . '/1234567890.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/1357913579.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/7777777777.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/8888888888.abcdefghijabcdefghij.sess');
		$this->assertFileExists(static::TMP . '/9999999999.abcdefghijabcdefghij.sess');
	}

	/**
	 * Asserts that the given session is currently locked
	 *
	 * @param string $name Combined name
	 */
	protected function assertLocked(string $name): void
	{
		$isLocked = $this->storeIsLocked->getValue($this->store);
		$this->assertTrue(isset($isLocked[$name]));

		// try locking the file again, which should fail
		$path = static::TMP . '/' . $name . '.sess';
		if (is_file($path)) {
			$handle = fopen($path, 'r+');
			$this->assertFalse(flock($handle, LOCK_EX | LOCK_NB));
			flock($handle, LOCK_UN);
			fclose($handle);
		}
	}

	/**
	 * Asserts that the given session is currently not locked
	 *
	 * @param string $name Combined name
	 */
	protected function assertNotLocked(string $name): void
	{
		$isLocked = $this->storeIsLocked->getValue($this->store);
		$this->assertFalse(isset($isLocked[$name]));

		// try locking the file, which should work if the file is not currently locked
		$path = static::TMP . '/' . $name . '.sess';
		if (is_file($path)) {
			$handle = fopen($path, 'r+');
			$this->assertTrue(flock($handle, LOCK_EX | LOCK_NB));
			flock($handle, LOCK_UN);
			fclose($handle);
		}
	}

	/**
	 * Asserts that the given session currently has an open handle
	 *
	 * @param string $name Combined name
	 */
	protected function assertHandleExists(string $name): void
	{
		$handles = $this->storeHandles->getValue($this->store);
		$this->assertTrue(isset($handles[$name]));
	}

	/**
	 * Asserts that the given session currently has no open handle
	 *
	 * @param string $name Combined name
	 */
	protected function assertHandleNotExists(string $name): void
	{
		$handles = $this->storeHandles->getValue($this->store);
		$this->assertFalse(isset($handles[$name]));
	}
}
