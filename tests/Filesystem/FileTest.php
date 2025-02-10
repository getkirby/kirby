<?php

namespace Kirby\Filesystem;

use Exception as GlobalException;
use Kirby\Cms\App;
use Kirby\Cms\File as CmsFile;
use Kirby\Cms\Page;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class InvalidFileModel
{
	public string $foo = 'bar';
}

#[CoversClass(File::class)]
class FileTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP      = KIRBY_TMP_DIR . '/Filesystem.File';

	// used for the mocks
	public static array $block = [];

	protected function setUp(): void
	{
		Dir::copy(static::FIXTURES, static::TMP);

		static::$block = [];
	}

	public function tearDown(): void
	{
		if (file_exists(static::TMP . '/unreadable.txt') === true) {
			chmod(static::TMP . '/unreadable.txt', 0o755);
		}

		static::$block = [];
		App::destroy();
		Dir::remove(static::TMP);
	}

	protected function _file($file = 'test.js')
	{
		return new File([
			'root' => static::TMP . '/' . $file
		]);
	}

	public function testConstruct()
	{
		$file = new File([
			'root' => '/dev/null/test.pdf',
			'url'  => 'https://foo.bar/test.pdf'
		]);

		$this->assertSame('/dev/null/test.pdf', $file->root());
		$this->assertSame('https://foo.bar/test.pdf', $file->url());

		$file = new File('/dev/null/test.js');
		$this->assertSame('/dev/null/test.js', $file->root());
		$this->assertNull($file->url());
	}

	public function testLegacyConstruct()
	{
		$file = new File(
			'/dev/null/test.pdf',
			'https://home.io/test.pdf'
		);
		$this->assertSame('/dev/null/test.pdf', $file->root());
		$this->assertSame('https://home.io/test.pdf', $file->url());
	}

	public function testBase64()
	{
		$file   = $this->_file('real.svg');
		$base64 = file_get_contents(static::TMP . '/real.svg.base64');
		$this->assertSame($base64, $file->base64());
	}

	public function testCopy()
	{
		$oldRoot = static::TMP . '/test.txt';
		$newRoot = static::TMP . '/awesome.txt';

		$file = new File($oldRoot);
		$file->write('test');

		$this->assertFileExists($oldRoot);
		$this->assertFileDoesNotExist($newRoot);
		$this->assertSame($oldRoot, $file->root());

		$new = $file->copy($newRoot);

		$this->assertFileExists($oldRoot);
		$this->assertFileExists($newRoot);
		$this->assertInstanceOf(File::class, $new);
		$this->assertSame($newRoot, $new->root());
	}

	public function testCopyToExisting()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be copied');

		$file = $this->_file();
		$file->copy(static::TMP . '/folder/b.txt');
	}

	public function testCopyNonExisting()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be copied');

		$file = $this->_file('a.txt');
		$file->copy(static::TMP . '/b.txt');
	}

	public function testCopyFail()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be copied');

		static::$block[] = 'copy';
		$file = new File(static::TMP . '/awesome.txt');
		$file->copy(static::TMP . '/copied.txt');
	}

	public function testDataUri()
	{
		$file = $this->_file('real.svg');
		$base64 = file_get_contents(static::TMP . '/real.svg.base64');
		$this->assertSame('data:image/svg+xml;base64,' . $base64, $file->dataUri());
	}

	public function testDataUriRaw()
	{
		$file = $this->_file('real.svg');
		$encoded = rawurlencode($file->read());
		$this->assertSame('data:image/svg+xml,' . $encoded, $file->dataUri(false));
	}

	public function testDelete()
	{
		$file = new File(static::TMP . '/test.txt');

		$file->write('test');
		$this->assertTrue($file->exists());

		$file->delete();
		$this->assertFalse($file->exists());
	}

	public function testDeleteNotExisting()
	{
		$file = new File('test.txt');
		$this->assertFalse($file->exists());
		$this->assertTrue($file->delete());
	}

	public function testDeleteFail()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be deleted');

		static::$block[] = 'unlink';
		$file = $this->_file();
		$file->delete();
	}

	public function testDownload()
	{
		$file = $this->_file();
		$this->assertIsString($file->download());
		$this->assertIsString($file->download('meow.jpg'));
	}

	public function testExists()
	{
		$file = $this->_file();
		$this->assertTrue($file->exists());

		$file = new File('does-not-exist.jpg');
		$this->assertFalse($file->exists());
	}

	public function testExtension()
	{
		$file = $this->_file();
		$this->assertSame('js', $file->extension());
	}

	public function testFilename()
	{
		$file = $this->_file();
		$this->assertSame('test.js', $file->filename());
	}

	public function testHash()
	{
		$file = $this->_file();
		$this->assertIsString($file->hash());
	}

	public function testHeader()
	{
		$file = $this->_file();
		$this->assertInstanceOf(Response::class, $file->header(false));
	}

	public function testHeaderSend()
	{
		$file = $this->_file();
		$this->assertNull($file->header());
	}

	public function testHtml()
	{
		$file = new File([
			'root' => static::TMP . '/blank.pdf',
			'url'  => 'https://foo.bar/blank.pdf'
		]);
		$this->assertSame('<a href="https://foo.bar/blank.pdf">foo.bar/blank.pdf</a>', $file->html());
	}

	public function testIs()
	{
		$file = $this->_file();
		$this->assertTrue($file->is('text/plain'));
		$this->assertFalse($file->is('image/jpeg'));

		$this->assertTrue($file->is('js'));
		$this->assertFalse($file->is('jpg'));
	}

	public function testIsReadable()
	{
		$file = $this->_file();
		$this->assertSame(is_readable($file->root()), $file->isReadable());
	}

	public function testIsResizable()
	{
		$file = $this->_file();
		$this->assertFalse($file->isResizable());
	}

	public function testIsViewable()
	{
		$file = $this->_file();
		$this->assertFalse($file->isViewable());
	}

	public function testIsWritable()
	{
		$file = $this->_file();
		$this->assertTrue($file->isWritable());

		$file = new File(static::TMP . '/permissions/unwritable/test.txt');
		$this->assertFalse($file->isWritable());

		$file = new File(static::TMP . '/permissions/unwritable.txt');
		$this->assertFalse($file->isWritable());
	}

	public function testKirby()
	{
		$file = $this->_file();

		$this->assertNull($file->kirby());

		App::instance();
		$this->assertInstanceOf(App::class, $file->kirby());
	}

	public function testMatch()
	{
		$rules = [
			'miMe'        => ['image/png', 'image/jpeg', 'application/pdf'],
			'extensION'   => ['jpg', 'pdf'],
			'tYPe'        => ['image', 'video'],
			'MINsize'     => 20000,
			'maxSIze'     => 25000
		];

		$this->assertTrue($this->_file('cat.jpg')->match($rules));
	}

	public function testMatchMimeMissing()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The media type for "doesnotexist.invalid" cannot be detected');

		$this->_file('doesnotexist.invalid')->match(['mime' => ['image/png', 'application/pdf']]);
	}

	public function testMatchMimeInvalid()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid mime type: text/plain');

		// load translations to get the correct exception message
		App::instance();

		$this->_file()->match(['mime' => ['image/png', 'application/pdf']]);
	}

	public function testMatchExtensionException()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid extension: js');

		// load translations to get the correct exception message
		App::instance();

		$this->_file()->match(['extension' => ['png', 'pdf']]);
	}

	public function testMatchTypeException()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid file type: code');

		// load translations to get the correct exception message
		App::instance();

		$this->_file()->match(['type' => ['document', 'video']]);
	}

	public function testMime()
	{
		$file = $this->_file();
		$this->assertSame('text/plain', $file->mime());
	}

	public function testModel()
	{
		$parent = Page::factory(['slug' => 'test']);
		$model = CmsFile::factory([
			'filename' => 'test.js',
			'parent' => $parent
		]);

		$file = new File([
			'root' => static::TMP . '/test.js',
			'model' => $model
		]);

		$this->assertTrue(in_array(IsFile::class, class_uses($file->model())));
		$this->assertSame($model, $file->model());
	}

	public function testParentModel()
	{
		$parent = Page::factory([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			]
		]);

		$file = $parent->file('a.jpg');
		$asset = $file->asset();

		$this->assertTrue(in_array(IsFile::class, class_uses($asset->model())));
		$this->assertSame($file, $asset->model());
		$this->assertSame($file->url(), $asset->url());
		$this->assertSame($file->root(), $asset->root());
	}

	public function testInvalidModel()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The model object must use the "Kirby\Filesystem\IsFile" trait');

		new File([
			'root' => static::TMP . '/test.js',
			'model' => new InvalidFileModel()
		]);
	}

	public function testModified()
	{
		// existing file
		$file = $this->_file();
		$this->assertSame(F::modified($file->root()), $file->modified());

		$this->assertSame(@strftime('%d.%m.%Y', F::modified($file->root())), $file->modified('%d.%m.%Y', 'strftime'));

		// non-existing file
		$file = $this->_file('does/not/exist.js');
		$this->assertFalse($file->modified());
	}

	public function testMove()
	{
		$oldRoot = static::TMP . '/test.txt';
		$newRoot = static::TMP . '/awesome.txt';

		$file = new File($oldRoot);
		$file->write('test');

		$this->assertFileExists($oldRoot);
		$this->assertFileDoesNotExist($newRoot);
		$this->assertSame($oldRoot, $file->root());

		$moved = $file->move($newRoot);

		$this->assertFileDoesNotExist($oldRoot);
		$this->assertFileExists($newRoot);
		$this->assertSame($newRoot, $moved->root());
	}

	public function testMoveToExisting()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be moved');

		$file = $this->_file();
		$file->move(static::TMP . '/folder/b.txt');
	}

	public function testMoveNonExisting()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be moved');

		$file = $this->_file('a.txt');
		$file->move(static::TMP . '/b.txt');
	}

	public function testMoveFail()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be moved');

		static::$block[] = 'rename';
		$file = new File(static::TMP . '/awesome.txt');
		$file->move(static::TMP . '/moved.txt');
	}

	public function testName()
	{
		$file = $this->_file();
		$this->assertSame('test', $file->name());
	}

	public function testNiceSize()
	{
		// existing file
		$file = $this->_file('test.js');
		$this->assertSame('14 B', $file->niceSize());

		// non-existing file
		$file = $this->_file('does/not/exist.js');
		$this->assertSame('0 KB', $file->niceSize());
	}

	public function testRead()
	{
		$file = $this->_file();
		$this->assertSame(file_get_contents($file->root()), $file->read());
	}

	public function testReadNotExist()
	{
		$file = $this->_file('missing.txt');
		$this->assertFalse($file->read());
	}

	public function testReadUnreadble()
	{
		$file = new File(static::TMP . '/unreadable.txt');
		$file->write('test');
		chmod($file->root(), 0o000);
		$this->assertFalse($file->read());
	}

	public function testRename()
	{
		$file = new File(static::TMP . '/test.js');
		$file->write('test');

		$renamed = $file->rename('awesome');
		$this->assertSame('awesome.js', $renamed->filename());
		$this->assertSame('awesome', $renamed->name());
	}

	public function testRenameFail()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('The file: "' . static::TMP . '/test.js" could not be renamed to: "awesome"');

		static::$block[] = 'rename';
		$file = $this->_file();
		$renamed = $file->rename('awesome');
	}

	public function testRenameSameRoot()
	{
		$file = new File(static::TMP . '/test.txt');
		$file->write('test');
		$file->rename('test');

		$this->assertSame('test.txt', $file->filename());
		$this->assertSame(static::TMP . '/test.txt', $file->root());
	}

	public function testRoot()
	{
		$file = $this->_file();
		$this->assertSame(static::TMP . '/test.js', $file->root());
		$this->assertSame(static::TMP . '/test.js', $file->realpath());
	}

	public function testSanitizeContentsValid()
	{
		$fixture = static::TMP . '/clean.svg';
		$tmp     = static::TMP . '/clean.svg';
		copy($fixture, $tmp);

		$file = new File($tmp);
		$this->assertNull($file->sanitizeContents());
		$this->assertNull($file->sanitizeContents(true));
		$this->assertNull($file->sanitizeContents(false));

		$this->assertFileEquals($fixture, $tmp);
	}

	public function testSanitizeContentsWrongType()
	{
		$fixture = static::TMP . '/real.svg';
		$tmp     = static::TMP . '/real.svg';
		copy($fixture, $tmp);

		$file = new File($tmp);
		$file->sanitizeContents('xml');

		$this->assertFileEquals(static::TMP . '/real.sanitized.svg', $tmp);
	}

	public function testSanitizeContentsMissingHandler()
	{
		$file = new File(static::TMP . '/test.js');

		// lazy mode
		$file->sanitizeContents(true);

		// default mode
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Missing handler for type: "js"');

		$file->sanitizeContents();
	}

	public function testSize()
	{
		$file = $this->_file('test.js');
		$this->assertSame(14, $file->size());
	}

	public function testSha1()
	{
		$file = $this->_file('test.js');
		$this->assertSame('25f2d6df4f2a30f29f6f80da1e95011044b0b8f7', $file->sha1());
	}

	public function testToArray()
	{
		$file = $this->_file('blank.pdf');
		$this->assertSame('blank.pdf', $file->toArray()['filename']);
		$this->assertSame('blank', $file->toArray()['name']);
		$this->assertSame('pdf', $file->toArray()['extension']);
		$this->assertFalse($file->toArray()['isResizable']);
		$this->assertSame($file->toArray(), $file->__debugInfo());
	}

	public function testToJson()
	{
		$file = $this->_file();
		$this->assertIsString($json = $file->toJson());
		$this->assertSame('test.js', json_decode($json)->filename);
	}

	public function testToString()
	{
		$file = new File([
			'root' => static::TMP . '/blank.pdf',
			'url'  => $expected = 'https://foo.bar/blank.pdf'
		]);

		$this->assertSame($expected, (string)$file);
		$this->assertSame($expected, $file->__toString());

		$file = new File([
			'root' => $expected = static::TMP . '/blank.pdf'
		]);

		$this->assertSame($expected, (string)$file);
		$this->assertSame($expected, $file->__toString());

		$file = new File([]);

		$this->assertSame('', (string)$file);
		$this->assertSame('', $file->__toString());
	}

	public function testType()
	{
		$file = $this->_file();
		$this->assertSame('code', $file->type());
	}

	public function testTypeUnknown()
	{
		$file = $this->_file('test.kirby');
		$this->assertNull($file->type());
	}

	public function testValidateContentsValid()
	{
		$file = new File(static::TMP . '/real.svg');
		$this->assertNull($file->validateContents());
		$this->assertNull($file->validateContents(true));
		$this->assertNull($file->validateContents(false));
	}

	public function testValidateContentsWrongType()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The namespace "http://www.w3.org/2000/svg" is not allowed (around line 2)');

		$file = new File(static::TMP . '/real.svg');
		$file->validateContents('xml');
	}

	public function testValidateContentsMissingHandler()
	{
		$file = new File(static::TMP . '/test.js');

		// lazy mode
		$file->validateContents(true);

		// default mode
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Missing handler for type: "js"');

		$file->validateContents();
	}

	public function testWrite()
	{
		$root = static::TMP . '/test.txt';

		$file = new File($root);
		$this->assertFalse($file->exists());

		$file->write('test');
		$this->assertTrue($file->exists());
		$this->assertSame('test', file_get_contents($file->root()));
	}

	public function testWriteUnwritable()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('is not writable');

		$file = new File(static::TMP . '/unwritable.txt');
		$file->write('test');
		chmod($file->root(), 0o555);
		$file->write('kirby');
	}

	public function testWriteFail()
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessage('could not be written');

		static::$block[] = 'file_put_contents';
		$file = new File(static::TMP . '/test.txt');
		$file->write('test');
	}
}
