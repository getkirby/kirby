<?php

namespace Kirby\Data\Handler;

use Symfony\Component\Yaml\Yaml as Parser;
use Kirby\Data\Handler;

/**
 * Simple Wrapper around Symfony's Yaml Component
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Yaml extends Handler
{

    /**
     * Converts an array to a yaml string
     *
     * @param  array  $data
     * @return string
     */
    public static function encode(array $data): string
    {
        return Parser::dump($data);
    }

    /**
     * Parses YAML and returns a multi-dimensional array
     *
     * @param  string $yaml
     * @return array
     */
    public static function decode(string $yaml): array
    {
        return Parser::parse($yaml);
    }
}
