<?php

namespace Kirby\Session;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Session\FileSessionStore
 */
class FileSessionStoreTest extends TestCase
{
    protected $root = __DIR__ . '/fixtures/store';
    protected $store;
    protected $storeHandles;
    protected $storeIsLocked;

    public function setUp(): void
    {
        $this->store = new FileSessionStore($this->root);
        $this->assertDirectoryExists($this->root);

        // make internal data accessible
        $reflector = new ReflectionClass(FileSessionStore::class);
        $this->storeHandles = $reflector->getProperty('handles');
        $this->storeHandles->setAccessible(true);
        $this->storeIsLocked = $reflector->getProperty('isLocked');
        $this->storeIsLocked->setAccessible(true);

        // demo files
        F::write($this->root . '/.gitignore', "*\n!.gitignore");
        F::write($this->root . '/1234567890.abcdefghijabcdefghij.sess', '1234567890');
        F::write($this->root . '/1357913579.abcdefghijabcdefghij.sess', '1357913579');
        F::write($this->root . '/7777777777.abcdefghijabcdefghij.sess', '7777777777');
        F::write($this->root . '/8888888888.abcdefghijabcdefghij.sess', '');
        F::write($this->root . '/9999999999.abcdefghijabcdefghij.sess', '9999999999');
    }

    public function tearDown(): void
    {
        // let the store __destruct() itself
        unset($this->store);

        // make sure the directory and in files are writable before trying to delete
        chmod($this->root, 0777);

        $files = array_diff(scandir($this->root) ?? [], ['.', '..']);
        foreach ($files as $file) {
            chmod($this->root . '/' . $file, 0777);
        }

        Dir::remove($this->root);
        $this->assertDirectoryNotExists($this->root);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorNotWritable()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionCode('error.session.filestore.dirNotWritable');

        Dir::make($this->root, true);
        chmod($this->root, 0555);

        new FileSessionStore($this->root);
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
        $this->assertFileExists($this->root . '/1234567890.' . $id . '.sess');
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
        $this->expectException('Kirby\Exception\NotFoundException');
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
        unlink($this->root . '/1357913579.abcdefghijabcdefghij.sess');
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
        $this->expectException('Kirby\Exception\NotFoundException');
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
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionCode('error.session.filestore.notOpened');

        // session files need to have read and write permissions even for reading
        chmod($this->root . '/1234567890.abcdefghijabcdefghij.sess', 0444);
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
        $this->assertSame('some other data', F::read($this->root . '/1234567890.abcdefghijabcdefghij.sess'));
        $this->assertSame('some other data', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
    }

    /**
     * @covers ::set
     * @covers ::name
     * @covers ::path
     */
    public function testSetNonExistingFile()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
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
        $this->expectException('Kirby\Exception\LogicException');
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
        $this->assertFileExists($this->root . '/1234567890.abcdefghijabcdefghij.sess');
        $this->assertNotLocked('1234567890.abcdefghijabcdefghij');
        $this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');

        $this->store->destroy(1234567890, 'abcdefghijabcdefghij');

        $this->assertFileDoesNotExist($this->root . '/1234567890.abcdefghijabcdefghij.sess');
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
        unlink($this->root . '/1234567890.abcdefghijabcdefghij.sess');

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
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionCode('error.session.filestore.notFound');

        // make sure we get a handle
        $this->assertSame('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
        $this->store->lock(1234567890, 'abcdefghijabcdefghij');
        $this->assertHandleExists('1234567890.abcdefghijabcdefghij');

        // simulate that another thread deleted the file
        unlink($this->root . '/1234567890.abcdefghijabcdefghij.sess');

        // now it should throw even if there is already a handle
        $this->store->set(1234567890, 'abcdefghijabcdefghij', 'something else');
    }

    /**
     * @covers ::collectGarbage
     */
    public function testCollectGarbage()
    {
        $this->assertFileExists($this->root . '/.gitignore');
        $this->assertFileExists($this->root . '/1234567890.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/1357913579.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/7777777777.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/8888888888.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/9999999999.abcdefghijabcdefghij.sess');

        $this->store->collectGarbage();

        $this->assertFileExists($this->root . '/.gitignore');
        $this->assertFileDoesNotExist($this->root . '/1234567890.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/1357913579.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/7777777777.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/8888888888.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/9999999999.abcdefghijabcdefghij.sess');
    }

    /**
     * Asserts that the given session is currently locked
     *
     * @param string $name Combined name
     * @return void
     */
    protected function assertLocked(string $name)
    {
        $isLocked = $this->storeIsLocked->getValue($this->store);
        $this->assertTrue(isset($isLocked[$name]));

        // try locking the file again, which should fail
        $path = $this->root . '/' . $name . '.sess';
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
     * @return void
     */
    protected function assertNotLocked(string $name)
    {
        $isLocked = $this->storeIsLocked->getValue($this->store);
        $this->assertFalse(isset($isLocked[$name]));

        // try locking the file, which should work if the file is not currently locked
        $path = $this->root . '/' . $name . '.sess';
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
     * @return void
     */
    protected function assertHandleExists(string $name)
    {
        $handles = $this->storeHandles->getValue($this->store);
        $this->assertTrue(isset($handles[$name]));
    }

    /**
     * Asserts that the given session currently has no open handle
     *
     * @param string $name Combined name
     * @return void
     */
    protected function assertHandleNotExists(string $name)
    {
        $handles = $this->storeHandles->getValue($this->store);
        $this->assertFalse(isset($handles[$name]));
    }
}
