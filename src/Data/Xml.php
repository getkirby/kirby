<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Xml as XmlConverter;

/**
 * Simple Wrapper around the XML parser of the Toolkit
 *
 * @package   Kirby Data
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Xml extends Handler
{
    /**
     * Converts an array to an encoded XML string
     *
     * @param mixed $data
     * @return string
     */
    public static function encode($data): string
    {
        return XmlConverter::create($data, 'data');
    }

    /**
     * Parses an encoded XML string and returns a multi-dimensional array
     *
     * @param mixed $string
     * @return array
     */
    public static function decode($string): array
    {
        if ($string === null || $string === '') {
            return [];
        }

        if (is_array($string) === true) {
            return $string;
        }

        if (is_string($string) === false) {
            throw new InvalidArgumentException('Invalid XML data; please pass a string');
        }

        $result = XmlConverter::parse($string);

        if (is_array($result) === true) {
            // remove the root's name if it is the default <data> to ensure that
            // the decoded data is the same as the input to the encode() method
            if ($result['@name'] === 'data') {
                unset($result['@name']);
            }

            return $result;
        } else {
            throw new InvalidArgumentException('XML string is invalid');
        }
    }
}
