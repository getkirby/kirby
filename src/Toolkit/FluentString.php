<?php

namespace Kirby\Toolkit;

/**
 * FluentString - Fluent extension for proper intelisense in IDEs
 *
 * @package   Kirby Toolkit
 * @author    Adam Kiss <iam@adamkiss.com>
 * @link      https://getkirby.com
 * @copyright Adam Kiss
 * @license   https://opensource.org/licenses/MIT
 *
 * TODO: This is a very rough sketch, without descriptions and wrong return types
 *
 * @method FluentArray accepted() Parse accepted values and their quality from an accept string like an Accept or Accept-Language header
 * @method string after(string $needle, bool $caseInsensitive = false)
 * @method string afterStart(string $needle, bool $caseInsensitive = false)
 * @method string ascii()
 * @method string before(string $needle, bool $caseInsensitive = false)
 * @method string beforeEnd(string $needle, bool $caseInsensitive = false)
 * @method string between(string $start, string $end)
 * @method string camel(string $value = null)
 * @method string contains(string $needle, bool $caseInsensitive = false)
 * @method string date(int|null $time = null, string|IntlDateFormatter $format = null, string $handler = 'date')
 * @method string convert(string $targetEncoding, string $sourceEncoding = null)
 * @method string encode()
 * @method string encoding()
 * @method string endsWith(string $needle, bool $caseInsensitive = false)
 * @method string esc(string $context = 'html')
 * @method string excerpt($string, $chars = 140, $strip = true, $rep = ' …')
 * @method string float(string|int|float|null $value)
 * @method string from(string $needle, bool $caseInsensitive = false)
 * @method string increment(string $separator = '-', int $first = 1)
 * @method string kebab(string $value = null)
 * @method string length()
 * @method string lower()
 * @method string ltrim(string $trim = ' ')
 * @method string pool(string|array $type, bool $array = true)
 * @method string position(string $needle, bool $caseInsensitive = false)
 * @method string query(string $query, array $data = [])
 * @method string random(int $length = null, string $type = 'alphaNum')
 * @method string replace($string, $search, $replace, $limit = -1)
 * @method string replacements($search, $replace, $limit)
 * @method string replaceReplacements(array $replacements)
 * @method string rtrim(string $trim = ' ')
 * @method string short(int $length = 0, string $appendix = '…')
 * @method string similarity(string $first, string $second, bool $caseInsensitive = false)
 * @method string snake(string $value = null, string $delimiter = '_')
 * @method FluentArray split(string|array|null $string, string $separator = ',', int $length = 1)
 * @method string startsWith(string $needle, bool $caseInsensitive = false)
 * @method string studly(string $value = null)
 * @method string substr(int $start = 0, int $length = null)
 * @method string toBytes(string $size)
 * @method string toType($string, $type)
 * @method string trim(string $trim = ' ')
 * @method string ucfirst()
 * @method string ucwords()
 * @method string unhtml()
 * @method string until(string $needle, bool $caseInsensitive = false)
 * @method string upper()
 * @method string uuid()
 * @method string widont()
 * @method string wrap(string $before, string $after = null)
 */
class FluentString extends Fluent
{
	public function value(): string
	{
		return $this->value;
	}
}
