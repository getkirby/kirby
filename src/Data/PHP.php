<?php

namespace Kirby\Data;

use Exception;
use Kirby\Toolkit\F;

/**
 * Reader and write of PHP files with data in a returned array
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class PHP extends Handler
{

    /**
     * Converts an array to PHP file content
     *
     * @param  mixed  $data
     * @param  string $indent For internal use only
     * @return string
     */
    public static function encode($data, $indent = ''): string
    {
        switch (gettype($data)) {
            case 'array':
                $indexed = array_keys($data) === range(0, count($data) - 1);
                $array   = [];

                foreach ($data as $key => $value) {
                    $array[] = "$indent    " . ($indexed ? '' : static::encode($key) . ' => ') . static::encode($value, "$indent    ");
                }

                return "[\n" . implode(",\n", $array) . "\n" . $indent . "]";
            case 'boolean':
                return $data ? 'true' : 'false';
            case 'int':
            case 'double':
                return $data;
            default:
                return var_export($data, true);
        }
    }

    /**
     * PHP arrays don't have to be decoded
     *
     * @param  array $array
     * @return array
     */
    public static function decode($array): array
    {
        return $array;
    }

    /**
     * Reads data from a file
     *
     * @param  string $file
     * @return array
     */
    public static function read(string $file): array
    {
        if (is_file($file) !== true) {
            throw new Exception('The file "' . $file . '" does not exist');
        }

        return (array)(include $file);
    }

    /**
     * Creates a PHP file with the given data
     *
     * @param  array   $data
     * @return boolean
     */
    public static function write(string $file = null, array $data = []): bool
    {
        $php = static::encode($data);
        $php = "<?php\n\nreturn $php;";

        if (F::write($file, $php) === true) {
            F::invalidateOpcodeCache($file);
            return true;
        }

        return false; // @codeCoverageIgnore
    }
}
