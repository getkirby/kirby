<?php

namespace Kirby\Util;

use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{

    public function testSlug()
    {
        // Double dashes
        $this->assertEquals('a-b', Str::slug('a--b'));

        // Dashes at the end of the line
        $this->assertEquals('a', Str::slug('a-'));

        // Dashes at the beginning of the line
        $this->assertEquals('a', Str::slug('-a'));

        // Underscores converted to dashes
        $this->assertEquals('a-b', Str::slug('a_b'));

        // Unallowed characters
        $this->assertEquals('a-b', Str::slug('a@b'));

        // Spaces characters
        $this->assertEquals('a-b', Str::slug('a b'));

        // Double Spaces characters
        $this->assertEquals('a-b', Str::slug('a  b'));

        // Custom separator
        $this->assertEquals('a+b', Str::slug('a-b', '+'));

        // Allow underscores
        $this->assertEquals('a_b', Str::slug('a_b', '-', 'a-z0-9_'));

        // store default defaults
        $defaults = Str::$defaults['slug'];

        // Custom str defaults
        Str::$defaults['slug']['separator'] = '+';
        Str::$defaults['slug']['allowed']   = 'a-z0-9_';

        $this->assertEquals('a+b', Str::slug('a-b'));
        $this->assertEquals('a_b', Str::slug('a_b'));

        // Reset str defaults
        Str::$defaults['slug'] = $defaults;
    }

    public function testEncoding()
    {
        $this->assertEquals('UTF-8', Str::encoding('ÖÄÜ'));
    }

    public function testSubstr()
    {
        $string = 'äöü';

        $this->assertEquals($string, Str::substr($string));
        $this->assertEquals($string, Str::substr($string, 0));
        $this->assertEquals($string, Str::substr($string, 0, 3));
        $this->assertEquals('ä', Str::substr($string, 0, 1));
        $this->assertEquals('äö', Str::substr($string, 0, 2));
        $this->assertEquals('ü', Str::substr($string, -1));
    }

    public function testSplit()
    {
        $string = 'ä,ö,ü,ß';
        $this->assertEquals(['ä', 'ö', 'ü', 'ß'], Str::split($string));

        $string = 'ä/ö/ü/ß';
        $this->assertEquals(['ä', 'ö', 'ü', 'ß'], Str::split($string, '/'));

        $string = 'ää/ö/üü/ß';
        $this->assertEquals(['ää', 'üü'], Str::split($string, '/', 2));
    }

    public function testLower()
    {
        $this->assertEquals('öäü', Str::lower('ÖÄÜ'));
        $this->assertEquals('öäü', Str::lower('Öäü'));
    }

    public function testUpper()
    {
        $this->assertEquals('ÖÄÜ', Str::upper('öäü'));
        $this->assertEquals('ÖÄÜ', Str::upper('Öäü'));
    }

    public function testLength()
    {
        $this->assertEquals(0, Str::length(''));
        $this->assertEquals(3, Str::length('abc'));
        $this->assertEquals(3, Str::length('öäü'));
        $this->assertEquals(6, Str::length('Aœ?_ßö'));
    }

    public function testUcfirst()
    {
        $this->assertEquals('Hello world', Str::ucfirst('hello world'));
        $this->assertEquals('Hello world', Str::ucfirst('Hello World'));
    }

    public function testUcwords()
    {
        $this->assertEquals('Hello World', Str::ucwords('hello world'));
        $this->assertEquals('Hello World', Str::ucwords('Hello world'));
        $this->assertEquals('Hello World', Str::ucwords('HELLO WORLD'));
    }

    public function testContains()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::contains($string, 'Hellö'));
        $this->assertTrue(Str::contains($string, 'Wörld'));
        $this->assertFalse(Str::contains($string, 'hellö'));
        $this->assertFalse(Str::contains($string, 'wörld'));

        // case insensitive
        $this->assertTrue(Str::contains($string, 'Hellö', true));
        $this->assertTrue(Str::contains($string, 'Wörld', true));
        $this->assertTrue(Str::contains($string, 'hellö', true));
        $this->assertTrue(Str::contains($string, 'wörld', true));
    }

    public function testPosition()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::position($string, 'H') === 0);
        $this->assertFalse(Str::position($string, 'h') === 0);
        $this->assertTrue(Str::position($string, 'ö') === 4);
        $this->assertFalse(Str::position($string, 'Ö') === 4);

        // case insensitive
        $this->assertTrue(Str::position($string, 'H', true) === 0);
        $this->assertTrue(Str::position($string, 'h', true) === 0);
        $this->assertTrue(Str::position($string, 'ö', true) === 4);
        $this->assertTrue(Str::position($string, 'Ö', true) === 4);
    }

    public function testStartsWith()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::startsWith($string, ''));
        $this->assertTrue(Str::startsWith($string, 'H'));
        $this->assertFalse(Str::startsWith($string, 'h'));
        $this->assertTrue(Str::startsWith($string, 'Hellö'));
        $this->assertFalse(Str::startsWith($string, 'hellö'));

        // case insensitive
        $this->assertTrue(Str::startsWith($string, '', true));
        $this->assertTrue(Str::startsWith($string, 'H', true));
        $this->assertTrue(Str::startsWith($string, 'h', true));
        $this->assertTrue(Str::startsWith($string, 'Hellö', true));
        $this->assertTrue(Str::startsWith($string, 'hellö', true));
    }

    public function testEndsWith()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertTrue(Str::endsWith($string, ''));
        $this->assertTrue(Str::endsWith($string, 'd'));
        $this->assertFalse(Str::endsWith($string, 'D'));
        $this->assertTrue(Str::endsWith($string, 'Wörld'));
        $this->assertFalse(Str::endsWith($string, 'WÖRLD'));

        // case insensitive
        $this->assertTrue(Str::endsWith($string, '', true));
        $this->assertTrue(Str::endsWith($string, 'd', true));
        $this->assertTrue(Str::endsWith($string, 'D', true));
        $this->assertTrue(Str::endsWith($string, 'Wörld', true));
        $this->assertTrue(Str::endsWith($string, 'WÖRLD', true));
    }

    public function testBefore()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals('Hell', Str::before($string, 'ö'));
        $this->assertEquals(false, Str::before($string, 'Ö'));
        $this->assertEquals(false, Str::before($string, 'x'));

        // case insensitive
        $this->assertEquals('Hell', Str::before($string, 'ö', true));
        $this->assertEquals('Hell', Str::before($string, 'Ö', true));
        $this->assertEquals(false, Str::before($string, 'x'));
    }

    public function testUntil()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals('Hellö', Str::until($string, 'ö'));
        $this->assertEquals(false, Str::until($string, 'Ö'));
        $this->assertEquals(false, Str::until($string, 'x'));

        // case insensitive
        $this->assertEquals('Hellö', Str::until($string, 'ö', true));
        $this->assertEquals('Hellö', Str::until($string, 'Ö', true));
        $this->assertEquals(false, Str::until($string, 'x'));
    }

    public function testAfter()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals(' Wörld', Str::after($string, 'ö'));
        $this->assertEquals(false, Str::after($string, 'Ö'));
        $this->assertEquals(false, Str::after($string, 'x'));

        // case insensitive
        $this->assertEquals(' Wörld', Str::after($string, 'ö', true));
        $this->assertEquals(' Wörld', Str::after($string, 'Ö', true));
        $this->assertEquals(false, Str::after($string, 'x'));
    }

    public function testFrom()
    {
        $string = 'Hellö Wörld';

        // case sensitive
        $this->assertEquals('ö Wörld', Str::from($string, 'ö'));
        $this->assertEquals(false, Str::from($string, 'Ö'));
        $this->assertEquals(false, Str::from($string, 'x'));

        // case insensitive
        $this->assertEquals('ö Wörld', Str::from($string, 'ö', true));
        $this->assertEquals('ö Wörld', Str::from($string, 'Ö', true));
        $this->assertEquals(false, Str::from($string, 'x'));
    }

    public function testShort()
    {
        $string = 'Super Äwesøme String';

        // too long
        $this->assertEquals('Super…', Str::short($string, 5));

        // not too long
        $this->assertEquals($string, Str::short($string, 100));

        // zero chars
        $this->assertEquals($string, Str::short($string, 0));

        // with different ellipsis character
        $this->assertEquals('Super---', Str::short($string, 5, '---'));
    }

    public function testTemplate()
    {
        $string = 'From {b} to {a}';
        $this->assertEquals('From here to there', Str::template($string, ['a' => 'there', 'b' => 'here']));
        $this->assertEquals('From  to ', Str::template($string, []));
    }
}
