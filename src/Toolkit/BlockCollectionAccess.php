<?php

namespace Kirby\Toolkit;

use Attribute;

/**
 * Marks a method as blocked from collection operations such as
 * filterBy/sortBy/group/pluck/findBy to prevent sensitive data
 * exposure (e.g. password hashes) or unintended write actions
 * through queries driven by user input.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
class BlockCollectionAccess
{
}
