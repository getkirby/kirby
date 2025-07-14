<?php

namespace Kirby\Query;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;

/**
 * The Arguments class helps splitting a
 * parameter string into processable arguments
 *
 * @package   Kirby Query
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @todo Deprecate in v6
 *
 * @extends \Kirby\Toolkit\Collection<\Kirby\Query\Argument>
 */
class Arguments extends Collection
{
	// skip all matches inside of parantheses
	public const NO_PNTH = '\([^)]+\)(*SKIP)(*FAIL)';
	// skip all matches inside of square brackets
	public const NO_SQBR = '\[[^]]+\](*SKIP)(*FAIL)';
	// skip all matches inside of double quotes
	public const NO_DLQU = '\"(?:[^"\\\\]|\\\\.)*\"(*SKIP)(*FAIL)';
	// skip all matches inside of single quotes
	public const NO_SLQU = '\'(?:[^\'\\\\]|\\\\.)*\'(*SKIP)(*FAIL)';
	// skip all matches inside of any of the above skip groups
	public const OUTSIDE =
		self::NO_PNTH . '|' . self::NO_SQBR . '|' .
		self::NO_DLQU . '|' . self::NO_SLQU;

	/**
	 * Splits list of arguments into individual
	 * Argument instances while respecting skip groups
	 */
	public static function factory(string $arguments): static
	{
		$arguments = A::map(
			// split by comma, but not inside skip groups
			preg_split('!,|' . self::OUTSIDE . '!', $arguments),
			fn ($argument) => Argument::factory($argument)
		);

		return new static($arguments);
	}

	/**
	 * Resolve each argument, so that they can
	 * passed together to the actual method call
	 */
	public function resolve(array|object $data = []): array
	{
		return A::map(
			$this->data,
			fn ($argument) => $argument->resolve($data)
		);
	}
}
