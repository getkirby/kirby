<?php

namespace Kirby\Http\Request;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Files::class)]
class FilesTest extends TestCase
{
	public function testMultipleUploads(): void
	{
		$upload = [
			'upload' => [
				'name'     => ['a.txt', 'b.txt'],
				'tmp_name' => ['/tmp/a', '/tmp/b'],
				'size'     => [123, 456],
				'error'    => [0, 0]
			]
		];

		$files = new Files($upload);

		$this->assertSame('a.txt', $files->get('upload')[0]['name']);
		$this->assertSame('/tmp/a', $files->get('upload')[0]['tmp_name']);
		$this->assertSame(123, $files->get('upload')[0]['size']);
		$this->assertSame(0, $files->get('upload')[0]['error']);

		$this->assertSame('b.txt', $files->get('upload')[1]['name']);
		$this->assertSame('/tmp/b', $files->get('upload')[1]['tmp_name']);
		$this->assertSame(456, $files->get('upload')[1]['size']);
		$this->assertSame(0, $files->get('upload')[1]['error']);
	}

	public function testData(): void
	{
		// default
		$files = new Files();
		$this->assertSame([], $files->data());

		// custom
		$upload = [
			'upload' => [
				'name'     => 'test.txt',
				'tmp_name' => '/tmp/abc',
				'size'     => 123,
				'error'    => 0
			]
		];

		$files = new Files($upload);
		$this->assertSame($upload, $files->data());
	}

	public function testGet(): void
	{
		// test with default data
		$files = new Files();
		$this->assertNull($files->get('upload'));

		// test with mock data
		$files = new Files([
			'upload' => [
				'name'     => 'test.txt',
				'tmp_name' => '/tmp/abc',
				'size'     => 123,
				'error'    => 0
			]
		]);

		$this->assertSame(123, $files->get('upload')['size']);
	}

	public function testToArrayAndDebuginfo(): void
	{
		$data  = [
			'upload' => [
				'name'     => 'test.txt',
				'tmp_name' => '/tmp/abc',
				'size'     => 123,
				'error'    => 0
			]
		];

		$files = new Files($data);
		$this->assertSame($data, $files->toArray());
		$this->assertSame($data, $files->__debugInfo());
	}

	public function testToJson(): void
	{
		$data  = [
			'upload' => [
				'name'     => 'test.txt',
				'tmp_name' => '/tmp/abc',
				'size'     => 123,
				'error'    => 0
			]
		];

		$files = new Files($data);
		$this->assertSame(json_encode($data), $files->toJson());
	}
}
