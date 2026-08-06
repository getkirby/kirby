<?php

namespace Kirby\Cms;

use Kirby\Exception\AbilityException;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use PHPUnit\Framework\Attributes\CoversClass;

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
		$this->app->impersonate('nobody');

		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

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
		$this->app->impersonate('nobody');

		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

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
		$file = $this->fileWithMultipleTemplates();

		$this->expectNotToPerformAssertions();

		FileRules::changeTemplate($file, 'b');
	}

	public function testChangeTemplateWithoutPermissions(): void
	{
		$file = $this->fileWithMultipleTemplates();

		$this->app->impersonate('nobody');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the template for the file "test/test.jpg"');

		FileRules::changeTemplate($file, 'b');
	}

	public function testChangeTemplateTooFewTemplates(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(AbilityException::class);
		$this->expectExceptionMessage('The template for the file "test/test.jpg" cannot be changed');

		FileRules::changeTemplate($file, 'c');
	}

	public function testChangeTemplateWithInvalidTemplateName(): void
	{
		$file = $this->fileWithMultipleTemplates();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.changeTemplate.invalid');

		FileRules::changeTemplate($file, 'c');
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

		$this->app->impersonate('kirby');

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

		$this->app->impersonate('kirby');

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
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.svg', 'parent' => $parent]);
		$upload = new BaseFile(static::FIXTURES . '/test.svg');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2)');

		FileRules::create($file, $upload);
	}

	public function testCreateWithoutPermissions(): void
	{
		$this->app->impersonate('nobody');

		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);
		$upload = new BaseFile(static::FIXTURES . '/test.jpg');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be created');

		FileRules::create($file, $upload);
	}

	public function testDeleteWithoutPermissions(): void
	{
		$this->app->impersonate('nobody');

		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be deleted');

		FileRules::delete($file);
	}

	public function testReplaceWithoutPermissions(): void
	{
		$this->app->impersonate('nobody');

		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);
		$upload = new BaseFile(static::FIXTURES . '/test.jpg');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be replaced');

		FileRules::replace($file, $upload);
	}

	public function testReplaceInvalidMimeExtension(): void
	{
		$file   = $this->fileOnDisk('test.jpg');
		$upload = new BaseFile(static::FIXTURES . '/doc.pdf');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The uploaded file must be of the same mime type "image/jpeg"');

		FileRules::replace($file, $upload);
	}

	public function testReplaceHarmfulContents(): void
	{
		$file   = $this->fileOnDisk('test.svg');
		$upload = new BaseFile(static::FIXTURES . '/test.svg');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2)');

		FileRules::replace($file, $upload);
	}

	public function testUpdateWithoutPermissions(): void
	{
		$this->app->impersonate('nobody');

		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The file cannot be updated');

		FileRules::update($file, []);
	}

	public function testValidExtension(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectNotToPerformAssertions();

		FileRules::validExtension($file, 'jpg');
	}

	public function testValidExtensionWithForbiddenExtension(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The extension "exe" is not allowed');

		FileRules::validExtension($file, 'exe');
	}

	public function testValidFile(): void
	{
		$file = $this->fileOnDisk('test.jpg');

		$this->expectNotToPerformAssertions();

		FileRules::validFile($file);
	}

	public function testValidFileWithForbiddenFilename(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => '.htaccess', 'parent' => $parent]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('You are not allowed to upload Apache config files');

		FileRules::validFile($file, false);
	}

	public function testValidFileSkipMime(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = $this->app->page('test');

		// a file with a valid filename and extension,
		// but with HTML contents and thus a forbidden MIME type
		F::write($page->root() . '/test.jpg', '<html><body>test</body></html>');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		FileRules::validFile($file, false);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The media type "text/html" is not allowed');

		FileRules::validFile($file);
	}

	public function testValidFilename(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectNotToPerformAssertions();

		FileRules::validFilename($file, 'test.jpg');
	}

	public function testValidFilenameWithInvisibleFile(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('You are not allowed to upload invisible files');

		FileRules::validFilename($file, '.gitignore');
	}

	public function testValidMime(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectNotToPerformAssertions();

		FileRules::validMime($file, 'image/jpeg');
	}

	public function testValidMimeWithForbiddenMime(): void
	{
		$parent = new Page(['slug' => 'test']);
		$file   = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The media type "text/html" is not allowed');

		FileRules::validMime($file, 'text/html');
	}

	/**
	 * Creates a file that really exists in the page root,
	 * so that the extension, filename and MIME type can be detected
	 */
	protected function fileOnDisk(string $filename): File
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = $this->app->page('test');

		F::copy(static::FIXTURES . '/' . $filename, $page->root() . '/' . $filename);

		return new File([
			'filename' => $filename,
			'parent'   => $page
		]);
	}

	/**
	 * Creates a file with more than one available template
	 */
	protected function fileWithMultipleTemplates(): File
	{
		$this->app = $this->app->clone([
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

		$this->app->impersonate('kirby');

		return $this->app->page('test')->file('test.jpg');
	}
}
