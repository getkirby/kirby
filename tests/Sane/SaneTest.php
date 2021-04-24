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
        $this->expectExceptionMessage('The URL is not allowed in attribute: style');

        Sane::validateFile($this->fixture('external-source-1.svg'), 'svg');
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMime1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "script" element (line 3) is not allowed in SVGs');

        Sane::validateFile($this->fixture('script-1.xml'));
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMime2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace is not allowed in XML files (around line 2)');

        Sane::validateFile($this->fixture('script-2.xml'));
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMime3()
    {
        $this->assertNull(Sane::validateFile($this->fixture('compressed.svgz'), true));
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMime4()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');

        Sane::validateFile($this->fixture('doctype-entity-attack.svgz'), true);
    }

    /**
     * @covers ::validateFile
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
     */
    public function testValidateFileMissingHandler2()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "xyz"');

        Sane::validateFile($this->fixture('unknown.xyz'));
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMissingHandler3()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "xyz"');

        Sane::validateFile($this->fixture('unknown.xyz'), false);
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMissingHandler4()
    {
        $this->assertNull(Sane::validateFile($this->fixture('unknown.xyz'), true));
    }
}
