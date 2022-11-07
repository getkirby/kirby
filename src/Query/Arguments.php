<?php

namespace Kirby\Query;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;

/**
 * The Argument class represents a single
 * parameter passed to a method in a chained query
 *
 * @package   Kirby Query
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
final class Arguments extends Collection
{
	public const NO_PNTH = '\([^(]+\)(*SKIP)(*FAIL)';
	public const NO_SQBR = '\[[^]]+\](*SKIP)(*FAIL)';
	public const NO_DLQU = '\"(?:[^"\\\\]|\\\\.)*\"(*SKIP)(*FAIL)';
	public const NO_SLQU = '\'(?:[^\'\\\\]|\\\\.)*\'(*SKIP)(*FAIL)';

	public static function factory(string $arguments): static
	{
		$arguments = A::map(
			// split by comma, but not inside skip groups
			preg_split('!,|' . self::NO_PNTH . '|' . self::NO_SQBR . '|' .
					self::NO_DLQU . '|' . self::NO_SLQU . '!', $arguments),
			fn ($argument) => Argument::factory($argument)
		);

		return new static($arguments);
	}

	public function resolve(array|object $data = []): array
	{
		return A::map(
			$this->data,
			fn ($argument) => $argument->resolve($data)
		);
	}
}
