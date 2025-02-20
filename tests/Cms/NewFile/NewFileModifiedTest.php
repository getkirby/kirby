<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileModifiedTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileModified';

	public function testModified(): void
	{
		// create a file
		F::write($file = static::TMP . '/content/test/test.js', 'test');
		$modified = filemtime($file);

		$page = new Page([ 'slug' => 'test']);
		$file = new File([
			'filename' => 'test.js',
			'parent'   => $page
		]);


		$this->assertSame($modified, $file->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $file->modified($format));

		// custom date handler
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $file->modified($format, 'strftime'));
	}

	public function testModifiedContent(): void
	{
		// create a file
		F::write($file = static::TMP . '/content/test/test.js', 'test');
		touch($file, $modifiedFile = \time() + 2);

		F::write($content = static::TMP . '/content/test/test.js.txt', 'test');
		touch($file, $modifiedContent = \time() + 5);

		$page = new Page([ 'slug' => 'test']);
		$file = new File([
			'filename' => 'test.js',
			'parent'   => $page
		]);

		$this->assertNotEquals($modifiedFile, $file->modified());
		$this->assertSame($modifiedContent, $file->modified());
	}

	public function testModifiedSpecifyingLanguage(): void
	{
		$this->setUpMultiLanguage();

		// create a file
		F::write(static::TMP . '/test.js', 'test');

		// create the english content
		F::write($file = static::TMP . '/content/test/test.js.en.txt', 'test');
		touch($file, $modifiedEnContent = \time() + 2);

		// create the german content
		F::write($file = static::TMP . '/content/test/test.js.de.txt', 'test');
		touch($file, $modifiedDeContent = \time() + 5);

		$page = new Page([ 'slug' => 'test']);
		$file = new File([
			'filename' => 'test.js',
			'parent'   => $page
		]);

		$this->assertSame($modifiedEnContent, $file->modified(null, null, 'en'));
		$this->assertSame($modifiedDeContent, $file->modified(null, null, 'de'));
	}
}
