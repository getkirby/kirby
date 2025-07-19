<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\Http\Request;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 * @unstable
 *
 * @codeCoverageIgnore
 */
abstract class DialogController
{
	protected App $kirby;
	protected Request $request;

	public function __construct()
	{
		$this->kirby   = App::instance();
		$this->request = $this->kirby->request();
	}

	abstract public function load(): array;

	/**
	 * Submit successfully by default to allow for submit-less dialogs
	 */
	public function submit()
	{
		return true;
	}
}
