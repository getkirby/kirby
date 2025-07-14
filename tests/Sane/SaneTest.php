<?php

namespace Kirby\Sane;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Sane::class)]
class SaneTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Sane.Sane';

	protected static string $type = 'sane';

	public function testCustomAlias(): void
	{
		Sane::$aliases['scalable'] = 'svg';
		$this->assertInstanceOf(Svg::class, Sane::handler('scalable'));
	}

	public function testCustomHandler(): void
	{
		Sane::$handlers['test'] = CustomHandler::class;
		$this->assertInstanceOf(CustomHandler::class, Sane::handler('test'));
	}

	public function testDefaultHandlers(): void
	{
		$this->assertInstanceOf(Html::class, Sane::handler('html'));
		$this->assertInstanceOf(Svg::class, Sane::handler('svg'));
		$this->assertInstanceOf(Svgz::class, Sane::handler('svgz'));
		$this->assertInstanceOf(Xml::class, Sane::handler('xml'));

		// different case
		$this->assertInstanceOf(Svg::class, Sane::handler('SvG'));
		$this->assertInstanceOf(Svg::class, Sane::handler('svG'));

		// lazy mode shouldn't make a difference
		$this->assertInstanceOf(Svg::class, Sane::handler('svg', true));
		$this->assertInstanceOf(Svg::class, Sane::handler('SvG', true));
		$this->assertInstanceOf(Svg::class, Sane::handler('svG', true));
	}

	public function testDefaultAliases(): void
	{
		$this->assertInstanceOf(Html::class, Sane::handler('text/html'));
		$this->assertInstanceOf(Svg::class, Sane::handler('image/svg+xml'));
		$this->assertInstanceOf(Xml::class, Sane::handler('application/xml'));
		$this->assertInstanceOf(Xml::class, Sane::handler('text/xml'));
	}

	public function testMissingHandler(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Missing handler for type: "foo"');

		Sane::handler('foo');
	}

	public function testMissingHandlerLazy(): void
	{
		$this->assertNull(Sane::handler('foo', true));
	}

	public function testSanitize(): void
	{
		$this->assertSame('<svg><path d="123"/></svg>', Sane::sanitize('<svg><path d="123" onclick="alert(1)"></path></svg>', 'svg'));

		$string = '<svg><a xlink:href="/another-folder">Very malicious</a></svg>';
		$this->assertSame($string, Sane::sanitize($string, 'svg')); // not external by default
		$this->assertSame('<svg><a>Very malicious</a></svg>', Sane::sanitize($string, 'svg', isExternal: true));
	}

	public function testSanitizeFile(): void
	{
		$expected = $this->fixture('doctype-valid.svg');
		$tmp      = $this->fixture('doctype-valid.svg', true);

		Sane::sanitizeFile($tmp);

		$this->assertFileEquals($expected, $tmp);

		$expected = $this->fixture('external-source-1.sanitized.svg');
		$tmp      = $this->fixture('external-source-1.svg', true);

		Sane::sanitizeFile($tmp);

		$this->assertFileEquals($expected, $tmp);

		$expected = $this->fixture('xlink-subfolder.sanitized.svg');
		$tmp      = $this->fixture('xlink-subfolder.svg', true);

		Sane::sanitizeFile($tmp);

		$this->assertFileEquals($expected, $tmp);
	}

	public function testSanitizeFileExplicitHandler(): void
	{
		$expected = $this->fixture('doctype-valid.svg');
		$tmp      = $this->fixture('doctype-valid.svg', true);

		Sane::sanitizeFile($tmp, 'svg');

		$this->assertFileEquals($expected, $tmp);

		$expected = $this->fixture('external-source-1.sanitized.svg');
		$tmp      = $this->fixture('external-source-1.svg', true);

		Sane::sanitizeFile($tmp, 'svg');

		$this->assertFileEquals($expected, $tmp);
	}

	public function testSanitizeFileLazyHandler(): void
	{
		$this->assertNull(
			Sane::sanitizeFile($this->fixture('unknown.xyz'), true)
		);
	}

	public function testSanitizeFileMultipleHandlers(): void
	{
		$fixture = $this->fixture('script-2.xml', true);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Cannot sanitize file as more than one handler applies: Kirby\Sane\Xml, Kirby\Sane\Svg');

		Sane::sanitizeFile($fixture);
	}

	public function testSanitizeFileMultipleHandlersExplicit(): void
	{
		$expected = $this->fixture('script-2.sanitized.xml');
		$tmp      = $this->fixture('script-2.xml', true);

		Sane::sanitizeFile($tmp, 'xml');
		$this->assertFileEquals($expected, $tmp);
	}

	public function testValidate(): void
	{
		$this->assertNull(Sane::validate('<svg></svg>', 'svg'));
	}

	public function testValidateError(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The file is not a SVG (got <html>)');

		Sane::validate('<html></html>', 'svg');
	}

	public function testValidateMissingHandler(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Missing handler for type: "foo"');

		Sane::validate('foo', 'foo');
	}

	public function testValidateFile(): void
	{
		$file = $this->fixture('doctype-valid.svg');

		$this->assertNull(Sane::validateFile($file));
		$this->assertNull(Sane::validateFile($file, 'svg'));
	}

	public function testValidateFileError(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "style"');

		Sane::validateFile($this->fixture('external-source-1.svg'), 'svg');
	}

	public function testValidateFileErrorExternalFile(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');

		Sane::validateFile($this->fixture('xlink-subfolder.svg'));
	}

	public function testValidateFileMime1(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "script" element (line 2) is not allowed');

		Sane::validateFile($this->fixture('script-1.xml'));
	}

	public function testValidateFileMime2(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The namespace "http://www.w3.org/2000/svg" is not allowed (around line 1)');

		Sane::validateFile($this->fixture('script-2.xml'));
	}

	public function testValidateFileMime3(): void
	{
		$this->assertNull(Sane::validateFile($this->fixture('compressed.svgz'), true));
	}

	public function testValidateFileMime4(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not define a subset');

		Sane::validateFile($this->fixture('doctype-entity-attack.svgz'), true);
	}

	public function testValidateFileMissing(): void
	{
		$file = $this->fixture('does-not-exist.svg');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist');

		Sane::validateFile($file);
	}

	public function testValidateFileMissingHandler1(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Missing handler for type: "foo"');

		Sane::validateFile($this->fixture('doctype-valid.svg'), 'foo');
	}

	public function testValidateFileMissingHandler2(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Missing handler for type: "xyz"');

		Sane::validateFile($this->fixture('unknown.xyz'));
	}

	public function testValidateFileMissingHandler3(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Missing handler for type: "xyz"');

		Sane::validateFile($this->fixture('unknown.xyz'), false);
	}

	public function testValidateFileMissingHandler4(): void
	{
		$this->assertNull(Sane::validateFile($this->fixture('unknown.xyz'), true));
	}
}
