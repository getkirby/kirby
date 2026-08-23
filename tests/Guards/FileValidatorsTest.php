<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as Upload;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileValidators::class)]
class FileValidatorsTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/../Cms/File/fixtures/files';
	public const TMP      = KIRBY_TMP_DIR . '/Guards.FileValidators';

	public function testDoesNotExist(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->assertNull($validators->validateDoesNotExist());
	}

	public function testDoesNotExistWithExistingFile(): void
	{
		$validators = $this->validators($this->fileOnDisk('test.jpg'));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.file.duplicate');

		$validators->validateDoesNotExist();
	}

	public function testDuplicate(): void
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$validators = $this->validators($page->file('a.jpg'));

		$this->assertNull($validators->validateDuplicate('a'));
	}

	public function testDuplicateWithExistingFile(): void
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			]
		]);

		$validators = $this->validators($page->file('a.jpg'));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.file.duplicate');

		$validators->validateDuplicate('b');
	}

	public function testEnsureChangeTemplate(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.changeTemplate.invalid');

		$validators->ensure('changeTemplate', 'does-not-exist');
	}

	public function testEnsureReplace(): void
	{
		$validators = $this->validators($this->fileOnDisk('test.jpg'));
		$upload     = new Upload(static::FIXTURES . '/doc.pdf');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.mime.differs');

		$validators->ensure('replace', $upload);
	}

	public function testExtension(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->assertNull($validators->validateExtension('jpg'));
	}

	public function testExtensionWithMissingExtension(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.extension.missing');

		$validators->validateExtension('');
	}

	public function testExtensionWithForbiddenPhpExtension(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.forbidden');

		$validators->validateExtension('php');
	}

	public function testExtensionWithForbiddenHtmlExtension(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.forbidden');

		$validators->validateExtension('html');
	}

	public function testExtensionWithForbiddenExtension(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.extension.forbidden');

		$validators->validateExtension('exe');
	}

	public function testFile(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->assertNull($validators->validateFile('image/jpeg'));
	}

	public function testFileWithDetectedMime(): void
	{
		$validators = $this->validators($this->fileOnDisk('test.jpg'));

		$this->assertNull($validators->validateFile());
	}

	public function testFileWithSkippedMime(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->assertNull($validators->validateFile(false));
	}

	public function testFileWithForbiddenMime(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.mime.forbidden');

		$validators->validateFile('text/html');
	}

	public function testFilename(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->assertNull($validators->validateFilename('test.jpg'));
	}

	public function testFilenameWithMissingFilename(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.name.missing');

		$validators->validateFilename('');
	}

	public function testFilenameWithApacheConfig(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.forbidden');

		$validators->validateFilename('.htaccess');
	}

	public function testFilenameWithInvisibleFile(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.forbidden');

		$validators->validateFilename('.gitignore');
	}

	public function testMime(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->assertNull($validators->validateMime('image/jpeg'));
	}

	public function testMimeWithMissingMime(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.mime.missing');

		$validators->validateMime('');
	}

	public function testMimeWithForbiddenPhpMime(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.forbidden');

		$validators->validateMime('application/php');
	}

	public function testMimeWithForbiddenMime(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.mime.forbidden');

		$validators->validateMime('text/html');
	}

	public function testName(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->assertNull($validators->validateName('test'));
	}

	public function testNameWithEmptyName(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.changeName.empty');

		$validators->validateName('');
	}

	public function testTemplate(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						'files' => [
							'type'     => 'files',
							'template' => 'a'
						]
					]
				],
				'files/a' => ['title' => 'A'],
				'files/b' => ['title' => 'B']
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$validators = $this->validators($file);

		$this->assertNull($validators->validateTemplate('a'));
	}

	public function testTemplateWithInvalidTemplate(): void
	{
		$validators = $this->validators($this->file('test.jpg'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.changeTemplate.invalid');

		$validators->validateTemplate('does-not-exist');
	}

	public function testUpload(): void
	{
		$validators = $this->validators($this->fileOnDisk('test.jpg'));
		$upload     = new Upload(static::FIXTURES . '/test.jpg');

		$this->assertNull($validators->validateUpload($upload));
	}

	public function testUploadWithHarmfulContents(): void
	{
		$validators = $this->validators($this->fileOnDisk('test.svg'));
		$upload     = new Upload(static::FIXTURES . '/test.svg');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2)');

		$validators->validateUpload($upload);
	}

	public function testUploadWithUnacceptedMime(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/default' => [
					'accept' => ['mime' => 'image/jpeg']
				]
			]
		]);

		$validators = $this->validators($this->fileOnDisk('test.jpg'));
		$upload     = new Upload(static::FIXTURES . '/doc.pdf');

		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.mime.invalid');

		$validators->validateUpload($upload);
	}

	public function testUploadWithSameType(): void
	{
		$validators = $this->validators($this->fileOnDisk('test.jpg'));
		$upload     = new Upload(static::FIXTURES . '/cat.jpg');

		$this->assertNull($validators->validateUploadWithSameType($upload));
	}

	public function testUploadWithSameTypeWithDifferentType(): void
	{
		$validators = $this->validators($this->fileOnDisk('test.jpg'));
		$upload     = new Upload(static::FIXTURES . '/doc.pdf');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.mime.differs');

		$validators->validateUploadWithSameType($upload);
	}

	protected function file(string $filename): File
	{
		return new File([
			'filename' => $filename,
			'parent'   => new Page(['slug' => 'test'])
		]);
	}

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

		F::copy(
			source: static::FIXTURES . '/' . $filename,
			target: $page->root() . '/' . $filename
		);

		return new File([
			'filename' => $filename,
			'parent'   => $page
		]);
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}

	protected function validators(File $file): FileValidators
	{
		return new FileValidators(
			model: $file,
			user: $this->user()
		);
	}
}
