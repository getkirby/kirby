<?php

namespace Kirby\Toolkit;

class EscapeTest extends TestCase
{
    /**
     * All character encodings supported by htmlspecialchars()
     */
    protected $supportedEncodings = [
        'iso-8859-1',   'iso8859-1',    'iso-8859-5',   'iso8859-5',
        'iso-8859-15',  'iso8859-15',   'utf-8',        'cp866',
        'ibm866',       '866',          'cp1251',       'windows-1251',
        'win-1251',     '1251',         'cp1252',       'windows-1252',
        '1252',         'koi8-r',       'koi8-ru',      'koi8r',
        'big5',         '950',          'gb2312',       '936',
        'big5-hkscs',   'shift_jis',    'sjis',         'sjis-win',
        'cp932',        '932',          'euc-jp',       'eucjp',
        'eucjp-win',    'macroman'
    ];

    protected $htmlSpecialChars = [
        '\''    => '&#039;',
        '"'     => '&quot;',
        '<'     => '&lt;',
        '>'     => '&gt;',
        '&'     => '&amp;',
    ];

    protected $htmlAttrSpecialChars = [
        '\''    => '&#x27;',
        // Characters beyond ASCII value 255 to unicode escape
        'Ā'     => '&#x0100;',
        // Characters beyond Unicode BMP to unicode escape
        "\xF0\x90\x80\x80" => '&#x10000;',
        // Immune chars excluded
        ','     => ',',
        '.'     => '.',
        '-'     => '-',
        '_'     => '_',
        // Basic alnums exluded
        'a'     => 'a',
        'A'     => 'A',
        'z'     => 'z',
        'Z'     => 'Z',
        '0'     => '0',
        '9'     => '9',
        // Basic control characters and null
        "\r"    => '&#x0D;',
        "\n"    => '&#x0A;',
        "\t"    => '&#x09;',
        "\0"    => '&#xFFFD;', // should use Unicode replacement char
        // Encode chars as named entities where possible
        '<'     => '&lt;',
        '>'     => '&gt;',
        '&'     => '&amp;',
        '"'     => '&quot;',
        // Encode spaces for quoteless attribute protection
        ' '     => '&#x20;',
    ];

    protected $jsSpecialChars = [
        // HTML special chars - escape without exception to hex
        '<'     => '\\x3C',
        '>'     => '\\x3E',
        '\''    => '\\x27',
        '"'     => '\\x22',
        '&'     => '\\x26',
        // Characters beyond ASCII value 255 to unicode escape
        'Ā'     => '\\u0100',
        // Characters beyond Unicode BMP to unicode escape
        "\xF0\x90\x80\x80" => '\\uD800\\uDC00',
        // Immune chars excluded
        ','     => ',',
        '.'     => '.',
        '_'     => '_',
        // Basic alnums exluded
        'a'     => 'a',
        'A'     => 'A',
        'z'     => 'z',
        'Z'     => 'Z',
        '0'     => '0',
        '9'     => '9',
        // Basic control characters and null
        "\r"    => '\\x0D',
        "\n"    => '\\x0A',
        "\t"    => '\\x09',
        "\0"    => '\\x00',
        // Encode spaces for quoteless attribute protection
        ' '     => '\\x20',
    ];

    protected $urlSpecialChars = [
        // HTML special chars - escape without exception to percent encoding
        '<'     => '%3C',
        '>'     => '%3E',
        '\''    => '%27',
        '"'     => '%22',
        '&'     => '%26',
        // Characters beyond ASCII value 255 to hex sequence
        'Ā'     => '%C4%80',
        // Punctuation and unreserved check
        ','     => '%2C',
        '.'     => '.',
        '_'     => '_',
        '-'     => '-',
        ':'     => '%3A',
        ';'     => '%3B',
        '!'     => '%21',
        // Basic alnums excluded
        'a'     => 'a',
        'A'     => 'A',
        'z'     => 'z',
        'Z'     => 'Z',
        '0'     => '0',
        '9'     => '9',
        // Basic control characters and null
        "\r"    => '%0D',
        "\n"    => '%0A',
        "\t"    => '%09',
        "\0"    => '%00',
        // PHP quirks from the past
        ' '     => '%20',
        '~'     => '~',
        '+'     => '%2B',
    ];

