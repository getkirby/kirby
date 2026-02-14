<?php

namespace Kirby\Uuid;

/**
 * Qualifies an object (e.g. page, file) to
 * be identifiable via UUID. Mostly useful for
 * type-hinting inside the Uuid classes.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.8.0
 */
interface Identifiable
{
	public function id();
	public function uuid(): Uuid|null;
}
