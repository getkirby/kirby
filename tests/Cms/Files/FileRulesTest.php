<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;

class FileRulesTest extends TestCase
{
	protected $app;
	protected $fixtures;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->fixtures = __DIR__ . '/fixtures/FileRulesTest'
			]
		]);

		$this->app->impersonate('kirby');
		Dir::make($this->fixtures);
	}

	public function tearDown(): void
	{
		Dir::remove($this->fixtures);
	}

	public function testChangeName()
	{
		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$file = $page->file('a.jpg');

		$this->assertTrue(FileRules::changeName($file, 'c'));
	}

	public function testChangeNameWithEmptyInput()
	{
		$parent = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The name must not be empty');

		FileRules::changeName($file, '');
	}

	public function testChangeNameWithoutPermissions()
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('changeName')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);
		$file->method('filename')->willReturn('test.jpg');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the name of "test.jpg"');

		FileRules::changeName($file, 'test');
	}

	public function testChangeSort()
	{
		$file = $this->createMock(File::class);
		$this->assertTrue(FileRules::changeSort($file, 1));
	}

	public function testChangeToSameNameWithDifferentException()
	{
		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.png']
			]
		]);

		$file = $page->file('a.jpg');

		$this->assertTrue(FileRules::changeName($file, 'b'));
	}

	public function testChangeNameToExistingFile()
	{
		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A file with the name "b.jpg" already exists');

		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$file = $page->file('a.jpg');
		FileRules::changeName($file, 'b');
	}

	public function testCreateExistingFile()
	{
		$file = $this->createMock(File::class);
		$file->method('filename')->willReturn('test.jpg');
		$file->method('exists')->willReturn(true);
		$page = $this->createMock(Page::class);
		$file->method('parent')->willReturn($page);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A file with the name "test.jpg" already exists');

		$upload = $this->createMock(BaseFile::class);

		FileRules::create($file, $upload);
	}

	public function testCreateSameFile()
	{
		$testImage =  __DIR__ . '/fixtures/files/test.jpg';

		$app = new App([
			'roots' => [
				'index' => $this->fixtures = __DIR__ . '/fixtures/FileRulesTest/createSameFile',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg', 'content' => ['template' => 'test']],
						]
					]
				]
			]
		]);

		$page = $app->page('test');

		// create real file with content and move into page root
		F::copy($testImage, $page->root() . '/test.jpg');
		F::write($page->root() . '/test.jpg.txt', 'Template: test');

		// create new file
		$newFile = new File([
			'filename' => 'test.jpg',
			'parent' => $page,
			'content' => [
				'template' => 'test'
			]
		]);

		$upload = new BaseFile($testImage);
		$create = FileRules::create($newFile, $upload);

		$this->assertTrue($create);
	}

	public function testCreateSameFileWithDifferentTemplate()
	{
		$testImage =  __DIR__ . '/fixtures/files/test.jpg';

		$app = new App([
			'roots' => [
				'index' => $this->fixtures = __DIR__ . '/fixtures/FileRulesTest/createSameFileWithDifferentTemplate',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg', 'content' => ['template' => 'test']],
						]
					]
				]
			]
		]);

		$page = $app->page('test');

		// create real file with content and move into page root
		F::copy($testImage, $page->root() . '/test.jpg');
		F::write($page->root() . '/test.jpg.txt', 'Template: test');

		$newFile = new File([
			'filename' => 'test.jpg',
			'parent' => $page,
			'content' => [
				'template' => 'cover'
			]
		]);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A file with the name "test.jpg" already exists');

		$upload = new BaseFile($testImage);
		FileRules::create($newFile, $upload);
	}

	public function testCreateDifferentFileWithSameFilename()
	{
		$testImage =  __DIR__ . '/fixtures/files/test.jpg';

		$app = new App([
			'roots' => [
				'index' => $this->fixtures = __DIR__ . '/fixtures/FileRulesTest/createDifferentFileWithSameFilename',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg', 'content' => ['template' => 'test']],
						]
					]
				]
			]
		]);

		$page = $app->page('test');

		// create real file with content and move into page root
		F::copy($testImage, $page->root() . '/test.jpg');
		F::write($page->root() . '/test.jpg.txt', 'Template: test');

		$newFile = new File([
			'filename' => 'test.jpg',
			'parent' => $page,
			'content' => [
				'template' => 'test'
			]
		]);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A file with the name "test.jpg" already exists');

		$upload = new BaseFile(__DIR__ . '/fixtures/files/cat.jpg');
		FileRules::create($newFile, $upload);
	}

	public function testCreateHarmfulContents()
	{
		$blueprint = $this->createMock(FileBlueprint::class);

		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('create')->willReturn(true);

		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['permissions', 'blueprint', 'filename'])
			->addMethods(['extension'])
			->getMock();
		$file->method('blueprint')->willReturn($blueprint);
		$file->method('extension')->willReturn('svg');
		$file->method('filename')->willReturn('test.svg');
		$file->method('permissions')->willReturn($permissions);

		$upload = new BaseFile(__DIR__ . '/fixtures/files/test.svg');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2)');

		FileRules::create($file, $upload);
	}

	public function testCreateWithoutPermissions()
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('create')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);
		$file->method('filename')->willReturn('test.jpg');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be created');

		$upload = $this->createMock(BaseFile::class);

		FileRules::create($file, $upload);
	}

	public function testDeleteWithoutPermissions()
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('delete')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be deleted');

		FileRules::delete($file);
	}

	public function testReplaceWithoutPermissions()
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('replace')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be replaced');

		$upload = $this->createMock(BaseFile::class);

		FileRules::replace($file, $upload);
	}

	public function testReplaceInvalidMimeExtension()
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('replace')->willReturn(true);

		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['permissions'])
			->addMethods(['mime', 'extension'])
			->getMock();
		$file->method('permissions')->willReturn($permissions);
		$file->method('mime')->willReturn('image/jpeg');
		$file->method('extension')->willReturn('jpg');

		$upload = $this->createMock(BaseFile::class);
		$upload->method('mime')->willReturn('image/png');
		$upload->method('extension')->willReturn('png');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The uploaded file must be of the same mime type "image/jpeg"');

		FileRules::replace($file, $upload);
	}

	public function testReplaceHarmfulContents()
	{
		$blueprint = $this->createMock(FileBlueprint::class);

		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('replace')->willReturn(true);

		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['__call', 'permissions', 'blueprint', 'filename'])
			->addMethods(['extension'])
			->getMock();
		$file->method('__call')->with('mime')->willReturn('image/svg+xml');
		$file->method('blueprint')->willReturn($blueprint);
		$file->method('extension')->willReturn('svg');
		$file->method('filename')->willReturn('test.svg');
		$file->method('permissions')->willReturn($permissions);

		$upload = new BaseFile(__DIR__ . '/fixtures/files/test.svg');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2)');

		FileRules::replace($file, $upload);
	}

	public function testUpdateWithoutPermissions()
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('__call')->with('update')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be updated');

		FileRules::update($file, []);
	}

	public function extensionProvider()
	{
		return [
			['jpg', true],
			['png', true],
			['', false, 'The extensions for "test" is missing'],
			['php', false, 'You are not allowed to upload PHP files'],
			['phar', false, 'You are not allowed to upload PHP files'],
			['phtml', false, 'You are not allowed to upload PHP files'],
			['php4', false, 'You are not allowed to upload PHP files'],
			['1phar2', false, 'You are not allowed to upload PHP files'],
			['phtml5', false, 'You are not allowed to upload PHP files'],
			['htm', false, 'You are not allowed to upload HTML files'],
			['html', false, 'You are not allowed to upload HTML files'],
			['dhtml', false, 'You are not allowed to upload HTML files'],
			['exe', false, 'The extension "exe" is not allowed'],
			['txt', false, 'The extension "txt" is not allowed'],
		];
	}

	/**
	 * @dataProvider extensionProvider
	 */
	public function testValidExtension($extension, $expected, $message = null)
	{
		$file = $this->createMock(File::class);
		$file->method('filename')->willReturn('test');

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		}

		$result = FileRules::validExtension($file, $extension);

		$this->assertTrue($result);
	}

	public function fileProvider()
	{
		return [
			// valid examples
			['test.jpg', 'jpg', 'image/jpeg', true],
			['abc.png', 'png', 'image/png', true],

			// extension
			['test', '', 'text/plain', false, 'The extensions for "test" is missing'],
			['test.htm', 'htm', 'text/plain', false, 'You are not allowed to upload HTML files'],
			['test.html', 'html', 'text/plain', false, 'You are not allowed to upload HTML files'],
			['test.php', 'php', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.phtml', 'phtml', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.phar', 'phar', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.exe', 'exe', 'text/plain', false, 'The extension "exe" is not allowed'],
			['test.txt', 'txt', 'text/plain', false, 'The extension "txt" is not allowed'],
			['test.php4', 'php4', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.phtml5', 'phtml5', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.1phar2', '1phar2', 'text/plain', false, 'You are not allowed to upload PHP files'],

			// mime
			['test', 'jpg', '', false, 'The media type for "test" cannot be detected'],
			['test.jpg', 'jpg', 'application/php', false, 'You are not allowed to upload PHP files'],
			['test.jpg', 'jpg', 'text/html', false, 'The media type "text/html" is not allowed'],
			['test.jpg', 'jpg', 'application/x-msdownload', false, 'The media type "application/x-msdownload" is not allowed'],

			// filename
			['', 'jpg', 'image/jpg', false, 'The filename must not be empty'],
			['.htaccess', 'htaccess', 'application/x-apache', false, 'You are not allowed to upload Apache config files'],
			['.htpasswd', 'htpasswd', 'application/x-apache', false, 'You are not allowed to upload Apache config files'],
			['.gitignore', 'gitignore', 'application/x-git', false, 'You are not allowed to upload invisible files'],

			// rule order
			['.test.jpg', 'jpg', 'application/php', false, 'You are not allowed to upload PHP files'],
			['.test.htm', 'htm', 'text/plain', false, 'You are not allowed to upload HTML files'],
			['.test.jpg', 'jpg', 'text/plain', false, 'You are not allowed to upload invisible files'],
		];
	}

	/**
	 * @dataProvider fileProvider
	 */
	public function testValidFile($filename, $extension, $mime, $expected, $message = null)
	{
		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['filename'])
			->addMethods(['mime', 'extension'])
			->getMock();
		$file->method('filename')->willReturn($filename);
		$file->method('extension')->willReturn($extension);
		$file->method('mime')->willReturn($mime);

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		}

		$result = FileRules::validFile($file);

		$this->assertTrue($result);
	}

	public function testValidFileSkipMime()
	{
		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['filename'])
			->addMethods(['mime', 'extension'])
			->getMock();
		$file->method('filename')->willReturn('test.jpg');
		$file->method('extension')->willReturn('jpg');
		$file->method('mime')->willReturn('text/html');

		$this->assertTrue(FileRules::validFile($file, false));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The media type "text/html" is not allowed');
		$this->assertTrue(FileRules::validFile($file));
	}

	public function filenameProvider()
	{
		return [
			['test.jpg', true],
			['abc.txt', true],
			['', false, 'The filename must not be empty'],
			['.htaccess', false, 'You are not allowed to upload Apache config files'],
			['.htpasswd', false, 'You are not allowed to upload Apache config files'],
			['.gitignore', false, 'You are not allowed to upload invisible files'],
		];
	}

	/**
	 * @dataProvider filenameProvider
	 */
	public function testValidFilename($filename, $expected, $message = null)
	{
		$file = $this->createMock(File::class);
		$file->method('filename')->willReturn($filename);

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		}

		$result = FileRules::validFilename($file, $filename);

		$this->assertTrue($result);
	}

	public function mimeProvider()
	{
		return [
			['image/jpeg', true],
			['image/png', true],
			['', false, 'The media type for "test" cannot be detected'],
			['application/php', false, 'You are not allowed to upload PHP files'],
			['text/html', false, 'The media type "text/html" is not allowed'],
			['application/x-msdownload', false, 'The media type "application/x-msdownload" is not allowed'],
		];
	}

	/**
	 * @dataProvider mimeProvider
	 */
	public function testValidMime($mime, $expected, $message = null)
	{
		$file = $this->createMock(File::class);
		$file->method('filename')->willReturn('test');

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		}

		$result = FileRules::validMime($file, $mime);

		$this->assertTrue($result);
	}
}