    protected $cssSpecialChars = [
        // HTML special chars - escape without exception to hex
        '<'     => '\\3C ',
        '>'     => '\\3E ',
        '\''    => '\\27 ',
        '"'     => '\\22 ',
        '&'     => '\\26 ',
        // Characters beyond ASCII value 255 to unicode escape
        'Ā'     => '\\100 ',
        // Characters beyond Unicode BMP to unicode escape
        "\xF0\x90\x80\x80" => '\\10000 ',
        // Immune chars excluded
        ','     => '\\2C ',
        '.'     => '\\2E ',
        '_'     => '\\5F ',
        // Basic alnums exluded
        'a'     => 'a',
        'A'     => 'A',
        'z'     => 'z',
        'Z'     => 'Z',
        '0'     => '0',
        '9'     => '9',
        // Basic control characters and null
        "\r"    => '\\D ',
        "\n"    => '\\A ',
        "\t"    => '\\9 ',
        "\0"    => '\\0 ',
        // Encode spaces for quoteless attribute protection
        ' '     => '\\20 ',
    ];


    public function testHtmlEscapingConvertsSpecialChars()
    {
        foreach ($this->htmlSpecialChars as $key => $value) {
            $this->assertEquals(
                $value,
                Escape::html($key),
                'Failed to escape: ' . $key
            );
        }
    }

    public function testHtmlAttributeEscapingConvertsSpecialChars()
    {
        foreach ($this->htmlAttrSpecialChars as $key => $value) {
            $this->assertEquals(
                $value,
                Escape::attr($key),
                'Failed to escape: ' . $key
            );
        }
    }

    public function testJavascriptEscapingConvertsSpecialChars()
    {
        foreach ($this->jsSpecialChars as $key => $value) {
            $this->assertEquals(
                $value,
                Escape::js($key),
                'Failed to escape: ' . $key
            );
        }
    }

    public function testJavascriptEscapingReturnsStringIfZeroLength()
    {
        $this->assertEquals('', Escape::js(''));
    }

    public function testJavascriptEscapingReturnsStringIfContainsOnlyDigits()
    {
        $this->assertEquals('123', Escape::js('123'));
    }

    public function testCssEscapingConvertsSpecialChars()
    {
        foreach ($this->cssSpecialChars as $key => $value) {
            $this->assertEquals(
                $value,
                Escape::css($key),
                'Failed to escape: ' . $key
            );
        }
    }

    public function testCssEscapingReturnsStringIfZeroLength()
    {
        $this->assertEquals('', Escape::css(''));
    }

    public function testCssEscapingReturnsStringIfContainsOnlyDigits()
    {
        $this->assertEquals('123', Escape::css('123'));
    }

    public function testUrlEscapingConvertsSpecialChars()
    {
        foreach ($this->urlSpecialChars as $key => $value) {
            $this->assertEquals(
                $value,
                Escape::url($key),
                'Failed to escape: ' . $key
            );
        }
    }

    // /**
    //  * Range tests to confirm escaped range of characters is within OWASP recommendation
    //  */

    /**
     * Only testing the first few 2 ranges on this prot. function as that's all these
     * other range tests require
     */
    public function testUnicodeCodepointConversionToUtf8()
    {
        $expected = ' ~ޙ';
        $codepoints = [0x20, 0x7e, 0x799];
        $result = '';
        foreach ($codepoints as $value) {
            $result .= $this->codepointToUtf8($value);
        }
        $this->assertEquals($expected, $result);
    }

