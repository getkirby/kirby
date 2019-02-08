<?php

namespace Kirby\Toolkit;

class FTest extends TestCase
{
    public function setUp(): void
    {
        $this->fixtures = __DIR__ . '/fixtures/f';
        $this->tmp      = $this->fixtures . '/test.txt';
        $this->moved    = $this->fixtures . '/moved.txt';

        Dir::remove($this->fixtures);
        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testAppend()
    {
        $this->assertTrue(F::append($this->tmp, ' is awesome'));
    }

    public function testCopy()
    {
        F::write($this->tmp, 'test');

        $this->assertFalse(file_exists($this->moved));

        $this->assertTrue(F::copy($this->tmp, $this->moved));

        $this->assertTrue(file_exists($this->moved));
    }

    public function testDirname()
    {
        $this->assertEquals(dirname($this->tmp), F::dirname($this->tmp));
    }

    public function testExists()
    {
        touch($this->tmp);

        $this->assertTrue(F::exists($this->tmp));
    }

    public function testExtension()
    {
        $this->assertEquals('php', F::extension(__FILE__));
        $this->assertEquals('test.jpg', F::extension($this->tmp, 'jpg'));
    }

    public function testExtensionToType()
    {
        $this->assertEquals('image', F::extensionToType('jpg'));
        $this->assertFalse(F::extensionToType('something'));
    }

    public function testExtensions()
    {
        $this->assertEquals(array_keys(Mime::types()), F::extensions());
        $this->assertEquals(F::$types['image'], F::extensions('image'));
        $this->assertEquals([], F::extensions('unknown-type'));
    }

    public function testFilename()
    {
        $this->assertEquals('test.txt', F::filename($this->tmp));
    }

    public function testIs()
    {
        F::write($this->tmp, 'test');

        $this->assertTrue(F::is($this->tmp, 'txt'));
        $this->assertTrue(F::is($this->tmp, 'text/plain'));
        $this->assertFalse(F::is($this->tmp, 'something/weird'));
        $this->assertFalse(F::is($this->tmp, 'no-clue'));
    }

    public function testIsReadable()
    {
        F::write($this->tmp, 'test');

        $this->assertEquals(is_readable($this->tmp), F::isReadable($this->tmp));
    }

    public function testIsWritable()
    {
        F::write($this->tmp, 'test');

        $this->assertEquals(is_writable($this->tmp), F::isWritable($this->tmp));
    }

    public function testMove()
    {
        F::write($this->tmp, 'test');

        $this->assertFalse(file_exists($this->moved));
        $this->assertTrue(file_exists($this->tmp));

        $this->assertTrue(F::move($this->tmp, $this->moved));

        $this->assertTrue(file_exists($this->moved));
        $this->assertFalse(file_exists($this->tmp));
    }

    public function testMime()
    {
        F::write($this->tmp, 'test');

        $this->assertEquals('text/plain', F::mime($this->tmp));
    }

    public function testMimeToExtension()
    {
        $this->assertEquals('jpg', F::mimeToExtension('image/jpeg'));
        $this->assertEquals(false, F::mimeToExtension('image/something'));
    }

    public function testMimeToType()
    {
        $this->assertEquals('image', F::mimeToType('image/jpeg'));
        $this->assertEquals(false, F::mimeToType('image/something'));
    }

    public function testModified()
    {
        F::write($this->tmp, 'test');

        $this->assertEquals(filemtime($this->tmp), F::modified($this->tmp));
    }

    public function testName()
    {
        $this->assertEquals('test', F::name($this->tmp));
    }

    public function testNiceSize()
    {
        F::write($this->tmp, 'test');

        $this->assertEquals('4 B', F::niceSize($this->tmp));
        $this->assertEquals('4 B', F::niceSize(4));
    }

    public function testRead()
    {
        file_put_contents($this->tmp, $content = 'my content is awesome');

        $this->assertEquals($content, F::read($this->tmp));
    }

    public function testRemove()
    {
        F::write($a = $this->fixtures . '/a.jpg', '');

        $this->assertFileExists($a);

        F::remove($this->fixtures . '/a.jpg');

        $this->assertFileNotExists($a);
    }

    public function testRemoveGlob()
    {
        F::write($a = $this->fixtures . '/a.jpg', '');
        F::write($b = $this->fixtures . '/a.1234.jpg', '');
        F::write($c = $this->fixtures . '/a.3456.jpg', '');

        $this->assertFileExists($a);
        $this->assertFileExists($b);
        $this->assertFileExists($c);

        F::remove($this->fixtures . '/a*.jpg');

        $this->assertFileNotExists($a);
        $this->assertFileNotExists($b);
        $this->assertFileNotExists($c);
    }

    public function testSafeName()
    {
        // with extension
        $this->assertEquals('uber-genious.txt', F::safeName('über genious.txt'));

        // with unsafe extension
        $this->assertEquals('uber-genious.taxt', F::safeName('über genious.täxt'));

        // without extension
        $this->assertEquals('uber-genious', F::safeName('über genious'));

        // with leading dash
        $this->assertEquals('super.jpg', F::safeName('-super.jpg'));

        // with leading underscore
        $this->assertEquals('super.jpg', F::safeName('_super.jpg'));

        // with leading dot
        $this->assertEquals('super.jpg', F::safeName('.super.jpg'));
    }

    public function testSize()
    {
        F::write($this->tmp, 'test');

        $this->assertEquals(4, F::size($this->tmp));
    }

    public function testType()
    {
        $this->assertEquals('image', F::type('jpg'));
        $this->assertEquals('document', F::type('pdf'));
        $this->assertEquals('archive', F::type('zip'));
        $this->assertEquals('code', F::type('css'));
        $this->assertEquals('code', F::type('content.php'));
        $this->assertEquals('code', F::type('py'));
        $this->assertEquals('code', F::type('java'));
    }

    public function testURI()
    {
        F::write($this->tmp, 'test');

        $expected = 'dGVzdA==';
        $this->assertEquals($expected, F::base64($this->tmp));

        $expected = 'data:text/plain;base64,dGVzdA==';
        $this->assertEquals($expected, F::uri($this->tmp));
    }

    public function testUriOfNonExistingFile()
    {
        $this->assertFalse(F::uri('/does-not-exist'));
    }

    public function testWrite()
    {
        $this->assertTrue(F::write($this->tmp, 'my content'));
    }

    public function testSimilar()
    {
        F::write($a = $this->fixtures . '/a.jpg', '');
        F::write($b = $this->fixtures . '/a.1234.jpg', '');
        F::write($c = $this->fixtures . '/a.3456.jpg', '');

        $expected = [
            $b,
            $c,
            $a
        ];

        $this->assertEquals($expected, F::similar($a));
    }
}
