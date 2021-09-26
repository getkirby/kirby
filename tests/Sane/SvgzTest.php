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
        $fixture = $this->fixture($file);

        $this->assertNull(Svgz::validateFile($fixture));

        $input     = file_get_contents($fixture);
        $sanitized = Svgz::sanitize($input);
        $decoded   = gzdecode($sanitized);

        $this->assertIsString($decoded);
        $this->assertSame(gzdecode($input), gzdecode($sanitized));
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
        $this->expectExceptionMessage('Could not uncompress gzip data');

        Svgz::validateFile($this->fixture($file));
    }

    public function invalidProvider()
    {
        return $this->fixtureList('invalid', 'svgz');
    }

    public function testDisallowedDoctypeEntityAttack()
    {
        $fixture   = $this->fixture('disallowed/doctype-entity-attack.svgz');
        $sanitized = $this->fixture('sanitized/doctype-entity-attack.svg');

        $this->assertStringEqualsFile($sanitized, gzdecode(Svgz::sanitize(file_get_contents($fixture))));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');
        Svgz::validateFile($fixture);
    }
}
