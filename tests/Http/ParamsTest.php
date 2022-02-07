<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class ParamsTest extends TestCase
{
    public function testConstructWithArray()
    {
        $params = new Params([
            'a' => 'value-a',
            'b' => 'value-b'
        ]);

        $this->assertEquals('value-a', $params->a);
        $this->assertEquals('value-b', $params->b);
    }

    public function testConstructWithString()
    {
        $params = new Params('a:value-a/b:value-b');

        $this->assertEquals('value-a', $params->a);
        $this->assertEquals('value-b', $params->b);
    }

    public function testConstructWithEmptyValue()
    {
        $params = new Params('a:/b:');

        $this->assertEquals(null, $params->a);
        $this->assertEquals(null, $params->b);
    }

    public function testExtractFromNull()
    {
        $params   = Params::extract();
        $expected = [
            'path'   => null,
            'params' => null,
            'slash'  => false
        ];

        $this->assertEquals($expected, $params);
    }

    public function testExtractFromEmptyString()
    {
        $params   = Params::extract('');
        $expected = [
            'path'   => null,
            'params' => null,
            'slash'  => false
        ];

        $this->assertEquals($expected, $params);
    }

    public function testExtractFromSeparator()
    {
        $params   = Params::extract(Params::separator());
        $expected = [
            'path'   => [],
            'params' => [],
            'slash'  => false
        ];

        $this->assertEquals($expected, $params);
    }

    public function testToString()
    {
        $params = new Params([
            'a' => 'value-a',
            'b' => 'value-b'
        ]);

        $this->assertEquals('a:value-a/b:value-b', $params->toString());
    }

    public function testToStringWithLeadingSlash()
    {
        $params = new Params([
            'a' => 'value-a',
            'b' => 'value-b'
        ]);

        $this->assertEquals('/a:value-a/b:value-b', $params->toString(true));
    }

    public function testToStringWithTrailingSlash()
    {
        $params = new Params([
            'a' => 'value-a',
            'b' => 'value-b'
        ]);

        $this->assertEquals('a:value-a/b:value-b/', $params->toString(false, true));
    }

    public function testToStringWithWindowsSeparator()
    {
        Params::$separator = ';';

        $params = new Params([
            'a' => 'value-a',
            'b' => 'value-b'
        ]);

        $this->assertEquals('a;value-a/b;value-b/', $params->toString(false, true));

        Params::$separator = null;
    }

    public function testUnsetParam()
    {
        $params = new Params(['foo' => 'bar']);
        $params->foo = null;

        $this->assertEquals('', $params->toString());
    }
}
