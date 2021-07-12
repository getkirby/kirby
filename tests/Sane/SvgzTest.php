<?php

namespace Kirby\Sane;

/**
 * @covers \Kirby\Sane\Svgz
 */
class SvgzTest extends TestCase
{
    protected $type = 'svgz';

    /**
     * @dataProvider allowedProvider
     */
    public function testAllowed(string $file)
    {
        $this->assertNull(Svgz::validateFile($this->fixture($file)));
    }

    public function allowedProvider()
    {
        return $this->fixtureList('allowed', 'svgz');
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $file)
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Could not uncompressed gzip data');

        Svgz::validateFile($this->fixture($file));
    }

    public function invalidProvider()
    {
        return $this->fixtureList('invalid', 'svgz');
    }

    public function testValidateDoctypeInternalSubset()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');

        Svgz::validateFile($this->fixture('disallowed/doctype-entity-attack.svgz'));
    }
}
