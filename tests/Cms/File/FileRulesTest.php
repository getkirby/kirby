<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FileRules::class)]
class FileRulesTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.FileRules';

	public function testChangeName(): void
	{
		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$this->expectNotToPerformAssertions();

		$file = $page->file('a.jpg');
		FileRules::changeName($file, 'c');
	}

	public function testChangeNameWithEmptyInput(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The name must not be empty');

		FileRules::changeName($file, '');
	}

	public function testChangeNameWithoutPermissions(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('changeName')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);
		$file->method('filename')->willReturn('test.jpg');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the name of "test.jpg"');

		FileRules::changeName($file, 'test');
	}

	public function testChangeSort(): void
	{
		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$this->expectNotToPerformAssertions();

		$file = $page->file('a.jpg');
		FileRules::changeSort($file, 1);
	}

	public function testChangeSortWithoutPermissions(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('sort')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);
		$file->method('filename')->willReturn('test.jpg');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the sorting of "test.jpg"');

		FileRules::changeSort($file, 1);
	}

	public function testChangeToSameNameWithDifferentException(): void
	{
		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.png']
			]
		]);

		$this->expectNotToPerformAssertions();

		$file = $page->file('a.jpg');
		FileRules::changeName($file, 'b');
	}

	public function testChangeNameToExistingFile(): void
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

	public function testChangeTemplate(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/foo' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'b'
						]
					]
				],
				'files/a' => ['title' => 'a'],
				'files/b' => ['title' => 'b'],
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'foo',
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => ['template' => 'a']
							]
						]
					]
				]
			],
		]);

		$app->impersonate('kirby');

		$this->expectNotToPerformAssertions();

		$file = $app->page('test')->file('test.jpg');
		FileRules::changeTemplate($file, 'b');
	}

	public function testChangeTemplateWithoutPermissions(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('changeTemplate')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('id')->willReturn('test');
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the template for the file "test"');

		FileRules::changeTemplate($file, 'test');
	}

	public function testChangeTemplateTooFewTemplates(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('changeTemplate')->willReturn(true);

		$file = $this->createMock(File::class);
		$file->method('blueprints')->willReturn([[]]);
		$file->method('id')->willReturn('test');
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The template for the file "test" cannot be changed');

		FileRules::changeTemplate($file, 'c');
	}

	public function testChangeTemplateWithInvalidTemplateName(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('changeTemplate')->willReturn(true);

		$file = $this->createMock(File::class);
		$file->method('blueprints')->willReturn([
			['name' => 'a'], ['name' => 'b']
		]);
		$file->method('id')->willReturn('test');
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The template for the file "test" cannot be changed');

		FileRules::changeTemplate($file, 'c');
	}

	public function testCreateExistingFile(): void
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

	public function testCreateSameFile(): void
	{
		$testImage = static::FIXTURES . '/test.jpg';

		$this->app = $this->app->clone([
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

		$page = $this->app->page('test');

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

		$this->expectNotToPerformAssertions();

		$upload = new BaseFile($testImage);
		FileRules::create($newFile, $upload);
	}

	public function testCreateSameFileWithDifferentTemplate(): void
	{
		$testImage = static::FIXTURES . '/test.jpg';

		$this->app = $this->app->clone([
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

		$page = $this->app->page('test');

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

	public function testCreateDifferentFileWithSameFilename(): void
	{
		$testImage = static::FIXTURES . '/test.jpg';

		$this->app = $this->app->clone([
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

		$page = $this->app->page('test');

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

		$upload = new BaseFile(static::FIXTURES . '/cat.jpg');
		FileRules::create($newFile, $upload);
	}

	public function testCreateHarmfulContents(): void
	{
		$blueprint = $this->createMock(FileBlueprint::class);

		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('create')->willReturn(true);

		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['permissions', 'blueprint', 'filename', '__call'])
			->getMock();
		$file->method('blueprint')->willReturn($blueprint);
		$file->method('permissions')->willReturn($permissions);
		$file->method('filename')->willReturn('test.svg');
		$file->method('__call')->with('extension')->willReturn('svg');

		$upload = new BaseFile(static::FIXTURES . '/test.svg');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2)');

		FileRules::create($file, $upload);
	}

	public function testCreateWithoutPermissions(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('create')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);
		$file->method('filename')->willReturn('test.jpg');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be created');

		$upload = $this->createMock(BaseFile::class);

		FileRules::create($file, $upload);
	}

	public function testDeleteWithoutPermissions(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('delete')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be deleted');

		FileRules::delete($file);
	}

	public function testReplaceWithoutPermissions(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('replace')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be replaced');

		$upload = $this->createMock(BaseFile::class);

		FileRules::replace($file, $upload);
	}

	public function testReplaceInvalidMimeExtension(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('replace')->willReturn(true);

		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['permissions', '__call'])
			->getMock();
		$file->method('permissions')->willReturn($permissions);
		$file->method('__call')->willReturnCallback(fn ($method, $args = []) => match ($method) {
			'mime'      => 'image/jpeg',
			'extension' => 'jpg'
		});

		$upload = $this->createMock(BaseFile::class);
		$upload->method('mime')->willReturn('image/png');
		$upload->method('extension')->willReturn('png');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The uploaded file must be of the same mime type "image/jpeg"');

		FileRules::replace($file, $upload);
	}

	public function testReplaceHarmfulContents(): void
	{
		$blueprint = $this->createMock(FileBlueprint::class);

		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('replace')->willReturn(true);

		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['__call', 'permissions', 'blueprint', 'filename'])
			->getMock();
		$file->method('blueprint')->willReturn($blueprint);
		$file->method('filename')->willReturn('test.svg');
		$file->method('permissions')->willReturn($permissions);
		$file->method('__call')->with('mime')->willReturnCallback(fn ($method, $args = []) => match ($method) {
			'extension' => 'svg',
			'mime'      => 'image/svg+xml'
		});

		$upload = new BaseFile(static::FIXTURES . '/test.svg');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2)');

		FileRules::replace($file, $upload);
	}

	public function testUpdateWithoutPermissions(): void
	{
		$permissions = $this->createMock(FilePermissions::class);
		$permissions->method('can')->with('update')->willReturn(false);

		$file = $this->createMock(File::class);
		$file->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be updated');

		FileRules::update($file, []);
	}

	public static function extensionProvider(): array
	{
		return [
			['jpg', true],
			['png', true],
			['', false, 'The extensions for "test" is missing'],
			['php', false, 'You are not allowed to upload PHP files'],
			['phar', false, 'You are not allowed to upload PHP files'],
			['pht', false, 'You are not allowed to upload PHP files'],
			['phtml', false, 'You are not allowed to upload PHP files'],
			['php4', false, 'You are not allowed to upload PHP files'],
			['1phar2', false, 'You are not allowed to upload PHP files'],
			['pht5', false, 'You are not allowed to upload PHP files'],
			['phtml5', false, 'You are not allowed to upload PHP files'],
			['htm', false, 'You are not allowed to upload HTML files'],
			['html', false, 'You are not allowed to upload HTML files'],
			['dhtml', false, 'You are not allowed to upload HTML files'],
			['exe', false, 'The extension "exe" is not allowed'],
			['txt', false, 'The extension "txt" is not allowed'],
		];
	}

	#[DataProvider('extensionProvider')]
	public function testValidExtension(
		string $extension,
		bool $expected,
		string|null $message = null
	): void {
		$file = $this->createMock(File::class);
		$file->method('filename')->willReturn('test');

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		} else {
			$this->expectNotToPerformAssertions();
		}

		FileRules::validExtension($file, $extension);
	}

	public static function fileProvider(): array
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
			['test.pht', 'pht', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.phtml', 'phtml', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.phar', 'phar', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.exe', 'exe', 'text/plain', false, 'The extension "exe" is not allowed'],
			['test.txt', 'txt', 'text/plain', false, 'The extension "txt" is not allowed'],
			['test.php4', 'php4', 'text/plain', false, 'You are not allowed to upload PHP files'],
			['test.pht5', 'pht5', 'text/plain', false, 'You are not allowed to upload PHP files'],
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

	#[DataProvider('fileProvider')]
	public function testValidFile(
		string $filename,
		string $extension,
		string $mime,
		bool $expected,
		string|null $message = null
	): void {
		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['filename', '__call'])
			->getMock();
		$file->method('filename')->willReturn($filename);
		$file->method('__call')
			->willReturnCallback(fn ($method, $args = []) => match ($method) {
				'extension' => $extension,
				'mime'      => $mime
			});

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		} else {
			$this->expectNotToPerformAssertions();
		}

		FileRules::validFile($file);
	}

	public function testValidFileSkipMime(): void
	{
		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['filename', '__call'])
			->getMock();
		$file->method('filename')->willReturn('test.jpg');
		$file->method('__call')->willReturnCallback(fn ($method, $args = []) => match ($method) {
			'extension' => 'jpg',
			'mime'      => 'text/html'
		});

		FileRules::validFile($file, false);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The media type "text/html" is not allowed');
		FileRules::validFile($file);
	}

	public static function filenameProvider(): array
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

	#[DataProvider('filenameProvider')]
	public function testValidFilename(
		string $filename,
		bool $expected,
		string|null $message = null
	): void {
		$file = $this->createMock(File::class);
		$file->method('filename')->willReturn($filename);

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		} else {
			$this->expectNotToPerformAssertions();
		}

		FileRules::validFilename($file, $filename);
	}

	public static function mimeProvider(): array
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

	#[DataProvider('mimeProvider')]
	public function testValidMime(
		string $mime,
		bool $expected,
		string|null $message = null
	): void {
		$file = $this->createMock(File::class);
		$file->method('filename')->willReturn('test');

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage($message);
		} else {
			$this->expectNotToPerformAssertions();
		}

		FileRules::validMime($file, $mime);
	}
}
