<?php

declare(strict_types=1);

namespace Base32;

/**
 * Base32 encoder and decoder.
 *
 * RFC 4648 compliant
 *
 * @see     http://www.ietf.org/rfc/rfc4648.txt
 * Some groundwork based on this class
 * https://github.com/NTICompass/PHP-Base32
 *
 * @author  Christian Riesen <chris.riesen@gmail.com>
 * @author  Sam Williams <sam@badcow.co>
 *
 * @see     http://christianriesen.com
 *
 * @license MIT License see LICENSE file
 */
class Base32
{
    /**
     * Alphabet for encoding and decoding base32.
     *
     * @var string
     */
    protected const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

    /**
     * Number of padding characters that may legally trail an encoded
     * block, keyed by count. Any other count is a malformed encoding.
     *
     * A final quantum of 1/2/3/4 bytes is encoded as 2/4/5/7 significant
     * characters padded to 8 with 6/4/3/1 '=' characters respectively;
     * a full 5-byte quantum has no padding.
     */
    protected const VALID_PADDING = [0 => true, 1 => true, 3 => true, 4 => true, 6 => true];

    /**
     * Maps the Base32 character to its corresponding bit value.
     */
    protected const MAPPING = [
        'A' => 0b00000,
        'B' => 0b00001,
        'C' => 0b00010,
        'D' => 0b00011,
        'E' => 0b00100,
        'F' => 0b00101,
        'G' => 0b00110,
        'H' => 0b00111,
        'I' => 0b01000,
        'J' => 0b01001,
        'K' => 0b01010,
        'L' => 0b01011,
        'M' => 0b01100,
        'N' => 0b01101,
        'O' => 0b01110,
        'P' => 0b01111,
        'Q' => 0b10000,
        'R' => 0b10001,
        'S' => 0b10010,
        'T' => 0b10011,
        'U' => 0b10100,
        'V' => 0b10101,
        'W' => 0b10110,
        'X' => 0b10111,
        'Y' => 0b11000,
        'Z' => 0b11001,
        '2' => 0b11010,
        '3' => 0b11011,
        '4' => 0b11100,
        '5' => 0b11101,
        '6' => 0b11110,
        '7' => 0b11111,
    ];

    /**
     * Encodes into base32.
     *
     * @param string $string Clear text string
     *
     * @return string Base32 encoded string
     */
    public static function encode(string $string): string
    {
        // Empty string results in empty string
        if ('' === $string) {
            return '';
        }

        $encoded = '';

        //Set the initial values
        $n = $bitLen = $val = 0;
        $len = \strlen($string);

        //Pad the end of the string - this ensures that there are enough zeros
        $string .= \str_repeat(\chr(0), 4);

        //Explode string into integers
        $chars = (array) \unpack('C*', $string, 0);

        while ($n < $len || 0 !== $bitLen) {
            //If the bit length has fallen below 5, shift left 8 and add the next character.
            if ($bitLen < 5) {
                $val = $val << 8;
                $bitLen += 8;
                $n++;
                $val += $chars[$n];
            }
            $shift = $bitLen - 5;
            $encoded .= ($n - (int)($bitLen > 8) > $len && 0 == $val) ? '=' : static::ALPHABET[$val >> $shift];
            $val = $val & ((1 << $shift) - 1);
            $bitLen -= 5;
        }

        return $encoded;
    }

    /**
     * Decodes base32.
     *
     * This decoder conforms to RFC 4648: the input is rejected (rather than
     * sanitized) if it is not a canonical encoding. It is case-sensitive and
     * requires correct padding.
     *
     * @param string $base32String Base32 encoded string
     *
     * @throws \InvalidArgumentException if $base32String is not valid RFC 4648 base32
     *
     * @return string Clear text string
     */
    public static function decode(string $base32String): string
    {
        // Empty string results in empty string
        if ('' === $base32String) {
            return '';
        }

        $len = \strlen($base32String);

        // RFC 4648: encoded data is a whole number of 8-character blocks,
        // padding included.
        if (0 !== $len % 8) {
            throw new \InvalidArgumentException('Base32: input length must be a multiple of 8.');
        }

        // Padding must be a contiguous run of '=' at the very end.
        $padLen = 0;
        while ($padLen < $len && '=' === $base32String[$len - 1 - $padLen]) {
            $padLen++;
        }

        if (!isset(static::VALID_PADDING[$padLen])) {
            throw new \InvalidArgumentException('Base32: invalid padding.');
        }

        $dataLen = $len - $padLen;

        $decoded = '';
        $val = 0;
        $bitLen = 0;

        for ($i = 0; $i < $dataLen; $i++) {
            $char = $base32String[$i];

            // Reject anything outside the alphabet, including a stray '='
            // before the trailing padding run.
            if (!isset(static::MAPPING[$char])) {
                throw new \InvalidArgumentException(\sprintf('Base32: invalid character "%s" at offset %d.', $char, $i));
            }

            $val = ($val << 5) | static::MAPPING[$char];
            $bitLen += 5;

            if ($bitLen >= 8) {
                $bitLen -= 8;
                $decoded .= \chr(($val >> $bitLen) & 0xFF);
                $val &= (1 << $bitLen) - 1;
            }
        }

        // Any leftover bits that do not form a full byte must be zero,
        // otherwise the encoding is non-canonical.
        if (0 !== $val) {
            throw new \InvalidArgumentException('Base32: non-zero trailing bits.');
        }

        return $decoded;
    }
}
