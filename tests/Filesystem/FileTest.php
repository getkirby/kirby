<?php

namespace Kirby\Filesystem;

use Exception as GlobalException;
use JsonException;
use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Image\Dimensions;
use Kirby\Image\Exif;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP      = KIRBY_TMP_DIR . '/Filesystem.File';

	// used for the mocks
	public static array $block = [];
	public static array $mocks = [];

	protected function setUp(): void
	{
		Dir::copy(static::FIXTURES, static::TMP);

		static::$block = [];
		static::$mocks = [];
	}

	protected function tearDown(): void
	{
		if (file_exists(static::TMP . '/unreadable.txt') === true) {
			chmod(static::TMP . '/unreadable.txt', 0o755);
		}

		static::$block = [];
		static::$mocks = [];
		App::destroy();
		Dir::remove(static::TMP);
	}

	protected function _file($file = 'test.js')
	{
		return new File(
			root: static::TMP . '/' . $file
		);
	}

	public function testBase64(): void
	{
		$file   = $this->_file('real.svg');
		$base64 = file_get_contents(static::TMP . '/real.svg.base64');
		$this->assertSame($base64, $file->base64());
	}

	public function testCmsFileAsset(): void
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

		$this->assertSame($file->url(), $asset->url());
		$this->assertSame($file->root(), $asset->root());
	}

	public function testConstruct(): void
	{
		$file = new File(
			root: '/dev/null/test.pdf',
			url:  'https://foo.bar/test.pdf'
		);

		$this->assertSame('/dev/null/test.pdf', $file->root());
		$this->assertSame('https://foo.bar/test.pdf', $file->url());

		$file = new File(root: '/dev/null/test.js');
		$this->assertSame('/dev/null/test.js', $file->root());
		$this->assertNull($file->url());
	}

	public function testCopy(): void
	{
		$oldRoot = static::TMP . '/test.txt';
		$newRoot = static::TMP . '/awesome.txt';

		$file = new File(root: $oldRoot);
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

	public function testCopyFail(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file "' . static::TMP . '/awesome.txt" could not be copied');

		static::$block[] = 'copy';
		$file = new File(root: static::TMP . '/awesome.txt');
		$file->copy(static::TMP . '/copied.txt');
	}

	public function testCopyNonExisting(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file "' . static::TMP . '/a.txt" could not be copied');

		$file = $this->_file('a.txt');
		$file->copy(static::TMP . '/b.txt');
	}

	public function testCopyToExisting(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file "' . static::TMP . '/test.js" could not be copied');

		$file = $this->_file();
		$file->copy(static::TMP . '/folder/b.txt');
	}

	public function testDataUri(): void
	{
		$file = $this->_file('real.svg');
		$base64 = file_get_contents(static::TMP . '/real.svg.base64');
		$this->assertSame('data:image/svg+xml;base64,' . $base64, $file->dataUri());
	}

	public function testDataUriRaw(): void
	{
		$file = $this->_file('real.svg');
		$encoded = rawurlencode($file->read());
		$this->assertSame('data:image/svg+xml,' . $encoded, $file->dataUri(false));
	}

	public function testDelete(): void
	{
		$file = new File(root: static::TMP . '/test.txt');

		$file->write('test');
		$this->assertTrue($file->exists());

		$file->delete();
		$this->assertFalse($file->exists());
	}

	public function testDeleteFail(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file "' . static::TMP . '/test.js" could not be deleted');

		static::$block[] = 'unlink';
		$file = $this->_file();
		$file->delete();
	}

	public function testDeleteNotExisting(): void
	{
		$file = new File(root: 'test.txt');
		$this->assertFalse($file->exists());
		$this->assertTrue($file->delete());
	}

	public function testDimensionMethodsWithoutDimensions(): void
	{
		$file = $this->_file('blank.pdf');
		$this->assertNull($file->width());
		$this->assertNull($file->height());
		$this->assertNull($file->ratio());
		$this->assertFalse($file->orientation());
		$this->assertFalse($file->isLandscape());
		$this->assertFalse($file->isPortrait());
		$this->assertFalse($file->isSquare());
	}

	public function testDimensions(): void
	{
		$file = $this->_file('cat.jpg');
		$this->assertInstanceOf(Dimensions::class, $file->dimensions());
		$this->assertSame(500, $file->dimensions()->width());

		// cached object
		$this->assertSame($file->dimensions(), $file->dimensions());

		// files without visual dimensions
		$this->assertNull($this->_file('blank.pdf')->dimensions());
		$this->assertNull($this->_file('test.js')->dimensions());
	}

	public function testDownload(): void
	{
		$file = $this->_file();
		$this->assertIsString($file->download());
		$this->assertIsString($file->download('meow.jpg'));
	}

	public function testExif(): void
	{
		$this->assertInstanceOf(Exif::class, $this->_file('cat.jpg')->exif());
		$this->assertNull($this->_file('test.js')->exif());
	}

	public function testExists(): void
	{
		$file = $this->_file();
		$this->assertTrue($file->exists());

		$file = new File(root: 'does-not-exist.jpg');
		$this->assertFalse($file->exists());
	}

	public function testExtension(): void
	{
		$file = $this->_file();
		$this->assertSame('js', $file->extension());
	}

	public function testFilename(): void
	{
		$file = $this->_file();
		$this->assertSame('test.js', $file->filename());
	}

	public function testHash(): void
	{
		$file = $this->_file();
		$this->assertIsString($file->hash());
	}

	public function testHeader(): void
	{
		$file = $this->_file();
		$this->assertInstanceOf(Response::class, $file->header(false));
	}

	public function testHeaderSend(): void
	{
		$file = $this->_file();
		$this->assertNull($file->header());
	}

	public function testHtml(): void
	{
		$file = new File(
			root: static::TMP . '/blank.pdf',
			url:  'https://foo.bar/blank.pdf'
		);
		$this->assertSame('<a href="https://foo.bar/blank.pdf">foo.bar/blank.pdf</a>', $file->html());
	}

	public function testHtmlImage(): void
	{
		$file = new File(
			root: static::TMP . '/cat.jpg',
			url:  'https://foo.bar/cat.jpg'
		);
		$this->assertSame('<img alt="" src="https://foo.bar/cat.jpg">', $file->html());
		$this->assertSame('<img alt="" src="https://foo.bar/cat.jpg">', (string)$file);
	}

	public function testIs(): void
	{
		$file = $this->_file();
		$this->assertTrue($file->is('text/plain'));
		$this->assertFalse($file->is('image/jpeg'));

		$this->assertTrue($file->is('js'));
		$this->assertFalse($file->is('jpg'));
	}

	public function testIsReadable(): void
	{
		$file = $this->_file();
		$this->assertSame(is_readable($file->root()), $file->isReadable());
	}

	public function testIsResizable(): void
	{
		$file = $this->_file();
		$this->assertFalse($file->isResizable());
	}

	public function testIsViewable(): void
	{
		$file = $this->_file();
		$this->assertFalse($file->isViewable());
	}

	public function testIsWritable(): void
	{
		$file = $this->_file();
		$this->assertTrue($file->isWritable());

		$file = new File(root: static::TMP . '/permissions/unwritable/test.txt');
		$this->assertFalse($file->isWritable());

		$file = new File(root: static::TMP . '/permissions/unwritable.txt');
		$this->assertFalse($file->isWritable());
	}

	public function testKirby(): void
	{
		$file = $this->_file();

		$this->assertNull($file->kirby());

		App::instance();
		$this->assertInstanceOf(App::class, $file->kirby());
	}

	public function testMatch(): void
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

	public function testMatchDimensionRulesWithoutDimensions(): void
	{
		$file = $this->_file('blank.pdf');
		$this->assertTrue($file->match([
			'maxwidth'    => 100,
			'minheight'   => 100,
			'orientation' => 'portrait'
		]));

		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.maxwidth');
		$this->_file('cat.jpg')->match(['maxwidth' => 100]);
	}

	public function testMatchExtensionException(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.extension.invalid');
		$this->expectExceptionMessageIs('Invalid extension: js');

		// load translations to get the correct exception message
		App::instance();

		$this->_file()->match(['extension' => ['png', 'pdf']]);
	}

	public function testMatchMimeInvalid(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.mime.invalid');
		$this->expectExceptionMessageIs('Invalid mime type: text/plain');

		// load translations to get the correct exception message
		App::instance();

		$this->_file()->match(['mime' => ['image/png', 'application/pdf']]);
	}

	public function testMatchMimeMissing(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.mime.missing');
		$this->expectExceptionMessageIs('The media type for "doesnotexist.invalid" cannot be detected');

		$this->_file('doesnotexist.invalid')->match(['mime' => ['image/png', 'application/pdf']]);
	}

	public function testMatchTypeException(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.type.invalid');
		$this->expectExceptionMessageIs('Invalid file type: code');

		// load translations to get the correct exception message
		App::instance();

		$this->_file()->match(['type' => ['document', 'video']]);
	}

	public function testMime(): void
	{
		$file = $this->_file();
		$this->assertSame('text/plain', $file->mime());
	}

	public function testModified(): void
	{
		// existing file
		$file = $this->_file();
		$this->assertSame(F::modified($file->root()), $file->modified());

		$this->assertSame(@strftime('%d.%m.%Y', F::modified($file->root())), $file->modified('%d.%m.%Y', 'strftime'));

		// non-existing file
		$file = $this->_file('does/not/exist.js');
		$this->assertFalse($file->modified());
	}

	public function testMove(): void
	{
		$oldRoot = static::TMP . '/test.txt';
		$newRoot = static::TMP . '/awesome.txt';

		$file = new File(root: $oldRoot);
		$file->write('test');

		$this->assertFileExists($oldRoot);
		$this->assertFileDoesNotExist($newRoot);
		$this->assertSame($oldRoot, $file->root());

		$moved = $file->move($newRoot);

		$this->assertFileDoesNotExist($oldRoot);
		$this->assertFileExists($newRoot);
		$this->assertSame($newRoot, $moved->root());
	}

	public function testMoveFail(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file: "' . static::TMP . '/awesome.txt" could not be moved to: "' . static::TMP . '/moved.txt"');

		static::$block[] = 'rename';
		$file = new File(root: static::TMP . '/awesome.txt');
		$file->move(static::TMP . '/moved.txt');
	}

	public function testMoveNonExisting(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file: "' . static::TMP . '/a.txt" could not be moved to: "' . static::TMP . '/b.txt"');

		$file = $this->_file('a.txt');
		$file->move(static::TMP . '/b.txt');
	}

	public function testMoveToExisting(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file: "' . static::TMP . '/test.js" could not be moved to: "' . static::TMP . '/folder/b.txt"');

		$file = $this->_file();
		$file->move(static::TMP . '/folder/b.txt');
	}

	public function testName(): void
	{
		$file = $this->_file();
		$this->assertSame('test', $file->name());
	}

	public function testNiceSize(): void
	{
		// existing file
		$file = $this->_file('test.js');
		$this->assertSame('14 B', $file->niceSize());

		// non-existing file
		$file = $this->_file('does/not/exist.js');
		$this->assertSame('0 KB', $file->niceSize());
	}

	public function testRead(): void
	{
		$file = $this->_file();
		$this->assertSame(file_get_contents($file->root()), $file->read());
	}

	public function testReadNotExist(): void
	{
		$file = $this->_file('missing.txt');
		$this->assertFalse($file->read());
	}

	public function testRealpath(): void
	{
		$file = $this->_file();
		$this->assertSame(static::TMP . '/test.js', $file->realpath());
	}

	public function testRealpathMissingFile(): void
	{
		$file = $this->_file('does-not-exist.txt');
		$this->assertFalse($file->realpath());
	}

	public function testRename(): void
	{
		$file = new File(root: static::TMP . '/test.js');
		$file->write('test');

		$renamed = $file->rename('awesome');
		$this->assertSame('awesome.js', $renamed->filename());
		$this->assertSame('awesome', $renamed->name());
	}

	public function testRenameFail(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file: "' . static::TMP . '/test.js" could not be renamed to: "awesome"');

		static::$block[] = 'rename';
		$file = $this->_file();
		$renamed = $file->rename('awesome');
	}

	public function testRenameSameRoot(): void
	{
		$file = new File(root: static::TMP . '/test.txt');
		$file->write('test');
		$file->rename('test');

		$this->assertSame('test.txt', $file->filename());
		$this->assertSame(static::TMP . '/test.txt', $file->root());
	}

	public function testRoot(): void
	{
		$file = $this->_file();
		$this->assertSame(static::TMP . '/test.js', $file->root());
	}

	public function testSanitizeContentsMissingHandler(): void
	{
		$file = new File(root: static::TMP . '/test.js');

		// lazy mode
		$file->sanitizeContents(true);

		// default mode
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessageIs('Missing handler for type: "js"');

		$file->sanitizeContents();
	}

	public function testSanitizeContentsValid(): void
	{
		$fixture = static::TMP . '/clean.svg';
		$tmp     = static::TMP . '/clean.svg';
		copy($fixture, $tmp);

		$file = new File(root: $tmp);
		$this->assertNull($file->sanitizeContents());
		$this->assertNull($file->sanitizeContents(true));
		$this->assertNull($file->sanitizeContents(false));

		$this->assertFileEquals($fixture, $tmp);
	}

	public function testSanitizeContentsWrongType(): void
	{
		$fixture = static::TMP . '/real.svg';
		$tmp     = static::TMP . '/real.svg';
		copy($fixture, $tmp);

		$file = new File(root: $tmp);
		$file->sanitizeContents('xml');

		$this->assertFileEquals(static::TMP . '/real.sanitized.svg', $tmp);
	}

	public function testSha1(): void
	{
		$file = $this->_file('test.js');
		$this->assertSame('25f2d6df4f2a30f29f6f80da1e95011044b0b8f7', $file->sha1());
	}

	public function testSha1MissingFile(): void
	{
		$file = $this->_file('does-not-exist.txt');

		$this->expectException(Exception::class);
		$this->expectExceptionMessageIs('The file "' . static::TMP . '/does-not-exist.txt" could not be hashed');

		$file->sha1();
	}

	public function testSize(): void
	{
		$file = $this->_file('test.js');
		$this->assertSame(14, $file->size());
	}

	public function testToArray(): void
	{
		$file = $this->_file('blank.pdf');
		$this->assertSame('blank.pdf', $file->toArray()['filename']);
		$this->assertSame('blank', $file->toArray()['name']);
		$this->assertSame('pdf', $file->toArray()['extension']);
		$this->assertFalse($file->toArray()['isResizable']);
		$this->assertSame($file->toArray(), $file->__debugInfo());
	}

	public function testToArrayImage(): void
	{
		$array = $this->_file('cat.jpg')->toArray();
		$this->assertSame(500, $array['dimensions']['width']);
		$this->assertIsArray($array['exif']);

		$array = $this->_file('blank.pdf')->toArray();
		$this->assertNull($array['dimensions']);
		$this->assertNull($array['exif']);
	}

	public function testToJson(): void
	{
		$file = $this->_file();
		$this->assertIsString($json = $file->toJson());
		$this->assertSame('test.js', json_decode($json)->filename);
	}

	public function testToJsonInvalid(): void
	{
		$file = $this->_file("invalid-\xB1\x31.txt");

		$this->expectException(JsonException::class);
		$file->toJson();
	}

	public function testToString(): void
	{
		$file = new File(
			root: static::TMP . '/blank.pdf',
			url:  $expected = 'https://foo.bar/blank.pdf'
		);

		$this->assertSame($expected, (string)$file);
		$this->assertSame($expected, $file->__toString());

		$file = new File(
			root: $expected = static::TMP . '/blank.pdf'
		);

		$this->assertSame($expected, (string)$file);
		$this->assertSame($expected, $file->__toString());

		$file = new File();

		$this->assertSame('', (string)$file);
		$this->assertSame('', $file->__toString());
	}

	public function testType(): void
	{
		$file = $this->_file();
		$this->assertSame('code', $file->type());
	}

	public function testTypeUnknown(): void
	{
		$file = $this->_file('test.kirby');
		$this->assertNull($file->type());
	}

	public function testUrlLazy(): void
	{
		$calls = 0;
		$file  = new File(
			root: static::TMP . '/test.js',
			url:  function () use (&$calls) {
				$calls++;
				return 'https://foo.bar/test.js';
			}
		);

		$this->assertSame(0, $calls);
		$this->assertSame('https://foo.bar/test.js', $file->url());
		$this->assertSame('https://foo.bar/test.js', $file->url());
		$this->assertSame(1, $calls);
	}

	public function testValidateContentsMissingHandler(): void
	{
		$file = new File(root: static::TMP . '/test.js');

		// lazy mode
		$file->validateContents(true);

		// default mode
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessageIs('Missing handler for type: "js"');

		$file->validateContents();
	}

	public function testValidateContentsValid(): void
	{
		$file = new File(root: static::TMP . '/real.svg');
		$this->assertNull($file->validateContents());
		$this->assertNull($file->validateContents(true));
		$this->assertNull($file->validateContents(false));
	}

	public function testValidateContentsWrongType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('The namespace "http://www.w3.org/2000/svg" is not allowed (around line 2)');

		$file = new File(root: static::TMP . '/real.svg');
		$file->validateContents('xml');
	}

	public function testWrite(): void
	{
		$root = static::TMP . '/test.txt';

		$file = new File(root: $root);
		$this->assertFalse($file->exists());

		$file->write('test');
		$this->assertTrue($file->exists());
		$this->assertSame('test', file_get_contents($file->root()));
	}

	public function testWriteFail(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file "' . static::TMP . '/test.txt" could not be written');

		static::$block[] = 'file_put_contents';
		$file = new File(root: static::TMP . '/test.txt');
		$file->write('test');
	}

	public function testWriteUnwritable(): void
	{
		$this->expectException(GlobalException::class);
		$this->expectExceptionMessageIs('The file "' . static::TMP . '/unwritable.txt" is not writable');

		$file = new File(root: static::TMP . '/unwritable.txt');
		$file->write('test');
		chmod($file->root(), 0o555);
		$file->write('kirby');
	}
}
