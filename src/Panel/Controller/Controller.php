<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\Cms\Site;
use Kirby\Http\Request;
use Kirby\Toolkit\I18n;

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
abstract class Controller
{
	protected App $kirby;
	protected Request $request;
	protected Site $site;

	public function __construct()
	{
		$this->kirby   = App::instance();
		$this->request = $this->kirby->request();
		$this->site    = $this->kirby->site();
	}

	protected function i18n(
		string|array|null $key,
		array|null $data = null
	): string|null {

		if ($key === null) {
			return null;
		}

		if ($data === null) {
			return I18n::translate($key, $key);
		}

		return I18n::template($key, $data);
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
