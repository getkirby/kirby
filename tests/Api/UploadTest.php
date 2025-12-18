<?php

namespace Kirby\Api;

use Exception;
use Kirby\Blueprint\Blueprint;
use Kirby\Cms\App;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Upload::class)]
class UploadTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Api.Upload';

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

	protected function upload(
		array $api = [],
		bool $single = true,
		bool $debug = false
	): Upload {
		return new Upload(
			api:    $this->api($api),
			single: $single,
			debug:  $debug
		);
	}

	public function testChunkId(): void
	{
		$this->assertSame('abcd', Upload::chunkId('abcd'));
		$this->assertSame('abcd', Upload::chunkId('ab/cd'));
		$this->assertSame('abcd', Upload::chunkId('../../ab/cd'));
		$this->assertSame('abcd', Upload::chunkId('a-b/../cd.'));
	}

	public function testChunkIdInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Chunk ID must at least be 3 characters long');
		Upload::chunkId('a-b-');
	}

	public function testChunkSize(): void
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

	public function testCleanTmpDir(): void
	{
		$dir = static::TMP . '/site/cache/.uploads';
		F::write($a = $dir . '/abcd-a.md', '');
		F::write($b = $dir . '/efgh-b.md', '');
		touch($b, time() - 86400 - 1);

		$this->assertDirectoryExists($dir);
		$this->assertFileExists($a);
		$this->assertFileExists($b);

		Upload::cleanTmpDir();

		$this->assertDirectoryExists($dir);
		$this->assertFileExists($a);
		$this->assertFileDoesNotExist($b);

		touch($a, time() - 86400 - 1);

		Upload::cleanTmpDir();

		$this->assertDirectoryDoesNotExist($dir);
		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);
	}

	public function testError(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No file was uploaded');
		Upload::error(UPLOAD_ERR_NO_FILE);
	}

	public function testErrorUnknown(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file could not be uploaded');
		Upload::error(999);
	}

	public function testFilename(): void
	{
		$this->assertSame('foo.md', Upload::filename(['name' => 'foo.md']));
		$this->assertSame('foo.jpg', Upload::filename([
			'tmp_name' => 'foo.jpg',
			'name'     => 'foo.tmp'
		]));
	}

	public function testProcessSingle(): void
	{
		$upload = $this->upload([
			'requestMethod' => 'POST',
			'requestData' => [
				'files' => [
					[
						'name'     => 'test.txt',
						'tmp_name' => static::TMP . '/abc',
						'size'     => 123,
						'error'    => 0
					]
				]
			]
		], true, true);

		$phpunit = $this;
		$uploads = [];
		$data = $upload->process(function ($source, $filename) use ($phpunit, &$uploads) {
			// can't test souce path with dynamic uniqid
			// $phpunit->assertSame('uniqid.test.txt', $source);
			$phpunit->assertSame('test.txt', $filename);

			return $uploads = [
				'filename' => $filename
			];
		});

		$this->assertSame([
			'status' => 'ok',
			'data' => $uploads
		], $data);
	}

	public function testProcessMultiple(): void
	{
		$upload = $this->upload([
			'requestMethod' => 'POST',
			'requestData' => [
				'files' => [
					[
						'name'     => 'foo.txt',
						'tmp_name' => static::TMP . '/foo',
						'size'     => 123,
						'error'    => 0
					],
					[
						'name'     => 'bar.txt',
						'tmp_name' => static::TMP . '/bar',
						'size'     => 123,
						'error'    => 0
					],
					[
						'name'     => 'invalid.txt',
					]
				]
			]
		], false, true);

		$data = $upload->process(function ($source, $filename) {
			return [
				'filename' => $filename
			];
		});

		$this->assertSame([
			'status' => 'ok',
			'data' => [
				'foo.txt' => ['filename' => 'foo.txt'],
				'bar.txt' => ['filename' => 'bar.txt'],
			]
		], $data);
	}

	public function testProcessError(): void
	{
		$upload = $this->upload([
			'requestMethod' => 'POST',
			'requestData' => [
				'files' => [
					[
						'name'     => 'test.txt',
						'tmp_name' => static::TMP . '/abc',
						'size'     => 123,
						'error'    => UPLOAD_ERR_PARTIAL
					]
				]
			]
		], true, true);

		// move_uploaded_file error
		$data = $upload->process(function ($source) {
		});

		$this->assertSame([
			'status'  => 'error',
			'message' => 'The uploaded file was only partially uploaded'
		], $data);
	}

	public function testProcessException(): void
	{
		$upload = $this->upload([
			'requestMethod' => 'POST',
			'requestData' => [
				'files' => [
					[
						'name'     => 'test.txt',
						'tmp_name' => $tmp = static::TMP . '/abc',
						'size'     => 123,
						'error'    => 0
					]
				]
			]
		]);

		$this->assertFalse(F::exists($tmp));
		touch($tmp);
		$this->assertTrue(F::exists($tmp));

		// move_uploaded_file error
		$data = $upload->process(function ($source) {
			// empty closure
		});

		$this->assertSame([
			'status'  => 'error',
			'message' => 'The uploaded file could not be moved'
		], $data);
		$this->assertFalse(F::exists($tmp));
	}

	public function testProcessWithChunk(): void
	{
		$source = static::TMP . '/test.md';
		F::write($source, 'abcdef');

		$upload = $this->upload([
			'requestMethod' => 'POST',
			'requestData' => [
				'headers' => [
					'Upload-Length' => 3000,
					'Upload-Offset' => 0,
					'Upload-Id'     => 'abcd'
				],
				'files' => [
					[
						'name'     => 'test.md',
						'tmp_name' => $source,
						'size'     => F::size($source),
						'error'    => 0
					]
				]
			]
		], false, true);

		$dir = static::TMP . '/site/cache/.uploads';
		$this->assertSame([
			'status' => 'ok',
			'data'   => null
		], $upload->process(function () {
		}));
		$this->assertFileDoesNotExist($dir . '/test.md');
		$this->assertFileExists($dir . '/abcd-test.md');
	}

	public function testProcessChunkFirstChunkFullLength(): void
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

	public function testProcessChunkFirstChunkPartialLength(): void
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

	public function testProcessChunkIdRemoveUnallowedCharacters(): void
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

	public function testProcessChunkFilenameNoDirectoryTraversal(): void
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

	public function testProcessChunkSuccesfulAll(): void
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

	public function testResponse(): void
	{
		// nothing
		$response = Upload::response([], []);
		$this->assertSame('ok', $response['status']);
		$this->assertNull($response['data']);

		// 1 upload
		$response = Upload::response(['foo'], []);
		$this->assertSame('ok', $response['status']);
		$this->assertSame('foo', $response['data']);

		// 2 uploads
		$response = Upload::response($expected = ['foo', 'bar'], []);
		$this->assertSame('ok', $response['status']);
		$this->assertSame($expected, $response['data']);

		// error without uploads
		$response = Upload::response([], ['err']);
		$this->assertSame('error', $response['status']);
		$this->assertSame('err', $response['message']);

		// error with 1 upload
		$response = Upload::response(['foo'], $expected = ['quz']);
		$this->assertSame('error', $response['status']);
		$this->assertSame($expected, $response['errors']);

		// error with 2 uploads
		$response = Upload::response(['foo', 'bar'], $expected = ['quz']);
		$this->assertSame('error', $response['status']);
		$this->assertSame($expected, $response['errors']);
	}


	public function testValidateChunkDuplicate(): void
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

	public function testValidateChunkSubsequentInvalidOffset(): void
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

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Chunk offset 1500 does not match the existing tmp upload file size of 6');
		$upload->processChunk($source, basename($source));
	}

	public function testValidateChunkSubsequentNoFirst(): void
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

	public function testValidateChunkInvalidExtension(): void
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

	public function testValidateChunkTooLargeTotal(): void
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

	public function testValidateChunkTooLargeCurrentChunk(): void
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

	public function testValidateFilesEmpty(): void
	{
		ini_set('upload_max_filesize', '10M');
		ini_set('post_max_size', '20M');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No files were uploaded');

		$upload = $this->upload();
		$upload->process(function () {
		});
	}
}
