<?php

namespace Kirby\Sane;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Sane\Sane
 */
class SaneTest extends TestCase
{
    protected $type = 'sane';

    /**
     * @covers ::handler
     */
    public function testCustomAlias()
    {
        Sane::$aliases['scalable'] = 'svg';
        $this->assertInstanceOf(Svg::class, Sane::handler('scalable'));
    }

    /**
     * @covers ::handler
     */
    public function testCustomHandler()
    {
        Sane::$handlers['test'] = CustomHandler::class;
        $this->assertInstanceOf(CustomHandler::class, Sane::handler('test'));
    }

    /**
     * @covers ::handler
     */
    public function testDefaultHandlers()
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

    /**
     * @covers ::handler
     */
    public function testDefaultAliases()
    {
        $this->assertInstanceOf(Html::class, Sane::handler('text/html'));
        $this->assertInstanceOf(Svg::class, Sane::handler('image/svg+xml'));
        $this->assertInstanceOf(Xml::class, Sane::handler('application/xml'));
        $this->assertInstanceOf(Xml::class, Sane::handler('text/xml'));
    }

    /**
     * @covers ::handler
     */
    public function testMissingHandler()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "foo"');

        Sane::handler('foo');
    }

    /**
     * @covers ::handler
     */
    public function testMissingHandlerLazy()
    {
        $this->assertNull(Sane::handler('foo', true));
    }

    /**
     * @covers ::sanitize
     */
    public function testSanitize()
    {
        $this->assertSame('<svg><path d="123"/></svg>', Sane::sanitize('<svg><path d="123" onclick="alert(1)"></path></svg>', 'svg'));
    }

    /**
     * @covers ::sanitizeFile
     * @covers ::handlersForFile
     */
    public function testSanitizeFile()
    {
        $expected = $this->fixture('doctype-valid.svg');
        $tmp      = $this->fixture('doctype-valid.svg', true);
        $this->assertNull(Sane::sanitizeFile($tmp));
        $this->assertFileEquals($expected, $tmp);

        $expected = $this->fixture('external-source-1.sanitized.svg');
        $tmp      = $this->fixture('external-source-1.svg', true);
        $this->assertNull(Sane::sanitizeFile($tmp));
        $this->assertFileEquals($expected, $tmp);
    }

    /**
     * @covers ::sanitizeFile
     */
    public function testSanitizeFileExplicitHandler()
    {
        $expected = $this->fixture('doctype-valid.svg');
        $tmp      = $this->fixture('doctype-valid.svg', true);
        $this->assertNull(Sane::sanitizeFile($tmp, 'svg'));
        $this->assertFileEquals($expected, $tmp);

        $expected = $this->fixture('external-source-1.sanitized.svg');
        $tmp      = $this->fixture('external-source-1.svg', true);
        $this->assertNull(Sane::sanitizeFile($tmp, 'svg'));
        $this->assertFileEquals($expected, $tmp);
    }

    /**
     * @covers ::sanitizeFile
     * @covers ::handlersForFile
     */
    public function testSanitizeFileLazyHandler()
    {
        $this->assertNull(Sane::sanitizeFile($this->fixture('unknown.xyz'), true));
    }

    /**
     * @covers ::sanitizeFile
     * @covers ::handlersForFile
     */
    public function testSanitizeFileMultipleHandlers()
    {
        $fixture = $this->fixture('script-2.xml', true);

        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionMessage('Cannot sanitize file as more than one handler applies: Kirby\Sane\Xml, Kirby\Sane\Svg');

        Sane::sanitizeFile($fixture);
    }

    /**
     * @covers ::sanitizeFile
     */
    public function testSanitizeFileMultipleHandlersExplicit()
    {
        $expected = $this->fixture('script-2.sanitized.xml');
        $tmp      = $this->fixture('script-2.xml', true);

        $this->assertNull(Sane::sanitizeFile($tmp, 'xml'));
        $this->assertFileEquals($expected, $tmp);
    }

    /**
     * @covers ::validate
     */
    public function testValidate()
    {
        $this->assertNull(Sane::validate('<svg></svg>', 'svg'));
    }

    /**
     * @covers ::validate
     */
    public function testValidateError()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The file is not a SVG (got <html>)');

        Sane::validate('<html></html>', 'svg');
    }

    /**
     * @covers ::validate
     */
    public function testValidateMissingHandler()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "foo"');

        Sane::validate('foo', 'foo');
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFile()
    {
        $file = $this->fixture('doctype-valid.svg');

        $this->assertNull(Sane::validateFile($file));
        $this->assertNull(Sane::validateFile($file, 'svg'));
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileError()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "style"');

        Sane::validateFile($this->fixture('external-source-1.svg'), 'svg');
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMime1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "script" element (line 2) is not allowed');

        Sane::validateFile($this->fixture('script-1.xml'));
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMime2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "http://www.w3.org/2000/svg" is not allowed (around line 1)');

        Sane::validateFile($this->fixture('script-2.xml'));
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMime3()
    {
        $this->assertNull(Sane::validateFile($this->fixture('compressed.svgz'), true));
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMime4()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');

        Sane::validateFile($this->fixture('doctype-entity-attack.svgz'), true);
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMissing()
    {
        $file = $this->fixture('does-not-exist.svg');

        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('The file "' . $file . '" does not exist');

        Sane::validateFile($file);
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMissingHandler1()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "foo"');

        Sane::validateFile($this->fixture('doctype-valid.svg'), 'foo');
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMissingHandler2()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "xyz"');

        Sane::validateFile($this->fixture('unknown.xyz'));
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMissingHandler3()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "xyz"');

        Sane::validateFile($this->fixture('unknown.xyz'), false);
    }

    /**
     * @covers ::validateFile
     * @covers ::handlersForFile
     */
    public function testValidateFileMissingHandler4()
    {
        $this->assertNull(Sane::validateFile($this->fixture('unknown.xyz'), true));
    }
}
