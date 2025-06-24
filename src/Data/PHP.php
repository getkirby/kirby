<?php

namespace Kirby\Data;

use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\Exception;
use Kirby\Filesystem\F;

/**
 * Reader and write of PHP files with data in a returned array
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class PHP extends Handler
{
	/**
	 * Converts data to PHP file content
	 *
	 * @param string $indent For internal use only
	 */
	public static function encode($data, string $indent = ''): string
	{
		return match (gettype($data)) {
			'array'   => static::encodeArray($data, $indent),
			'boolean' => $data ? 'true' : 'false',
			'integer',
			'double'  => (string)$data,
			default   => var_export($data, true)
		};
	}

	/**
	 * Converts an array to PHP file content
	 */
	protected static function encodeArray(array $data, string $indent): string
	{
		$indexed = array_is_list($data);
		$lines   = [];

		foreach ($data as $key => $value) {
			$line = "$indent    ";

			if ($indexed === false) {
				$line .= static::encode($key) . ' => ';
			}

			$line .= static::encode($value, "$indent    ");

			$lines[] =  $line;
		}

		return "[\n" . implode(",\n", $lines) . "\n" . $indent . ']';
	}

	/**
	 * PHP strings shouldn't be decoded manually
	 */
	public static function decode($string): array
	{
		throw new BadMethodCallException(
			message: 'The PHP::decode() method is not implemented'
		);
	}

	/**
	 * Reads data from a file
	 */
	public static function read(string $file): array
	{
		if (is_file($file) !== true) {
			throw new Exception(
				message: 'The file "' . $file . '" does not exist'
			);
		}

		return (array)F::load($file, [], allowOutput: false);
	}

	/**
	 * Creates a PHP file with the given data
	 */
	public static function write(string $file, $data = []): bool
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
