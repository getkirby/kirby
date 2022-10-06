<?php

namespace Kirby\Uuid;

/**
 * Qualifies an object (e.g. page, file) to
 * be identifiable via UUID. Mostly useful for
 * type-hinting inside the Uuid classes.
 * @since 3.8.0
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
interface Identifiable
{
	public function id();
	public function uuid(): Uuid;
}
