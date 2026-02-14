<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\Cms\Site;
use Kirby\Http\Request;
use Kirby\Toolkit\HasI18n;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 * @codeCoverageIgnore
 */
abstract class Controller
{
	use HasI18n;

	protected App $kirby;
	protected Request $request;
	protected Site $site;

	public function __construct()
	{
		$this->kirby   = App::instance();
		$this->request = $this->kirby->request();
		$this->site    = $this->kirby->site();
	}

	abstract public function load(): mixed;

	/**
	 * Submit successfully by default to allow for submit-less controllers
	 */
	public function submit(): mixed
	{
		return true;
	}
}
