<?php

namespace Kirby\Panel\Controller;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 *
 * @codeCoverageIgnore
 */
abstract class DialogController extends Controller
{
	abstract public function load(): array;

	/**
	 * Submit successfully by default to allow for submit-less dialogs
	 */
	public function submit()
	{
		return true;
	}
}
