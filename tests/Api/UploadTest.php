<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Api\Upload
 */
class UploadTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Api.Upload';

	protected $app;

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		Blueprint::$loaded = [];
		ini_restore('upload_max_filesize');
		ini_restore('post_max_size');
		unset($_SERVER['HTTP_CF_CONNECTING_IP']);
		Dir::remove(static::TMP);
		App::destroy();
	}

	protected function api(array $props = []): Api
	{
		 return new Api($props);
	}

	protected function upload(array $api = []): Upload
	{
		return new Upload(api: $this->api($api));
	}

	/**
	 * @covers ::chunkId
	 */
	public function testChunkId()
	{
		$this->assertSame('abcd', Upload::chunkId('abcd'));
		$this->assertSame('abcd', Upload::chunkId('ab/cd'));
		$this->assertSame('abcd', Upload::chunkId('../../ab/cd'));
		$this->assertSame('abcd', Upload::chunkId('a-b/../cd.'));
	}

	/**
	 * @covers ::chunkSize
	 */
	public function testChunkSize()
	{
		ini_set('upload_max_filesize', '10M');
		ini_set('post_max_size', '20M');

		$this->assertSame(
			(int)floor(10 * 1024 * 1024 * 0.95),
			Upload::chunkSize()
		);

		// with CloudFlare
		ini_set('upload_max_filesize', '200M');
		ini_set('post_max_size', '200M');
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '1.1.1.1';

		$this->assertSame(
			(int)floor(100 * 1024 * 1024 * 0.95),
			Upload::chunkSize()
		);
	}

	/**
	 * @covers ::cleanTmpDirectory
	 */
	public function testCleanTmpDirectory()
	{
		$dir = static::TMP . '/site/cache/.uploads';
		F::write($a = $dir . '/abcd-a.md', '');
		F::write($b = $dir . '/efgh-b.md', '');
		touch($b, time() - 86400 - 1);

		$this->assertDirectoryExists($dir);
		$this->assertFileExists($a);
		$this->assertFileExists($b);

		Upload::cleanTmpDirectory();

		$this->assertDirectoryExists($dir);
		$this->assertFileExists($a);
		$this->assertFileDoesNotExist($b);

		touch($a, time() - 86400 - 1);

		Upload::cleanTmpDirectory();

		$this->assertDirectoryDoesNotExist($dir);
		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);
	}

	/**
	 * @covers ::processChunk
	 */
	public function testProcessChunkFirstChunkFullLength()
	{
		$source = static::TMP . '/test.md';
		F::write($source, 'abcdef');
		$size   = F::size($source);
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => $size,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->assertSame(
			$file = static::TMP . '/site/cache/.uploads/test.md',
			$upload->processChunk($source, basename($source))
		);
		$this->assertFileExists($file);
		$this->assertFileDoesNotExist('abcd-' . $file);
	}

	/**
	 * @covers ::processChunk
	 */
	public function testProcessChunkFirstChunkPartialLength()
	{
		$source = static::TMP . '/test.md';
		F::write($source, 'abcdef');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$dir = static::TMP . '/site/cache/.uploads';
		$this->assertNull($upload->processChunk($source, basename($source)));
		$this->assertFileDoesNotExist($dir . '/test.md');
		$this->assertFileExists($dir . '/abcd-test.md');
	}

	/**
	 * @covers ::processChunk
	 */
	public function testProcessChunkIdRemoveUnallowedCharacters()
	{
		$source = static::TMP . '/test.md';
		F::write($source, 'abcdef');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 0,
					'Upload-Id'     => '../a/b!!cd'
				]
			]
		]);

		$dir = static::TMP . '/site/cache/.uploads';
		$this->assertNull($upload->processChunk($source, basename($source)));
		$this->assertFileDoesNotExist($dir . '/test.md');
		$this->assertFileExists($dir . '/abcd-test.md');
	}

	/**
	 * @covers ::processChunk
	 */
	public function testProcessChunkFilenameNoDirectoryTraversal()
	{
		$source = static::TMP . '/test.md';
		F::write($source, 'abcdef');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$dir = static::TMP . '/site/cache/.uploads';
		$this->assertNull($upload->processChunk($source, '../../test.md'));
		$this->assertFileDoesNotExist($dir . '/test.md');
		$this->assertFileExists($dir . '/abcd-test.md');
	}

	/**
	 * @covers ::processChunk
	 */
	public function testProcessChunkSuccesfulAll()
	{
		$source = static::TMP . '/test.md';
		F::write($source, 'abc');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 6,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->assertNull($upload->processChunk($source, basename($source)));

		$source = static::TMP . '/test.md';
		F::write($source, 'def');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 6,
					'Upload-Offset' => 3,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$file = static::TMP . '/site/cache/.uploads/test.md';
		$this->assertSame($file, $upload->processChunk($source, 'test.md'));
		$this->assertFileExists($file);
		$this->assertFileDoesNotExist('abcd-' . $file);
		$this->assertSame('abcdef', F::read($file));
	}

	/**
	 * @covers ::validateChunk
	 */
	public function testValidateChunkDuplicate()
	{
		$source = static::TMP . '/test.md';
		F::write($source, 'abcdef');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->assertNull($upload->processChunk($source, basename($source)));
		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A tmp file upload with the same filename and upload id already exists: abcd-test.md');
		$upload->processChunk($source, basename($source));
	}

	/**
	 * @covers ::validateChunk
	 */
	public function testValidateChunkSubsequentInvalidOffset()
	{
		$source = static::TMP . '/a.md';
		F::write($source, 'abcdef');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->assertNull($upload->processChunk($source, basename($source)));

		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 1500,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Chunk offset 1500 does not match the existing tmp upload file size of 6');
		$upload->processChunk($source, basename($source));
	}

	/**
	 * @covers ::validateChunk
	 */
	public function testValidateChunkSubsequentNoFirst()
	{
		$source = static::TMP . '/a.md';
		F::write($source, 'abcdef');
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 10,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Chunk offset 10 for non-existing tmp file: abcd-a.md');
		$upload->processChunk($source, basename($source));
	}

	/**
	 * @covers ::validateChunk
	 */
	public function testValidateChunkInvalidExtension()
	{
		$source = static::TMP . '/a.php';
		$upload = $this->upload([
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('You are not allowed to upload PHP files');
		$upload->processChunk($source, basename($source));
	}

	/**
	 * @covers ::validateChunk
	 */
	public function testValidateChunkTooLargeTotal()
	{
		$source = static::TMP . '/a.md';
		$app    = $this->app->clone([
			'blueprints' => [
				'files/test' => [
					'name'   => 'test',
					'accept' => ['maxsize' => 100]
				]
			]
		]);
		$upload = $this->upload([
			'requestData' => [
				'body' => [
					'template' => 'test'
				],
				'headers' => [
					'Upload-Length' => 120,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.maxsize');
		$upload->processChunk($source, basename($source));
	}

	/**
	 * @covers ::validateChunk
	 */
	public function testValidateChunkTooLargeCurrentChunk()
	{
		$dir    = static::TMP . '/site/cache/.uploads';
		$source = static::TMP . '/a.md';
		F::write($dir . '/abcd-a.md', 'abcdef');
		F::write($source, 'ghijkl');

		$app    = $this->app->clone([
			'blueprints' => [
				'files/test' => [
					'name'   => 'test',
					'accept' => ['maxsize' => 10]
				]
			]
		]);
		$upload = $this->upload([
			'requestData' => [
				'body' => [
					'template' => 'test'
				],
				'headers' => [
					'Upload-Length' => 10,
					'Upload-Offset' => 6,
					'Upload-Id'     => 'abcd'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.maxsize');
		$upload->processChunk($source, basename($source));
	}
}