    /**
     * Convert a Unicode Codepoint to a literal UTF-8 character.
     *
     * @param int $codepoint Unicode codepoint in hex notation
     * @return string UTF-8 literal string
     */
    protected function codepointToUtf8($codepoint)
    {
        if ($codepoint < 0x80) {
            return chr($codepoint);
        }
        if ($codepoint < 0x800) {
            return chr($codepoint >> 6 & 0x3f | 0xc0)
                . chr($codepoint & 0x3f | 0x80);
        }
        if ($codepoint < 0x10000) {
            return chr($codepoint >> 12 & 0x0f | 0xe0)
                . chr($codepoint >> 6 & 0x3f | 0x80)
                . chr($codepoint & 0x3f | 0x80);
        }
        if ($codepoint < 0x110000) {
            return chr($codepoint >> 18 & 0x07 | 0xf0)
                . chr($codepoint >> 12 & 0x3f | 0x80)
                . chr($codepoint >> 6 & 0x3f | 0x80)
                . chr($codepoint & 0x3f | 0x80);
        }
        throw new \Exception('Codepoint requested outside of Unicode range');
    }

    public function testJavascriptEscapingEscapesOwaspRecommendedRanges()
    {
        $immune = [',', '.', '_']; // Exceptions to escaping ranges
        for ($chr = 0; $chr < 0xFF; $chr++) {
            if ($chr >= 0x30 && $chr <= 0x39
                || $chr >= 0x41 && $chr <= 0x5A
                || $chr >= 0x61 && $chr <= 0x7A
            ) {
                $literal = $this->codepointToUtf8($chr);
                $this->assertEquals($literal, Escape::js($literal));
            } else {
                $literal = $this->codepointToUtf8($chr);
                if (in_array($literal, $immune)) {
                    $this->assertEquals($literal, Escape::js($literal));
                } else {
                    $this->assertNotEquals(
                        $literal,
                        Escape::js($literal),
                        $literal . ' should be escaped!'
                    );
                }
            }
        }
    }

    public function testHtmlAttributeEscapingEscapesOwaspRecommendedRanges()
    {
        $immune = [',', '.', '-', '_']; // Exceptions to escaping ranges
        for ($chr = 0; $chr < 0xFF; $chr++) {
            if ($chr >= 0x30 && $chr <= 0x39
                || $chr >= 0x41 && $chr <= 0x5A
                || $chr >= 0x61 && $chr <= 0x7A
            ) {
                $literal = $this->codepointToUtf8($chr);
                $this->assertEquals($literal, Escape::attr($literal));
            } else {
                $literal = $this->codepointToUtf8($chr);
                if (in_array($literal, $immune)) {
                    $this->assertEquals($literal, Escape::attr($literal));
                } else {
                    $this->assertNotEquals(
                        $literal,
                        Escape::attr($literal),
                        $literal . ' should be escaped!'
                    );
                }
            }
        }
    }

    public function testCssEscapingEscapesOwaspRecommendedRanges()
    {
        $immune = []; // CSS has no exceptions to escaping ranges
        for ($chr = 0; $chr < 0xFF; $chr++) {
            if ($chr >= 0x30 && $chr <= 0x39
                || $chr >= 0x41 && $chr <= 0x5A
                || $chr >= 0x61 && $chr <= 0x7A
            ) {
                $literal = $this->codepointToUtf8($chr);
                $this->assertEquals($literal, Escape::css($literal));
            } else {
                $literal = $this->codepointToUtf8($chr);
                $this->assertNotEquals(
                    $literal,
                    Escape::css($literal),
                    $literal . ' should be escaped!'
                );
            }
        }
    }

    public function xmlStringProvider()
    {
        return [
            ['\'', '&apos;'],
            ['"', '&quot;'],
            ['&', '&amp;'],
            ['<', '&lt;'],
            ['>', '&gt;'],
        ];
    }

    /**
     * @dataProvider xmlStringProvider
     */
    public function testXml($input, $expected)
    {
        $this->assertEquals($expected, Escape::xml($input));
    }
}
