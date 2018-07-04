<?php

namespace Kirby\Session;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class FileSessionStoreTest extends TestCase
{
    protected $root = __DIR__ . '/fixtures/store';
    protected $store;
    protected $storeHandles;
    protected $storeIsLocked;

    public function setUp()
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
        F::write($this->root . '/9999999999.abcdefghijabcdefghij.sess', '9999999999');
    }

    public function tearDown()
    {
        // let the store __destruct() itself
        unset($this->store);

        // make sure the directory is writable before trying to delete
        chmod($this->root, 0777);
        Dir::remove($this->root);
        $this->assertDirectoryNotExists($this->root);
    }

    /**
     * @expectedException     Kirby\Exception\Exception
     * @expectedExceptionCode error.session.filestore.dirNotWritable
     */
    public function testConstructorNotWritable()
    {
        Dir::make($this->root, true);
        chmod($this->root, 0555);

        new FileSessionStore($this->root);
    }

    public function testCreateId()
    {
        $id = $this->store->createId(1234567890);

        $this->assertStringMatchesFormat('%x', $id);
        $this->assertEquals(20, strlen($id));
        $this->assertFileExists($this->root . '/1234567890.' . $id . '.sess');
        $this->assertHandleExists('1234567890.' . $id);
        $this->assertLocked('1234567890.' . $id);
    }

    public function testExists()
    {
        $this->assertTrue($this->store->exists(1234567890, 'abcdefghijabcdefghij'));
        $this->assertTrue($this->store->exists(1357913579, 'abcdefghijabcdefghij'));
        $this->assertTrue($this->store->exists(7777777777, 'abcdefghijabcdefghij'));
        $this->assertTrue($this->store->exists(9999999999, 'abcdefghijabcdefghij'));
        $this->assertFalse($this->store->exists(1234567890, 'someotherid'));

        $this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');
        $this->assertHandleNotExists('1357913579.abcdefghijabcdefghij');
        $this->assertHandleNotExists('7777777777.abcdefghijabcdefghij');
        $this->assertHandleNotExists('9999999999.abcdefghijabcdefghij');
    }

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
     * @expectedException     Kirby\Exception\NotFoundException
     * @expectedExceptionCode error.session.filestore.notFound
     */
    public function testLockNonExistingFile()
    {
        $this->store->lock(1234567890, 'someotherid');
    }

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
    }

    public function testGet()
    {
        $this->assertEquals('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
        $this->assertHandleExists('1234567890.abcdefghijabcdefghij');
    }

    /**
     * @expectedException     Kirby\Exception\NotFoundException
     * @expectedExceptionCode error.session.filestore.notFound
     */
    public function testGetNonExistingFile()
    {
        $this->store->get(1234567890, 'someotherid');
    }

    /**
     * @expectedException     Kirby\Exception\Exception
     * @expectedExceptionCode error.session.filestore.notOpened
     */
    public function testGetUnreadableFile()
    {
        // session files need to have read and write permissions even for reading
        chmod($this->root . '/1234567890.abcdefghijabcdefghij.sess', 0444);
        $this->store->get(1234567890, 'abcdefghijabcdefghij');
    }

    public function testSet()
    {
        $this->assertEquals('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));

        $this->store->lock(1234567890, 'abcdefghijabcdefghij');
        $this->store->set(1234567890, 'abcdefghijabcdefghij', 'some other data');

        $this->assertLocked('1234567890.abcdefghijabcdefghij');
        $this->assertHandleExists('1234567890.abcdefghijabcdefghij');
        $this->assertEquals('some other data', F::read($this->root . '/1234567890.abcdefghijabcdefghij.sess'));
        $this->assertEquals('some other data', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
    }

    /**
     * @expectedException     Kirby\Exception\NotFoundException
     * @expectedExceptionCode error.session.filestore.notFound
     */
    public function testSetNonExistingFile()
    {
        $this->store->set(1234567890, 'someotherid', 'some other data');
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.filestore.notLocked
     */
    public function testSetWithoutLock()
    {
        $this->assertEquals('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));

        $this->store->set(1234567890, 'abcdefghijabcdefghij', 'some other data');
    }

    public function testDestroy()
    {
        $this->assertFileExists($this->root . '/1234567890.abcdefghijabcdefghij.sess');
        $this->assertNotLocked('1234567890.abcdefghijabcdefghij');
        $this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');

        $this->store->destroy(1234567890, 'abcdefghijabcdefghij');

        $this->assertFileNotExists($this->root . '/1234567890.abcdefghijabcdefghij.sess');
        $this->assertNotLocked('1234567890.abcdefghijabcdefghij');
        $this->assertHandleNotExists('1234567890.abcdefghijabcdefghij');
    }

    public function testDestroyAlreadyDestroyed()
    {
        // make sure we get a handle
        $this->assertEquals('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
        $this->assertHandleExists('1234567890.abcdefghijabcdefghij');

        // simulate that another thread deleted the file
        unlink($this->root . '/1234567890.abcdefghijabcdefghij.sess');

        // shouldn't throw an Exception
        $this->store->destroy(1234567890, 'abcdefghijabcdefghij');
    }

    /**
     * @expectedException     Kirby\Exception\NotFoundException
     * @expectedExceptionCode error.session.filestore.notFound
     */
    public function testAccessAfterDestroy()
    {
        // make sure we get a handle
        $this->assertEquals('1234567890', $this->store->get(1234567890, 'abcdefghijabcdefghij'));
        $this->store->lock(1234567890, 'abcdefghijabcdefghij');
        $this->assertHandleExists('1234567890.abcdefghijabcdefghij');

        // simulate that another thread deleted the file
        unlink($this->root . '/1234567890.abcdefghijabcdefghij.sess');

        // now it should throw even if there is already a handle
        $this->store->set(1234567890, 'abcdefghijabcdefghij', 'something else');
    }

    public function testCollectGarbage()
    {
        $this->assertFileExists($this->root . '/.gitignore');
        $this->assertFileExists($this->root . '/1234567890.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/1357913579.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/7777777777.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/9999999999.abcdefghijabcdefghij.sess');

        $this->store->collectGarbage();

        $this->assertFileExists($this->root . '/.gitignore');
        // TODO: Fix the following line
        // $this->assertFileNotExists($this->root . '/1234567890.abcdefghijabcdefghij.sess');
        $this->assertFileNotExists($this->root . '/1357913579.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/7777777777.abcdefghijabcdefghij.sess');
        $this->assertFileExists($this->root . '/9999999999.abcdefghijabcdefghij.sess');
    }

    /**
     * Asserts that the given session is currently locked
     *
     * @param  string $name Combined name
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
     * @param  string $name Combined name
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
     * @param  string $name Combined name
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
     * @param  string $name Combined name
     * @return void
     */
    protected function assertHandleNotExists(string $name)
    {
        $handles = $this->storeHandles->getValue($this->store);
        $this->assertFalse(isset($handles[$name]));
    }
}
