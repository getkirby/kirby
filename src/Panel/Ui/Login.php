<?php

namespace Kirby\Panel\Ui;

use Kirby\Auth\Auth;
use Kirby\Auth\Challenge;
use Kirby\Auth\Method;
use Kirby\Cms\App;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Login extends Component
{
	protected Auth $auth;
	protected App $kirby;

	public function __construct(
		protected Method|Challenge $for,
		...$attrs,
	) {
		parent::__construct(...$attrs);
		$this->kirby = App::instance();
		$this->auth  = $this->kirby->auth();
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'pending' => $this->auth->status()->toArray(),
			'type'    => $this->for->type()
		];
	}
}
