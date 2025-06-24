<?php

namespace Kirby\Filesystem;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Mime::class)]
class MimeTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/mime';

	public function testFixCss(): void
	{
		$this->assertSame('text/css', Mime::fix('something.css', 'text/x-asm', 'css'));
		$this->assertSame('text/css', Mime::fix('something.css', 'text/plain', 'css'));
	}

	public function testFixMjs(): void
	{
		$this->assertSame('text/javascript', Mime::fix('something.mjs', 'text/x-java', 'mjs'));
		$this->assertSame('text/javascript', Mime::fix('something.mjs', 'text/plain', 'mjs'));
	}

	public function testFixSvg(): void
	{
		$this->assertSame('image/svg+xml', Mime::fix('something.svg', 'image/svg', 'svg'));
		$this->assertSame('image/svg+xml', Mime::fix(static::FIXTURES . '/optimized.svg', 'text/html', 'svg'));
		$this->assertSame('image/svg+xml', Mime::fix(static::FIXTURES . '/unoptimized.svg', 'text/html', 'svg'));
	}

	public function testFromExtension(): void
	{
		$mime = Mime::fromExtension('jpg');
		$this->assertSame('image/jpeg', $mime);
	}

	public function testFromMimeContentType(): void
	{
		$mime = Mime::fromMimeContentType(__FILE__);
		$this->assertSame('text/x-php', $mime);
	}

	public function testFromSvg(): void
	{
		$mime = Mime::fromSvg(static::FIXTURES . '/optimized.svg');
		$this->assertSame('image/svg+xml', $mime);
	}

	public function testFromSvgNonExistingFile(): void
	{
		$mime = Mime::fromSvg(__DIR__ . '/imaginary.svg');
		$this->assertFalse($mime);
	}

	public function testIsAccepted(): void
	{
		$pattern = 'text/html,text/plain;q=0.8,application/*;q=0.7';

		$this->assertTrue(Mime::isAccepted('text/html', $pattern));
		$this->assertTrue(Mime::isAccepted('text/plain', $pattern));
		$this->assertTrue(Mime::isAccepted('application/json', $pattern));
		$this->assertTrue(Mime::isAccepted('application/yaml', $pattern));

		$this->assertFalse(Mime::isAccepted('text/xml', $pattern));
	}

	public function testMatches(): void
	{
		$this->assertTrue(Mime::matches('text/plain', 'text/plain'));
		$this->assertTrue(Mime::matches('text/plain', 'text/*'));
		$this->assertTrue(Mime::matches('text/xml', 'text/*'));
		$this->assertTrue(Mime::matches('text/plain', '*/plain'));
		$this->assertTrue(Mime::matches('application/plain', '*/plain'));
		$this->assertTrue(Mime::matches('text/plain', '*/*'));
		$this->assertTrue(Mime::matches('application/json', '*/*'));

		$this->assertFalse(Mime::matches('text/xml', 'text/plain'));
		$this->assertFalse(Mime::matches('application/json', 'text/*'));
		$this->assertFalse(Mime::matches('text/xml', '*/plain'));
	}

	public function testToExtension(): void
	{
		$extension = Mime::toExtension('image/jpeg');
		$this->assertSame('jpg', $extension);

		$extensions = Mime::toExtension('text/css');
		$this->assertSame('css', $extensions);
	}

	public function testToExtensions(): void
	{
		$extensions = Mime::toExtensions('image/jpeg');
		$this->assertSame(['jpg', 'jpeg', 'jpe'], $extensions);

		$extensions = Mime::toExtensions('text/css');
		$this->assertSame(['css'], $extensions);
	}

	public function testToExtensionsMatchWildcards(): void
	{
		// matchWildcards: false (default value)
		$extensions = Mime::toExtensions('image/*');
		$this->assertCount(0, $extensions);

		// matchWildcards: true
		$extensions = Mime::toExtensions('image/*', true);

		// we only check for a positive and negative subset
		// instead of a complete list to make sure the test
		// doesn't break when a new image type is added

		// should contain
		foreach (['jpg', 'jpeg', 'gif', 'png'] as $ext) {
			$this->assertContains($ext, $extensions);
		}

		// should not contain
		foreach (['js', 'pdf', 'zip', 'docx'] as $ext) {
			$this->assertNotContains($ext, $extensions);
		}
	}

	public function testTypeWithOptimizedSvg(): void
	{
		$mime = Mime::type(static::FIXTURES . '/optimized.svg');
		$this->assertSame('image/svg+xml', $mime);
	}

	public function testTypeWithUnoptimizedSvg(): void
	{
		$mime = Mime::type(static::FIXTURES . '/unoptimized.svg');
		$this->assertSame('image/svg+xml', $mime);
	}

	public function testTypeWithJson(): void
	{
		$mime = Mime::type(static::FIXTURES . '/something.json');
		$this->assertSame('application/json', $mime);
	}

	public function testTypes(): void
	{
		$this->assertSame(Mime::$types, Mime::types());
	}
}
